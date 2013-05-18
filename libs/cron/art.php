<?php

class Cron_Art extends Cron_Abstract
{
	protected function resize()
	{
		$request = new Api_Request_Inner(array(
			'filter' => array(array(
				'name' => 'art_tag',
				'type' => 'is',
				'value' => 'need_resize'
			)),
			'skip_meta' => 1,
			'per_page' => 1000
		));
		$worker = new Api_Read_Art_List($request);
		$data = $worker->process_request();
		var_dump($data);
		$arts = obj::db()->sql('select id, md5, extension from art where locate("|need_resize|",tag)');

		if (!empty($arts)) {

			foreach ($arts as $art) {
				$name = $art['md5'].'.'.$art['extension'];
				$path = ROOT_DIR.SL.'images'.SL.'booru'.SL.'full'.SL.$name;

				$worker = new Transform_Upload_Art($path, $name);

				$resized = $worker->resize();

				obj::db()->sql('update art set resized="'.$resized.'", tag=replace(tag,"|need_resize|","|") where id='.$art['id'],0);
			}
		}
	}
}