<?php

class Model_Post_Url extends Model_Abstract
{
	// Поля таблицы
	protected static $fields = array(
		'id',
		'url',
		'status',
		'lastcheck'
	);

	// Название таблицы
	protected static $table = 'post_url';

	const
		STATUS_WORKS = 1,
		STATUS_UNKNOWN = 2,
		STATUS_BROKEN = 3;
}
