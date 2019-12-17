<?php
namespace Xswoole;

require __DIR__.'/core/XswooleServer.php';
require __DIR__.'/config.php';

use Xswoole\core\XswooleServer;

$routes = include __DIR__.'/route.php';
go(function () use ($routes) {
    $server = new XswooleServer('0.0.0.0', 9501);
    $server->run($routes);
});
