<?php

use Otaku\Api\ApiRequest;
use Otaku\Api\ApiAbstract;
use Otaku\Api\ApiError;
use Otaku\Framework\Config;

include 'framework/init.php';

new Autoload(array(
	'Api' => LIBS,
	'Framework' => FRAMEWORK_LIBS
), FRAMEWORK_EXTERNAL);

Config::getInstance()->parse('define.ini', true);

$url = explode('/', preg_replace('/\?[^\/]+$/', '', $_SERVER['REQUEST_URI']));
array_shift($url);
// Нас используют как субмодуль, съедаем еще одну секцию запроса
if ($_SERVER['DOCUMENT_ROOT'] != ROOT_DIR) {
	array_shift($url);
}

$class = 'Otaku\Api\Api' . implode('', array_map('ucfirst', $url));

if (!class_exists($class) || !is_subclass_of($class, 'Otaku\Api\ApiAbstract')) {
	$class = 'Otaku\Api\ApiError';
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
