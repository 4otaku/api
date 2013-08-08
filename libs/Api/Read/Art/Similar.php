<?php

namespace Otaku\Api;

class Api_Read_Art_Similar extends Api_Read_Abstract
{
	protected $default_per_page = 20;

	public function process()
	{
		$offset = $this->get_offset();
		$per_page = $this->get_per_page();

		$items = $this->db->set_counter()->order('id_first')->order('id_second')
			->limit($per_page, $offset)->get_full_table('art_similar');
		$count = $this->db->get_counter();

		$ids = array();
		foreach ($items as $item) {
			$ids[] = $item['id_first'];
			$ids[] = $item['id_second'];
		}
		$ids = array_unique($ids);
		$arts = $this->db->get_vector('art', array('id', 'md5', 'ext', 'height',
			'width', 'weight', 'created', 'source', 'animated', 'id_parent'),
			$this->db->array_in('id', $ids), $ids, false);
		foreach ($items as &$item) {
			$item = array(
				'first' => $arts[$item['id_first']],
				'second' => $arts[$item['id_second']],
			);
		}

		$this->add_answer('count', $count);
		$this->add_answer('data', $items);
		$this->set_success(true);
	}
}