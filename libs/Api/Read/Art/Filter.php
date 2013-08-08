<?php

namespace Otaku\Api;

class Api_Read_Art_Filter
{
	public static $not_deleted = array(
		'name' => 'state',
		'meta_type' => Meta::STATE,
		'operator' => Meta::IS,
		'value' => 'deleted',
		'reverse' => true
	);
	public static $translated = array(
		'name' => 'state',
		'meta_type' => Meta::TRANSLATION_DATE,
		'operator' => Meta::MORE,
		'value' => 0,
		'reverse' => false
	);
	public static $commented = array(
		'name' => 'state',
		'meta_type' => Meta::COMMENT_DATE,
		'operator' => Meta::MORE,
		'value' => 0,
		'reverse' => false
	);
}
