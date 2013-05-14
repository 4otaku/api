<?php

abstract class Api_Create_Comment_Abstract extends Api_Create_Abstract
{
	public function process()
	{
		$title = (string) $this->get('title');
		$mail = (string) $this->get('mail');
		$text = (string) $this->get('text');
		$text = trim($text);

		if (empty($text)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$this->set_success(true);
	}
}
