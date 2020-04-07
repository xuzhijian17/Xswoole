<?php

Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
    echo "timer_id #$timer_id, after 3000ms.\n";
    echo "param1 is $param1, param2 is $param2.\n";
	$cli = new Swoole\Coroutine\Http\Client('www.google.com', 80);
	$cli->get('/');
	echo 'www.google.com:'.$cli->statusCode.PHP_EOL;
	$cli->close();

	// 每14s执行一次该定时器，每次执行会新生成一个timer_id
    Swoole\Timer::tick(14000, function ($timer_id) {
        echo "timer_id #$timer_id, after 14000ms.\n";
		$cli = new Swoole\Coroutine\Http\Client('www.baidu.com', 80);
		$cli->get('/');
		echo 'www.baidu.com:'.$cli->statusCode.PHP_EOL;
		$cli->close();
    });
}, "A", "B");

