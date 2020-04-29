<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server("0.0.0.0", 9502);

$GLOBALS['data'] = ['code'=>0,'data'=>[],'msg'=>''];

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) use($redis) {
	$uid = $request->get['uid'];

    // 将聊天室fd保存到session/redis中
	$uid && $redis->hset('uid',$request->fd,$uid);
	echo 'uid:'.$uid."\t fd:".$request->fd.PHP_EOL;

	/*
	关于客服系统主要有下面2种场景：
	1.游客进入聊天界面，直接分配在线的客服（游客主动匹配客服）
	2.游客进入后，不直接分配，进入等待区。由客服自己选择和谁聊天（客服主动匹配游客）
	*/
	$redis->sadd('fd_'.$request->fd, $request->fd);	// 先创建临时会话标识
	
	// 欢迎语
	$GLOBALS['data']['code'] = 1;
	$GLOBALS['data']['msg'] = $uid ? "hello, welcome! 会员{$request->fd}\n": "hello, welcome! 游客{$request->fd}\n";
    $ws->push($request->fd, json_encode($GLOBALS['data']));
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use($redis) {
    echo "Message: {$frame->data}\n";
	
	$uid = $redis->hget('uid',$frame->fd);
	echo 'uid:'.$uid."\t fd:".$frame->fd.PHP_EOL;
	
	$data = $GLOBALS['data'];
	
	// 默认返回数据
	$data['code'] = 0;
	$name = $uid ? '会员'.$frame->fd : '游客'.$frame->fd;
	$data['data'] = ['fd'=>$frame->fd, 'name'=>$name, 'data'=>$frame->data, 'status'=>0];
	$data['msg'] = '';
	
	// 临时会话中的fd集合，临时会话集合中只能保存2个fd，即一对一聊天
	$chat_fd = $redis->smembers('fd_'.$frame->fd);	// 这里可采用客户端push数据（$frame->data）的时候，把会话标识fd带上（可通过click_chat.php接口返回），方便识别
	if(empty($chat_fd)){
		echo "redis fd is empty"; 
	}
	
	foreach($chat_fd as $fd){
		// 判断临时会话中非当前监听的fd（即对方fd）
		if($frame->fd != $fd){
			$rs = $ws->push($fd, json_encode($data));	// push数据给对方
			if($rs){
				// 推送成功，返回成功push状态
				$data['data']['status'] = 1;
			}else{
				// 推送失败
				$data['code'] = -1;
				$data['data']['status'] = 0;
				$data['msg'] = 'error push!';
			}
			$ws->push($frame->fd, json_encode($data));	// push数据给自己（这里主要是为了返回发送状态）
			break;	// 一对一聊天，这里无需再往后执行
		}
	}
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($redis) {
	// 移除保存的fd
	//unset($_SESSION['obo'][$fd]);
	$redis->srem('fd_',$fd);
    echo "client-{$fd} is closed\n";
});

$ws->start();