<?php

/**
	URL 地址分发尝试
	apache rewrite配置示例:
	RewriteEngine on
	RewriteCond /data0/ecard/htdocs/%{REQUEST_FILENAME} !-f
	RewriteCond /data0/ecard/htdocs/%{REQUEST_FILENAME} !-d
	RewriteCond /data0/ecard/htdocs/%{REQUEST_FILENAME}/index.php !-f
	RewriteRule ^(.*)$ /routing.php
*/


require_once '../global.php';
$router = new Routing_Router();
$router->setProgramBasePath(PIC_HTDOCS_PATH);
$router->setNotFoundProgram('common' .DS. '404.php');

$router->loadRules(include(dirname(__FILE__) .DS. 'routing_rules.php'));

if ($router->doRoute() === false)
{
	$router->notFound();
}

exit;
