<?php

class Api_Read_Art_List_Manga extends Api_Read_Art_List_Abstract
{
	protected $item_type = 5;
	protected $table = 'art_manga';
	protected $fields = array('id', 'title', 'sortdate');

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$nocover = array();
		foreach ($data as &$item) {
			$item['md5'] = 'error';
			$item['cover'] = false;
			$nocover[] = $item['id'];
		}

		if (!empty($nocover)) {
			$links = $this->db->order('order', 'asc')->group('id_manga')
				->get_table('art_manga_item', array('id_manga', 'id_art'),
					$this->db->array_in('id_manga', $nocover), $nocover);

			$cover = array();
			foreach ($data as &$item) {
				foreach ($links as $link) {
					if ($item['id'] == $link['id_manga']) {
						$item['cover'] = $link['id_art'];
						$cover[] = $item['cover'];
						break;
					}
				}
			}
			unset($item);

			$covers = $this->db->get_table('art', array('id', 'md5'),
				$this->db->array_in('id', $cover), $cover);

			foreach ($data as &$item) {
				foreach ($covers as $cover) {
					if ($item['cover'] == $cover['id']) {
						$item['md5'] = $cover['md5'];
						unset($item['cover']);
						break;
					}
				}
			}
			unset($item);
		}
	}
}
