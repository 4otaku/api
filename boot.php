<?php

use Otaku\Api\ApiRequest;
use Otaku\Api\ApiAbstract;
use Otaku\Api\ApiError;

include 'framework/init.php';

Autoload::init(array(LIBS, EXTERNAL, FRAMEWORK_LIBS, FRAMEWORK_EXTERNAL), CACHE);

Config::parse('define.ini', true);

$url = explode('/', preg_replace('/\?[^\/]+$/', '', $_SERVER['REQUEST_URI']));
array_shift($url);
// Нас используют как субмодуль, съедаем еще одну секцию запроса
if ($_SERVER['DOCUMENT_ROOT'] != ROOT_DIR) {
	array_shift($url);
}

$class = 'Api_' . implode('_', array_map('ucfirst', $url));

if (!class_exists($class) || !is_subclass_of($class, 'Api_Abstract')) {
	$class = 'Api_Error';
	$request = new ApiRequest('dummy');
} else {
	if (!empty($_GET['f']) && ctype_alpha($_GET['f'])) {
		$request_type = $_GET['f'];
		unset($_GET['f']);
		$request = new ApiRequest($request_type);
	} else {
		$request = new ApiRequest();
	}
}

$worker = new $class($request);

if (!($worker instanceOf ApiAbstract)) {
	$worker = new ApiError($request);
}

echo $worker->process_request()->send_headers()->get_response();
