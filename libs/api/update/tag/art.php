<?php

class Api_Update_Tag_Art extends Api_Update_Abstract
{
	public function process()
	{
		$id = (int) $this->get('id');
		$name = (string) $this->get('name');
		$variant = $this->get('variant');
		$color = $this->get('color');
		$merge = (int) $this->get('merge');

		$no_changes = (empty($merge) && $color === null &&
			$variant === null && empty($name));

		if (empty($id) || $no_changes) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if ($variant !== null) {
			$this->db->delete('art_tag_variant', 'id_tag = ?', $id);
			$variant = empty($variant) ? array() : (array) $variant;
			foreach((array) $variant as $item) {
				$item = (string) $item;
				if (
					$this->db->get_count('art_tag', 'name = ?', $item) ||
					$this->db->get_count('art_tag_variant', 'name = ?', $item)
				) {
					$this->add_error(Error_Api::TAG_EXISTS);
				} else {
					$this->db->insert('art_tag_variant', array(
						'id_tag' => $id,
						'name' => $item
					));
				}
			}
		}

		if ($color !== null) {
			$color = preg_replace('/[^a-f\d]/ui', '', (string) $color);
			$color = substr($color, 0, 6);
			$this->db->update('art_tag', array('color' => $color), $id);
		}

		if (!empty($name)) {
			if ($this->db->get_count('art_tag_variant', 'name = ?', $name)) {
				$this->add_error(Error_Api::TAG_EXISTS);
			} else {
				$success =
					$this->db->update('art_tag', array('name' => $name), $id);
				if (!$success) {
					$this->add_error(Error_Api::TAG_EXISTS);
				}
			}
		}

		if (!empty($merge)) {
			$tag = $this->db->get_full_row('art_tag', $merge);
			if (!$tag) {
				$this->add_error(Error_Api::INCORRECT_INPUT);
			} else {
				$this->db->update('art_tag_variant', array('id_tag' => $id),
					'id_tag = ?', $merge);
				$this->db->insert('art_tag_variant', array(
					'id_tag' => $id,
					'name' => $tag['name']
				));
				$this->db->delete('art_tag', $merge);

				$arts_first = $this->db->get_vector('meta', 'item_id',
					'item_type = ? and meta_type = ? and meta = ?',
					array(Meta::ART, Meta::ART_TAG, $id), false);
				$arts_second = $this->db->get_vector('meta', 'item_id',
					'item_type = ? and meta_type = ? and meta = ?',
					array(Meta::ART, Meta::ART_TAG, $merge), false);

				$delete = array_intersect($arts_first, $arts_second);

				$this->db->delete('meta',
					'item_type = ? and meta_type = ? and meta = ? and ' .
						$this->db->array_in('item_id', $delete),
					array_merge(array(Meta::ART, Meta::ART_TAG, $merge), $delete));
				$this->db->update('meta', array('meta' => Database_Action::get(
						Database_Action::DECREMENT)),
					'item_type = ? and meta_type = ? and ' .
						$this->db->array_in('item_id', $delete),
					array_merge(array(Meta::ART, Meta::TAG_COUNT), $delete));

				$update = array_diff($arts_second, $arts_first);

				$this->db->update('meta', array('meta' => $id),
					'item_type = ? and meta_type = ? and meta = ? and ' .
						$this->db->array_in('item_id', $update),
					array_merge(array(Meta::ART, Meta::ART_TAG, $merge), $update));
			}
		}


		$this->set_success(true);
	}
}
