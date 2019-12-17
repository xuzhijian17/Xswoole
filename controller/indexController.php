<?php
namespace Xswoole\controller;

// require __DIR__.'/Controller.php';

use Xswoole\core\Controller;

use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class indexController extends Controller {

	public function __construct($action, $path_info) {
        parent::__construct($action, $path_info);
    }

	public function index($request, $response) {
        echo "#### index ####".PHP_EOL;
        echo $request->getData();
        echo "########".PHP_EOL.PHP_EOL;
        var_dump($request->server);

        $response->end("index".PHP_EOL);
    }

    public function test($request, $response) {
        echo "#### test ####".PHP_EOL;
        echo $request->getData();
        echo "########".PHP_EOL.PHP_EOL;
        var_dump($request->server);
        
        // 直接返回响应
        $response->end("test".PHP_EOL);
    }

}