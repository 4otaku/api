<?php

class Model_Recentchanges extends Model_Abstract
{
	// Поля таблицы
	protected static $fields = array(
		'rc_title',
		'rc_namespace',
		'rc_type',
		'rc_id',
		'rc_user_text',
	);

	// Поля таблицы представляющие из себя первичный ключ
	protected static $primary = array(
		'rc_id'
	);

	// Название таблицы
	protected static $table = 'recentchanges';
}
