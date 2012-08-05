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
		TAG_COUNT = 11,
		TRANSLATION_DATE = 12;

	const
		IS = '=',
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

	public static function value_needed() {
		return array(self::STATE, self::ART_TAG);
	}
}
