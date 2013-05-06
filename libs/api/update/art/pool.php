<?php

abstract class Api_Update_Art_Pool extends Api_Update_Abstract
{
	protected $table;

	public function process()
	{
		$id = $this->get('id');

		if (empty($id) || !$this->have_changes()) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$meta = Meta::parse($this->table);

		foreach ((array) $this->get('add') as $item) {
			if (isset($item['id']) && $this->check_pool($item['id'])) {
				if ($this->add_item($id, $item)) {
					$this->add_meta(Meta::ART, $id, $meta, $item['id']);
				}
			}
		}

		foreach ((array) $this->get('remove') as $item) {
			if (isset($item['id'])) {
				if ($this->remove_item($id, $item)) {
					$this->remove_meta(Meta::ART, $id, $meta, $item['id']);
				}
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
}