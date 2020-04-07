<?php
namespace Xswoole\core;

// use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

class Controller {
    protected $action;
    protected $path_info;

    protected $rs = ['code'=>0,'msg'=>'success', 'data'=>[]];
    
	public function __construct($action, $path_info) {
        $this->action = $action;
        $this->path_info = $path_info;
    }

    public function init($request, $response) {

        if (strcmp($request->server['path_info'], $this->path_info) && $request->server['path_info'] != '/') {
        	$this->error($request, $response);
        }else{            
        	call_user_func_array([$this, $this->action], [$request, $response]);
        }
    }

	

    public function error($request, $response)
    {
        $this->rs['code'] = -1;
        $this->rs['msg'] = "pathinfo {$request->server['path_info']} error";

        $response->end(json_encode($this->rs).PHP_EOL);
    }
}