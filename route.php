<?php

return [
	// 控制器名
	'index' => [
		'/' => 'index',		// 路由=>方法名
		'/index/index' => 'index',
		'/index/test' => 'test'
	],

	'websocket' => [
		'/websocket/index' => 'index'
	]
];