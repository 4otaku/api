<?php

class Model_Board extends Model_Abstract
{
	// Поля таблицы
	protected static $fields = array(
		'id',
		'type',
		'thread',
		'updated',
		'name',
		'trip',
		'pretty_text',
		'text',
		'pretty_date',
		'sortdate',
		'cookie',
		'ip',
	);

	// Название таблицы
	protected static $table = 'board';
}
