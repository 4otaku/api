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
		TRANSLATOR = 12,
		TRANSLATION_DATE = 13,
		ART_PACK_TAG = 14,
		ART_GROUP_TAG = 15,
		ART_MANGA_TAG = 16;

	const
		IS = '=',
		EQUAL = '=',
		NOT = '!=',
		LESS = '<',
		MORE = '>';

	const
		STATE_UNAPPROVED = 1,
		STATE_APPROVED = 2,
		STATE_DISAPPROVED = 3,
		STATE_DELETED = 4,
		STATE_UNTAGGED = 5,
		STATE_TAGGED = 6;

	const
		ART = 1,
		POST = 2,
		PACK = 3,
		GROUP = 4,
		MANGA = 5,
		ARTIST = 6;

	public static function parse($string) {
		$const = strtoupper($string);

		if (!defined('self::' . $const)) {
			return null;
		}

		return constant('self::' . $const);
	}

	public static function value_needed() {
		return array(self::STATE, self::ART_TAG, self::TRANSLATOR);
	}
}
