<?php

class Api_Update_Art_Variation extends Api_Update_Abstract
{
	protected $next_variation_id = false;

	public function process()
	{
		$id = $this->get('id');
		$add = (array) $this->get('add');
		$remove = (array) $this->get('remove');

		if (empty($id) || (empty($add) && empty($remove))) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		foreach ($add as $item) {
			try {
				$this->do_insert($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($remove as $item) {
			try {
				$this->do_delete($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		$this->set_success(true);
	}

	protected function do_insert($id, $data)
	{
		if (!isset($data['id']) || !is_numeric($data['id'])) {
			throw new Error_Api(Error_Api::INCORRECT_INPUT);
		}

		if (!$this->next_variation_id) {
			$max = $this->db->order('id_parent_order')->get_field(
				'art', 'id_parent_order', 'id_parent = ?', $id);
			$this->next_variation_id = $max + 1;
		}

		$this->db->update('art', array(
			'id_parent' => $id,
			'id_parent_order' => $this->next_variation_id,
		), (int) $data['id']);

		$this->next_variation_id++;
	}

	protected function do_delete($id, $data)
	{
		$data = (int) $data;

		if (empty($data)) {
			throw new Error_Api(Error_Api::INCORRECT_INPUT);
		}

		$this->db->update('art', array(
			'id_parent' => $data['id'],
			'id_parent_order' => 0,
		), (int) $data['id']);
	}
}