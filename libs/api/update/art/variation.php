<?php

class Api_Update_Art_Variation extends Api_Update_Abstract
{
	protected $next_variation_id = false;

	public function process()
	{
		$id = $this->get('id');
		$add = (array) $this->get('add');
		$remove = (array) $this->get('remove');
		$order = (array) $this->get('order');

		if (empty($id) || (empty($add) && empty($remove) && empty($order))) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$actual_order = array();
		$previous_order = array_keys($this->db->order('id_parent_order', 'asc')
			->get_vector('art', 'id', 'id_parent = ?', $id));
		foreach ($order as $item) {
			if (in_array($item, $previous_order) && !in_array($item, $remove)) {
				array_push($actual_order, $item);
			}
		}
		foreach ($previous_order as $item) {
			if (!in_array($item, $actual_order) && !in_array($item, $remove)) {
				array_push($actual_order, $item);
			}
		}
		$id = reset($actual_order);

		foreach ($remove as $item) {
			try {
				$this->do_delete($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($actual_order as $pos => $item) {
			$this->db->update('art', array(
				'id_parent' => $id,
				'id_parent_order' => $pos,
			), (int) $item);
		}

		foreach ($add as $item) {
			try {
				$this->do_insert($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		$this->add_answer('parent', $id);
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
			'id_parent' => $data,
			'id_parent_order' => 0,
		), $data);
	}
}