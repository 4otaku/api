<?php

namespace Otaku\Api;

abstract class Api_Update_Art_Pool extends Api_Update_Abstract
{
	protected $table;

	public function process()
	{
		$id = $this->get('id');

		if (!$this->is_moderator() && $this->get('remove')) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		if (empty($id) || !$this->have_changes()) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!$this->check_pool($id)) {
			throw new Error_Api(Error_Api::INCORRECT_INPUT);
		}

		$meta = Meta::parse($this->table);

		foreach ((array) $this->get('add') as $item) {
			if (!$this->in_pool($id, $item['id'])) {
				if ($this->add_item($id, $item)) {
					$this->add_meta(Meta::ART, (int) $item['id'], $meta, $id);
				}
			}
		}

		foreach ((array) $this->get('remove') as $item) {
			if ($this->remove_item($id, (int) $item)) {
				$this->remove_meta(Meta::ART, (int) $item, $meta, $id);
			}
		}

		if ($this->get('title')) {
			$this->db->update($this->table,
				array('title' => $this->get('title')), $id);
		}

		if ($this->get('text') !== null) {
			$this->db->update($this->table,
				array('text' => $this->get('text')), $id);
		}

		$this->set_success(true);
	}

	protected function have_changes()
	{
		return $this->get('add') || $this->get('remove') ||
			$this->get('title') || $this->get('text') !== null;
	}

	abstract protected function add_item($id, $data);
	abstract protected function remove_item($id, $data);

	protected function check_pool($id)
	{
		return (bool) $this->db->get_count($this->table, $id);
	}

	protected function in_pool($pool, $id)
	{
		return (bool) $this->db->get_count($this->table . '_item',
			$this->get_id_field() . ' = ? and id_art = ?', array($pool, $id));
	}

	protected function get_id_field()
	{
		return str_replace('art_', 'id_', $this->table);
	}
}