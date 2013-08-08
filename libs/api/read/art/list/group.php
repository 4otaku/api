<?php

namespace otaku\api;

class Api_Read_Art_List_Group extends Api_Read_Art_List_Abstract
{
	protected $item_type = 4;
	protected $table = 'art_group';
	protected $fields = array('id', 'title', 'sortdate');

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		foreach ($data as &$item) {
			$item['md5'] = false;
			$ids[] = $item['id'];
		}

		if (!empty($ids)) {
			$covers = $this->db->order('a.sortdate')->join('meta',
				'm.item_type = 1 and m.meta_type = ' . Meta::ART_GROUP . ' and m.id_item = a.id')
				->filter('meta', array(
					'item_type = 1',
					'id_item = id',
					'meta_type = ' . Meta::STATE,
					'meta = 4'
				), 'meta')->make_temp('art', array('m.meta', 'a.md5', 'a.sortdate'),
					$this->db->array_in('m.meta', $ids), $ids)->group('tmp.meta')
				->get_table('tmp', array('meta', 'md5'));

			foreach ($data as &$item) {
				foreach ($covers as $cover) {
					if ($item['id'] == $cover['meta']) {
						$item['md5'] = $cover['md5'];
						break;
					}
				}
			}
			unset($item);
		}
	}

	protected function add_tag_count_join($query) {
		$query->join('art_group_tag_count', 'at.id = agtc.id_tag');
	}
}
