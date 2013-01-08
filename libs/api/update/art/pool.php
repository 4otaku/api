<?php

abstract class Api_Update_Art_Pool extends Api_Update_Abstract
{
	protected $table;

	public function process()
	{
		$id = $this->get('id');
		$add = (array) $this->get('add');
		$remove = (array) $this->get('remove');

		if (empty($id) || (empty($add) && empty($remove))) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$meta = Meta::parse($this->table);

		foreach ($add as $item) {
			if (isset($item['id']) && $this->check_pool($item['id'])) {
				$this->add_item($id, $item);
				$this->add_meta(Meta::ART, $id, $meta, $item['id']);
			}
		}

		foreach ($remove as $item) {
			if (isset($item['id'])) {
				$this->remove_item($id, $item);
				$this->remove_meta(Meta::ART, $id, $meta, $item['id']);
			}
		}

		$this->set_success(true);
	}

	abstract protected function add_item($id, $data);
	abstract protected function remove_item($id, $data);

	protected function check_pool($id)
	{
		return (bool) $this->db->get_count($this->table, $id);
	}
}