<?php

namespace Otaku\Api;

class ApiReadArtListTranslation extends ApiReadArtListArt
{
	protected $fields = array('id', 'id_parent', 'md5', 'ext', 'animated');
	protected $default_sorter = 'translation_date';

	protected function get_default_filter() {
		$return = parent::get_default_filter();
		$return[] = ApiReadArtFilter::$translated;
		return $return;
	}

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		foreach ($data as &$item) {
			$ids[] = $item['id'];
			$item['translation_count'] = 0;
			$item['translator'] = array();
		}
		unset($item);

		$translation_count = $this->db->group('id_art')
			->get_vector('art_translation', array('id_art', 'count(*)'),
			'state = 1 and ' . $this->db->array_in('id_art', $ids), $ids);

		$translators = $this->db->order('at.sortdate', 'asc')
			->join('user', 'u.id = at.id_user')
			->get_table('art_translation', array('at.id_art', 'u.login'),
			'state != 3 and ' . $this->db->array_in('at.id_art', $ids), $ids);

		foreach ($translation_count as $id_art => $count) {
			foreach ($data as &$item) {
				if ($item['id'] == $id_art) {
					$item['translation_count'] = $count;
					continue 2;
				}
			}
			unset($item);
		}

		foreach ($translators as $translator) {
			foreach ($data as &$item) {
				if ($item['id'] == $translator['id_art']) {
					$item['translator'][] = $translator['login'];
					continue 2;
				}
			}
			unset($item);
		}

		foreach ($data as &$item) {
			$item['translator'] = array_unique($item['translator']);
		}
		unset($item);
	}
}
