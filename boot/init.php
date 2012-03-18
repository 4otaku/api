<?php

include_once 'constants.php';
include_once 'autoloader.php';
include_once 'error_handler.php';

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

Autoload::init(LIBS);

$url = explode('/', preg_replace('/\?[^\/]+$/', '', $_SERVER['REQUEST_URI']));
array_shift($url);

$class = 'Api_' . implode('_', array_map('ucfirst', $url));

if (!class_exists($class)) {
	$class = 'Api_Error';
	$request = new Api_Request('dummy');
} else {
	if (!empty($_GET['f']) && ctype_alpha($_GET['f'])) {
		$request_type = $_GET['f'];
		unset($_GET['f']);
		$request = new Api_Request($request_type);
	} else {
		$request = new Api_Request();
	}
}

$worker = new $class($request);

if (!($worker instanceOf Api_Abstract)) {
	$worker = new Api_Error($request);
}

echo $worker->process_request()->send_headers()->get_response();
