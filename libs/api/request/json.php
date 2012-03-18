<?php

class Api_Request_Json extends Api_Request_Input
{
	protected function convert($input) {
		return json_decode($input, true);
	}
}
