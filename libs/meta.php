<?php

class Meta
{
	const
		ART_TAG = 1,
		STATE = 2,
		ART_PACK = 3,
		ART_GROUP = 4,
		ART_MANGA = 5,
		ART_ARTIST = 6,
		ART_RATING = 7,
		COMMENT_COUNT = 9,
		COMMENT_DATE = 10,
		TAG_COUNT = 11;

	const
		EQUAL = '=',
		NOT = '!=',
		LESS = '<',
		MORE = '>';

	public static function parse($string) {
		$const = strtoupper($string);

		if (!defined('self::' . $const)) {
			return null;
		}

		return constant('self::' . $const);
	}
}