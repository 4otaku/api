<?php

class Api_Read_Art extends Api_Read_Abstract
{
	protected $fields = array('id', 'id_parent', 'id_user', 'md5', 'ext',
		'width', 'height', 'weight', 'resized', 'animated', 'source', 'sortdate', 'created');

	public function process() {

		$ids = (array) $this->get('id');

		if (empty($ids)) {
			$this->add_error(Error_Api::INCORRECT_INPUT);
			return;
		}

		$deleted = $this->db->get_field('state', 'id', 'name = ?', 'deleted');

		$sql = $this->db->set_counter()->filter('meta', array(
			'item_type = 1',
			'id_item = id',
			'meta_type = ' . Meta::STATE,
			'meta = ' . $deleted
		), 'meta');

		$data = $sql->get_vector('art',
			$this->fields, $this->db->array_in('id', $ids), $ids);

		$this->add_answer('count', $this->db->get_counter());

		$ids = array_keys($data);
		$parents = array();
		$users = array();
		foreach ($data as $key => &$item) {
			$item['id'] = $key;
			$parents[] = $item['id_parent'];
			$users[] = $item['id_user'];
		}
		unset($item);

		$users = $this->db->get_vector('user', array('id', 'login'),
			$sql->array_in('id', $users), $users);
		foreach ($data as &$item) {
			$item['user'] = $users[$item['id_user']];
		}
		unset($item);
		$rating = $this->db->get_vector('meta', array('id_item', 'meta'),
			'm.item_type = 1 and m.meta_type = ' . Meta::ART_RATING .
			' and ' . $sql->array_in('id_item', $ids), $ids);
		foreach ($data as &$item) {
			$item['rating'] = $rating[$item['id']];
		}
		unset($item);

		if ($this->get('add_tags')) {
			$tags = $this->db->join('art_tag', 'at.id = m.meta')->
				join('art_tag_count', 'at.id = atc.id_tag and atc.original = 1')->
				get_table('meta', array('m.id_item', 'at.*', 'atc.count'),
					'm.item_type = 1 and m.meta_type = ' . Meta::ART_TAG .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['tag'] = array();
			}
			unset($item);
			foreach ($tags as $tag) {
				$link = &$data[$tag['id_item']]['tag'];
				unset($tag['id_item']);
				unset($tag['id']);
				$link[] = $tag;
			}
		}

		if ($this->get('add_state')) {
			$states = $this->db->join('state', 's.id = m.meta')->
				get_table('meta', array('m.id_item', 's.*'),
					'm.item_type = 1 and m.meta_type = ' . Meta::STATE .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['state'] = array();
			}
			unset($item);
			foreach ($states as $state) {
				$link = &$data[$state['id_item']]['state'];
				$link[] = $state['name'];
			}
		}

		if ($this->get('add_comments')) {
			$comments = $this->db->order('sortdate')->get_table('comment', array('id', 'rootparent',
				'parent', 'id_item', 'username', 'email', 'text', 'editdate', 'sortdate'),
					'area = 1 and ' . $sql->array_in('id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['comment'] = array();
			}
			unset($item);
			foreach ($comments as $comment) {
				$link = &$data[$comment['id_item']]['comment'];
				unset($comment['id_item']);
				$link[] = $comment;
			}
		}

		if ($this->get('add_similar')) {
			$similar = $sql->order('id_parent_order', 'asc')->get_table('art',
				array('id', 'id_parent'), $this->db->array_in('id_parent', $parents), $parents);
			foreach ($data as &$item) {
				$item['similar'] = array();
				foreach ($similar as $art) {
					if ($item['id_parent'] == $art['id_parent']) {
						$item['similar'][] = $art['id'];
					}
				}
			}
			unset($item);
		}

		if ($this->get('add_groups')) {
			$groups = $this->db->join('art_group', 'ag.id = m.meta')
				->order('ag.sortdate')->get_table('meta',
					array('m.id_item', 'ag.id', 'ag.title'),
					'm.item_type = 1 and m.meta_type = ' . Meta::ART_GROUP .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['group'] = array();
			}
			unset($item);
			foreach ($groups as $group) {
				$link = &$data[$group['id_item']]['group'];
				unset($group['id_item']);
				$link[] = $group;
			}
		}

		if ($this->get('add_manga')) {
			$mangas = $this->db->join('art_manga', 'am.id = m.meta')
				->order('am.sortdate')->get_table('meta',
					array('m.id_item', 'am.id', 'am.title'),
					'm.item_type = 1 and m.meta_type = ' . Meta::ART_MANGA .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['manga'] = array();
			}
			unset($item);
			foreach ($mangas as $manga) {
				$link = &$data[$manga['id_item']]['manga'];
				unset($manga['id_item']);
				$link[] = $manga;
			}
		}

		if ($this->get('add_packs')) {
			$packs = $this->db->join('art_pack', 'ap.id = m.meta')
				->order('ap.sortdate')->get_table('meta',
					array('m.id_item', 'ap.id', 'ap.title'),
					'm.item_type = 1 and m.meta_type = ' . Meta::ART_PACK .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['pack'] = array();
			}
			unset($item);
			foreach ($packs as $pack) {
				$link = &$data[$pack['id_item']]['pack'];
				unset($pack['id_item']);
				$link[] = $pack;
			}
		}

		if ($this->get('add_artist')) {
			$artists = $this->db->join('art_artist', 'aa.id = m.meta')
				->order('aa.sortdate')->join('user', 'u.id = aa.id_user')
				->get_table('meta',
					array('m.id_item', 'aa.id', 'u.login as artist'),
					'm.item_type = 1 and m.meta_type = ' . Meta::ART_ARTIST .
					' and ' . $sql->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['artist'] = array();
			}
			unset($item);
			foreach ($artists as $artist) {
				$link = &$data[$artist['id_item']]['artist'];
				unset($artist['id_item']);
				$link[] = $artist;
			}
		}

		if ($this->get('add_translations')) {
			$translations = $this->db->get_table('art_translation',
				array('id_art', 'x1', 'x2', 'y1', 'y2', 'text'),
				'state = 1 and ' . $sql->array_in('id_art', $ids), $ids);

			foreach ($data as &$item) {
				$item['translation'] = array();
			}
			unset($item);
			foreach ($translations as $translation) {
				$link = &$data[$translation['id_art']]['translation'];
				unset($translation['id_art']);
				$link[] = $translation;
			}
		}

		$this->add_answer('data', array_values($data));
		$this->set_success(true);
	}
}
