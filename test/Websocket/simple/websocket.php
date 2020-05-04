<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new Swoole\WebSocket\Server("0.0.0.0", 9502);

$GLOBALS['data'] = ['code'=>0,'data'=>[],'msg'=>''];

// 这里使用swoole自带的table进行进程间内存共享，也可使用redis等其它外部存储服务
$table = new Swoole\Table(1024);	// 由于 Table 是在共享内存之上，所以无法动态扩容所以这个 $size 必须在创建前自己计算设置好，$size必需为2的n次方，最小为1024。
//$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->create();	// 必须在 Server->start() 前执行
var_dump($table->memorySize);	// 获取实际占用内存的尺寸，单位为字节。Table 占用的内存总数为(HashTable结构体长度 + KEY长度64字节 + $size值) * (1 + $conflict_proportion值作为hash冲突) * (列尺寸)
// 添加table对象
$ws->table = $table;

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
	echo "open fd:".$request->fd.PHP_EOL;

	// 可将聊天室fd保存到session/redis/table中
	$name = "游客{$request->fd}";
	$ret = $ws->table->set($request->fd, ['name'=>$name]);
	if(!$ret){
		var_dump($ret);
	}
    
	// 欢迎语
	$GLOBALS['data']['code'] = 1;
	$GLOBALS['data']['msg'] = "hello, welcome! {$name}\n";
    $ws->push($request->fd, json_encode($GLOBALS['data']));
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";
	
	$data = $GLOBALS['data'];
	
	$tab_data = $ws->table->get($frame->fd);
	
	$data['code'] = 0;
	$data['data'] = ['fd'=>$frame->fd, 'name'=>$tab_data['name'], 'data'=>$frame->data, 'status'=>1];
	$data['msg'] = '';
	
	$fd1 = $fd2 = $frame->fd;
	
	$rs = $ws->push($frame->fd, json_encode($data));	// 向当前fd推送消息（可选）
	// 向非当前fd推送消息
	while(($fd1++ && $ws->table->exist($fd1) && $fd = $fd1) || ($fd2-- && $ws->table->exist($fd2) && $fd = $fd2)){
		$rs = $ws->push($fd, json_encode($data));
		var_dump('fd:'.$fd);
	}
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
	// 移除保存的fd
	$ws->table->del($fd);
    echo "client-{$fd} is closed\n";
});

$ws->start();