<?php

namespace Otaku\Api;

class ApiReadArtListArtist extends ApiReadArtListAbstract
{
	protected $item_type = 6;
	protected $table = 'art_artist';
	protected $fields = array('id', 'id_user', 'sortdate');

	protected function add_meta_data(&$data) {
		parent::add_meta_data($data);

		$ids = array();
		$users = array();
		foreach ($data as &$item) {
			$item['md5'] = false;
			$ids[] = $item['id'];
			$users[] = $item['id_user'];
		}

		if (!empty($users)) {
			$users = $this->db->get_table('user', array('id', 'login'),
				$this->db->array_in('id', $users), $users);
		}

		if (!empty($ids)) {
			$covers = $this->db->order('a.sortdate')->join('meta',
				'm.item_type = 1 and m.meta_type = ' . Meta::ART_ARTIST . ' and m.id_item = a.id')
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

				foreach ($users as $user) {
					if ($item['id_user'] == $user['id']) {
						$item['artist'] = $user['login'];
						break;
					}
				}
			}
			unset($item);
		}
	}

	protected function add_tag_count_join($query) {
		$query->join('art_artist_tag_count', 'at.id = aatc.id_tag');
	}
}
