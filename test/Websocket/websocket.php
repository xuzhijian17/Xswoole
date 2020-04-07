<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server("0.0.0.0", 9502);


$GLOBALS['data'] = ['code'=>0,'data'=>[],'msg'=>''];

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    // 将聊天室fd保存到session/redis中
	$_SESSION['obo'][] = $request->fd;
	var_dump($_SESSION['obo']);
	
	// 欢迎语
	$GLOBALS['data']['code'] = 1;
	$GLOBALS['data']['msg'] = "hello, welcome! fd:{$request->fd}\n";
    $ws->push($request->fd, json_encode($GLOBALS['data']));
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";

	$data = $GLOBALS['data'];
	
	$data['code'] = 0;
	$err_list = [];
	
	$data['data'] = ['fd'=>$frame->fd, 'name'=>'游客'.$frame->fd, 'data'=>$frame->data];
	foreach($_SESSION['obo'] as $k=>$v){
		// 遍历所有连接的fd，向非当前fd推送数
		if($frame->fd != $v){
			$rs = $ws->push($v, json_encode($data));
			if(!$rs){
				$data['code'] = -1;
				$err_list[] = ['fd'=>$v, 'name'=>'游客'.$v, 'data'=>$frame->data];				
			}
		}
	}
	
	if($code == 0){
		$data['data']['status'] = 1;
	}elseif($code == -1){
		$data['data'] = $err_list;
	}
	
	$ws->push($frame->fd, json_encode($data));
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();