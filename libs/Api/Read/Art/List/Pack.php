<?php

namespace Otaku\Api;

class Api_Read_Art_List_Pack extends Api_Read_Art_List_Abstract
{
	protected $item_type = 3;
	protected $table = 'art_pack';
	protected $fields = array('id', 'title', 'cover', 'sortdate');

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$cover = array();
		$nocover = array();
		foreach ($data as &$item) {
			$item['md5'] = false;
			if (!empty($item['cover'])) {
				$cover[] = $item['cover'];
			} else {
				$nocover[] = $item['id'];
			}
		}

		if (!empty($nocover)) {
			$links = $this->db->order('order', 'asc')->group('id_pack')
				->get_table('art_pack_item', array('id_pack', 'id_art'),
					$this->db->array_in('id_pack', $nocover), $nocover);

			foreach ($data as &$item) {
				foreach ($links as $link) {
					if ($item['id'] == $link['id_pack']) {
						$item['cover'] = $link['id_art'];
						$cover[] = $item['cover'];
						break;
					}
				}
			}
			unset($item);
		}

		if (!empty($cover)) {
			$covers = $this->db->get_table('art', array('id', 'md5'),
				$this->db->array_in('id', $cover), $cover);

			foreach ($data as &$item) {
				foreach ($covers as $cover) {
					if ($item['cover'] == $cover['id']) {
						$item['md5'] = $cover['md5'];
						break;
					}
				}
			}
			unset($item);
		}
	}

	protected function add_tag_count_join($query) {
		$query->join('art_pack_tag_count', 'at.id = aptc.id_tag');
	}
}
