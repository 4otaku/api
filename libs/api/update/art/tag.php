<?php

class Api_Update_Art_Tag extends Api_Update_Art_Abstract_Tag
{
	protected $count_table = 'art_tag_count';

	protected function get_item_type()
	{
		return Meta::ART;
	}

	protected function after_process($count, $id) {
		$type = $this->get_item_type();
		$this->remove_meta($type, $id, Meta::STATE, Meta::STATE_UNTAGGED);
		$this->remove_meta($type, $id, Meta::STATE, Meta::STATE_TAGGED);
		if ($count > 4) {
			$this->add_meta($type, $id, Meta::STATE, Meta::STATE_TAGGED);
		} else {
			$this->add_meta($type, $id, Meta::STATE, Meta::STATE_UNTAGGED);
		}
	}

	protected function get_count_insert_data($tag, $id) {
		return array_merge(parent::get_count_insert_data($tag, $id), array(
			'name' => $tag, 'original' => 1
		));
	}
}