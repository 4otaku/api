<?php

class Model_Api_Log extends Model_Abstract
{
	// Поля таблицы
	protected static $fields = array(
		'id',
		'uid',
		'ip',
		'type',
		'data',
		'date'
	);

	// Название таблицы
	protected static $table = 'api_log';
}
