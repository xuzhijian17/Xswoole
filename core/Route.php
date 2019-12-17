<?php
namespace Xswoole\core;

// include __DIR__.'/../controller/Controller.php';
require __DIR__.'/Controller.php';

class Route
{
    protected $serv;

    static private $instance;

    public function __construct($serv) {
        $this->serv = $serv;
    }

    public function route($key, $value)
    {
        foreach ($value as $path_info => $action) {
            $controller = '\Xswoole\\controller\\'.$key.'Controller';

            $this->serv->handle($path_info, [new $controller($action, $path_info), 'init']);
        }
    }


    public function dispatch($routes){

        foreach ($routes as $key => &$value) {
            include __DIR__.'/../controller/'.$key.'Controller.php';
            $this->route($key, $value);
        }
    }
}

