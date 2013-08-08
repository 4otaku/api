<?php

namespace Otaku\Api;

class Api_Delete_Tag_Art extends Api_Delete_Abstract
{
	public function process()
	{
		$id = $this->get('id');

		if (empty($id)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		$this->db->begin();

		$types = array(Meta::ART, Meta::ART_PACK, Meta::ART_GROUP,
			Meta::ART_MANGA, Meta::ART_ARTIST);

		foreach ($types as $type) {

			$item_ids = $this->db->get_vector('meta', 'id_item',
				'item_type = ? and meta_type = ? and meta = ?',
				array($type, Meta::ART_TAG, $id), false);

			if (!empty($item_ids)) {
				$this->db->update('meta',
					array('meta' => Database_Action::get(Database_Action::DECREMENT)),
					'item_type = ? and meta_type = ? and ' .
						$this->db->array_in('id_item', $item_ids),
					array_merge(array($type, Meta::TAG_COUNT), $item_ids));
			}

			$this->db->delete('meta',
				'item_type = ? and meta_type = ? and meta = ?',
				array($type, Meta::ART_TAG, $id));
		}

		$this->db->delete('art_tag_count', 'id_tag = ?', $id);
		$this->db->delete('art_artist_tag_count', 'id_tag = ?', $id);
		$this->db->delete('art_pack_tag_count', 'id_tag = ?', $id);
		$this->db->delete('art_group_tag_count', 'id_tag = ?', $id);
		$this->db->delete('art_manga_tag_count', 'id_tag = ?', $id);
		$this->db->delete('art_tag_variant', 'id_tag = ?', $id);
		$this->db->delete('art_tag', $id);

		$this->db->commit();
	}
}