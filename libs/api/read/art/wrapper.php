<?php

class Api_Read_Art extends Api_Read_Abstract
{
	protected $fields = array('id', 'id_parent', 'id_user', 'md5', 'ext',
		'width', 'height', 'weight', 'resized', 'animated', 'comment',
		'source', 'sortdate', 'created');

	public function process() {

		$ids = (array) $this->get('id');

		if (empty($ids)) {
			$this->add_error(Error_Api::INCORRECT_INPUT);
			return;
		}

		$deleted = $this->db->get_field('state', 'id', 'name = ?', 'deleted');

		$data = $this->db->set_counter()->filter('meta', array('item_type = 1',
			'id_item = id', 'meta_type = ' . Meta::STATE, 'meta = ' . $deleted),
			'meta')->get_vector('art', $this->fields, $this->db->array_in('id', $ids), $ids);

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
			$this->db->array_in('id', $users), $users);
		foreach ($data as &$item) {
			$item['user'] = $users[$item['id_user']];
		}
		unset($item);
		$rating = $this->db->get_vector('meta', array('id_item', 'meta'),
			'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_RATING .
			' and ' . $this->db->array_in('id_item', $ids), $ids);
		foreach ($data as &$item) {
			$item['rating'] = $rating[$item['id']];
		}
		unset($item);

		$cookie = $this->get_cookie();
		if ($this->get('add_voted') && $cookie) {
			$voted = $this->db->get_vector('art_rating', array('id_art', 'rating'),
				'cookie	= ? or ip = ? and ' . $this->db->array_in('id_art', $ids),
				array_merge(array($cookie, $this->get_ip()), $ids));
			foreach ($data as &$item) {
				$item['voted'] = isset($voted[$item['id']]) ? $voted[$item['id']] : 0;
			}
		}

		if ($this->get('add_tags')) {
			$tags = $this->db->join('art_tag', 'at.id = m.meta')->
				join('art_tag_count', 'at.id = atc.id_tag and atc.original = 1')->
				get_table('meta', array('m.id_item', 'at.*', 'atc.count'),
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_TAG .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
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
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::STATE .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
			foreach ($data as &$item) {
				$item['state'] = array();
			}
			unset($item);
			foreach ($states as $state) {
				$link = &$data[$state['id_item']]['state'];
				$link[] = $state['name'];
			}
		}

		if ($this->get('add_similar')) {
			$similar = $this->db->order('id_parent_order', 'asc')->get_table('art',
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
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_GROUP .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
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
				->join('art_manga_item', 'm.id_item = ami.id_art and am.id = ami.id_manga')
				->order('am.sortdate')->get_table('meta',
					array('m.id_item', 'am.id', 'am.title', 'ami.order'),
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_MANGA .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
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
				->join('art_pack_item', 'm.id_item = api.id_art and ap.id = api.id_pack')
				->order('ap.sortdate')->get_table('meta',
					array('m.id_item', 'ap.id', 'ap.title', 'api.order', 'api.filename'),
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_PACK .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
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
					'm.item_type = ' . Meta::ART . ' and m.meta_type = ' . Meta::ART_ARTIST .
					' and ' . $this->db->array_in('m.id_item', $ids), $ids);
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
			foreach ($data as &$item) {
				$item['translation'] = array();
				$item['translator'] = array();
			}
			unset($item);

			$translations = $this->db->get_table('art_translation',
				array('id_translation', 'id_art', 'x1', 'x2', 'y1', 'y2', 'text'),
				'state = 1 and ' . $this->db->array_in('id_art', $ids), $ids);
			foreach ($translations as $translation) {
				$link = &$data[$translation['id_art']]['translation'];
				unset($translation['id_art']);
				$link[] = $translation;
			}

			$translators = $this->db->order('at.sortdate', 'asc')
				->join('user', 'u.id = at.id_user')
				->get_table('art_translation', array('at.id_art', 'u.login'),
				'state != 3 and ' . $this->db->array_in('at.id_art', $ids), $ids);
			foreach ($translators as $translator) {
				$link = &$data[$translator['id_art']]['translator'];
				$link[] = $translator['login'];
			}
			foreach ($data as &$item) {
				$item['translator'] = array_unique($item['translator']);
			}
			unset($item);
		}

		$this->add_answer('data', array_values($data));
		$this->set_success(true);
	}
}
