<?php
$http = new Swoole\Http\Server("0.0.0.0", 9501);

$http->set(array(
	'task_worker_num' => 4,
));

$http->on('request', function ($request, $response) use ($http) {
    if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
        $response->end();
        return;
    }
	$response->header("Content-Type", "text/html; charset=utf-8");
	list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
	$path = __DIR__.'/Controllers/'.$controller.'.php';
	include_once($path);
    //根据 $controller, $action 映射到不同的控制器类和方法
    (new $controller)->$action($request, $response);
    //var_dump($controller, $action);
	
	if($action == 'task'){
		//投递异步任务
		$data = $request->getData();
		$task_id = $http->task($data);
		echo "Dispatch AsyncTask: id=$task_id\n";
	}
    
});

$http->on('task', function ($serv, $task_id, $from_id, $data) {
    echo "#### onTask ####".PHP_EOL;
	echo "task_id: {$task_id}".PHP_EOL;
	echo "from_id: {$from_id}".PHP_EOL;
    echo "#{$serv->worker_id} onTask: [PID={$serv->worker_pid}]: task_id={$task_id}".PHP_EOL;

    //返回任务执行的结果
    $serv->finish($data);
});

$http->on('finish', function ($serv, $task_id, $data) {
    echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
});

$http->on('close', function ($serv, $fb) {
    echo "Client Close.".PHP_EOL;
});

$http->start();