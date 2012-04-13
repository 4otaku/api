<?php

class Model_Post_Extra extends Model_Abstract
{
	// Поля таблицы
	protected static $fields = array(
		'id',
		'post_id',
		'name',
		'alias',
		'url',
		'order',
	);

	// Название таблицы
	protected static $table = 'post_extra';
}
