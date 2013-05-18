<?php

class Api_Read_Art_Tag extends Api_Read_Tag
{
	protected $fields = array('id', 'name', 'color');
	protected $table = 'art_tag';

	protected function add_additional_data(&$data)
	{
		$ids = array();
		$links = array();
		foreach ($data as &$item) {
			$item['variant'] = array();
			$ids[] = $item['id'];
			$links[$item['id']] = &$item['variant'];
		}

		$variants = $this->db->get_table('art_tag_variant',
			array('id_tag', 'name'), $this->db->array_in('id_tag', $ids), $ids);

		foreach ($variants as $variant) {
			$links[$variant['id_tag']][] = $variant['name'];
		}

		return $data;
	}
}