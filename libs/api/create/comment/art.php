<?php

class Api_Create_Comment_Art extends Api_Create_Comment_Abstract
{
	protected function get_area()
	{
		return Meta::ART;
	}
}