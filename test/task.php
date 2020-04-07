<?php
$serv = new Swoole\Server("127.0.0.1", 9501);

//设置异步任务的工作进程数量
$serv->set(array(
	'task_worker_num' => 4,
	'task_enable_coroutine'=>true		//V4.2.12 起如果开启了 task_enable_coroutine 则回调函数原型是
));

//此回调函数在worker进程中执行
$serv->on('receive', function($serv, $fd, $from_id, $data) {
	echo "#### onReceive ####".PHP_EOL;
	echo "worker_pid: {$serv->worker_pid}".PHP_EOL;
	echo "from_id: {$from_id}".PHP_EOL;
	echo "客户端:{$fd} 发来的data:{$data}".PHP_EOL;

    //投递异步任务
    $task_id = $serv->task($data);
    echo "Dispatch AsyncTask: id=$task_id\n";
});

//处理异步任务(此回调函数在task进程中执行，未开启task_enable_coroutine=true)
/*
$serv->on('task', function ($serv, $task_id, $from_id, $data) {
    echo "#### onTask ####".PHP_EOL;
	echo "task_id: {$task_id}".PHP_EOL;
	echo "from_id: {$from_id}".PHP_EOL;
    echo "#{$serv->worker_id} onTask: [PID={$serv->worker_pid}]: task_id={$task_id}".PHP_EOL;

    //返回任务执行的结果
    $serv->finish("$data -> OK");
});
*/

//V4.2.12 起如果开启了 task_enable_coroutine 则回调函数原型是
$serv->on('task', function ($serv, $task) {
    echo "#### onTask ####".PHP_EOL;
	var_dump($task);

    //返回任务执行的结果
    $task->finish("OK");
});

//处理异步任务的结果(此回调函数在worker进程中执行)
$serv->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});

$serv->on('close', function ($serv, $fb) {
    echo "Client Close.".PHP_EOL;
});

$serv->start();