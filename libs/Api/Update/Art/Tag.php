<?php

namespace Otaku\Api;

class ApiUpdateArtTag extends ApiUpdateArtAbstractTag
{
	protected $count_table = 'art_tag_count';

	protected function get_item_type()
	{
		return Meta::ART;
	}

	protected function after_process($count, $id) {
		$type = $this->get_item_type();

		$was_tagged = false;
		$was_untagged = false;
		$state = $this->db->get_table('meta', 'meta',
			'item_type = ? and id_item = ? and meta_type = ? and
				(meta = ? or meta = ?)',
			array($type, $id, Meta::STATE, Meta::STATE_UNTAGGED,
				Meta::STATE_TAGGED));

		foreach ($state as $item) {
			if ($item['meta'] == Meta::STATE_TAGGED) {
				$was_tagged = true;
			}
			if ($item['meta'] == Meta::STATE_UNTAGGED) {
				$was_untagged = true;
			}
		}

		$update = false;
		if ($count > 4) {
			if ($was_untagged) {
				$this->remove_meta($type, $id, Meta::STATE, Meta::STATE_UNTAGGED);
			}
			if (!$was_tagged) {
				$this->add_meta($type, $id, Meta::STATE, Meta::STATE_TAGGED);
				$update = true;
			}
		} else {
			if (!$was_untagged) {
				$this->add_meta($type, $id, Meta::STATE, Meta::STATE_UNTAGGED);
			}
			if ($was_tagged) {
				$this->remove_meta($type, $id, Meta::STATE, Meta::STATE_TAGGED);
				$update = true;
			}
		}

		if ($update) {
			$this->db->update('art', array('sortdate' =>
				$this->db->unix_to_date()), $id);
		}
	}

	protected function get_count_insert_data($tag, $id) {
		return array_merge(parent::get_count_insert_data($tag, $id), array(
			'name' => $tag, 'original' => 1
		));
	}
}