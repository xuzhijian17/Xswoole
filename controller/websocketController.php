<?php
namespace Xswoole\controller;

// require __DIR__.'/Controller.php';

use Xswoole\core\Controller;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class websocketController extends Controller {
    protected $chan;

	public function __construct($action, $path_info) {
        $this->chan = new Channel(1);

        parent::__construct($action, $path_info);
    }

    public function index($request, $ws) {
    	$cid = Coroutine::getCid();
		$pid = Coroutine::getPcid();
        echo "#### websocket ####".PHP_EOL;
        echo "cid：{$cid}	pid：{$pid}".PHP_EOL;
        echo $request->getData();
        echo "########".PHP_EOL.PHP_EOL;
        var_dump($request->server);
        

        // 升级为websocket协议
        $ws->upgrade();
        while (true) {
	        $frame = $ws->recv();
	        if ($frame === false) {
	            echo "error : " . swoole_last_error() . "\n";
	            break;
	        } else if ($frame == '') {
	            break;
	        } else {
	        	$data = json_decode($frame->data,true);

	        	// var_dump($this->chan->errCode,$this->chan->capacity,$this->chan->stats());

		    	// 异步处理任务
	        	$tid = $this->task($data);
	        	
	        	// 直接返回响应
	            $ws->push(json_encode($data));

	            // 恢复子协程，开始处理任务
	            Coroutine::resume($tid);


	            // 从通道中取出数据，并使用websocket响应数据
	            $data = $this->chan->pop();
	            $ws->push(json_encode($data));
	        }
	    }
    }

    public function task($data)
    {
    	// 开启子协程处理任务
    	$cid = go(function() use ($data) {
	    	$cid = Coroutine::getCid();
	    	$pid = Coroutine::getPcid();
	    	echo "#### task ####".PHP_EOL;
	        echo "cid：{$cid}	pid：{$pid}".PHP_EOL;
	        echo "########".PHP_EOL.PHP_EOL;
    		// 挂起子协程
    		Coroutine::suspend();

	    	// Todo
	    	for($i = 1 ; $i <= 5 ; $i ++ ) {
	            Coroutine::sleep(2);
	            echo "Task {$cid} 已完成了 {$i}/5 的任务".PHP_EOL;
	        }

	        $this->finish($data);
    	});
    	
        return $cid;
    }

    public function finish($data)
    {
    	echo "#### finish ####".PHP_EOL;

    	// 任务完成，将数据写入通道
    	$data['sex'] = 1;
	    $this->chan->push($data);
    }

}