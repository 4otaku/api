<?php

include_once 'constants.php';
include_once 'autoloader.php';
include_once 'error_handler.php';

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

Autoload::init(LIBS);

echo "123";
