<?php
namespace Xswoole\core;

require __DIR__.'/Route.php';

// use Xswoole\Route;
use Swoole\Coroutine;
// use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Server;


class XswooleServer{

    protected $cid;
    protected $serv;
    
    public function __construct($host, $port) {
        $this->cid = Coroutine::getCid();
        echo "### Cid {$this->cid} ###".PHP_EOL;

        $this->serv = new Server($host, $port);

        $this->route = new Route($this->serv);
        
    }

    public function run($routes){
        // 路由分发
        $this->route->dispatch($routes);
        // 启动服务
        $this->serv->start();
    }
}

