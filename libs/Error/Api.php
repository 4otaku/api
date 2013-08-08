<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

class ErrorApi extends Error
{
	const
		INCORRECT_URL = 410,
		MISSING_INPUT = 420,
		INCORRECT_INPUT = 430,
		UNKNOWN_ERROR = 440,
		INSUFFICIENT_RIGHTS = 450,
		TAG_EXISTS = 600,
		COMMENT_PARENT_DO_NOT_EXIST = 820;
}
