<?php

abstract class Api_Update_Art_Pool_Sortable extends Api_Update_Art_Pool
{
	public function process()
	{
		parent::process();

		if ($this->get('order')) {
			$this->do_sort($this->get('id'), $this->get('order'));
		}
	}

	protected function have_changes()
	{
		return parent::have_changes() || $this->get('order');
	}

	protected function do_sort($id, $order)
	{
		$this->db->begin();

		$id_field = $this->get_id_field();

		$old_order = $this->db->order('order', 'asc')->get_vector(
			$this->table . '_item', array('order', 'id_art'),
			$id_field . ' = ?', $id);

		$max = max(array_keys($old_order));

		$old_keys = array();
		foreach ($order as $art_id) {
			$old_key = array_search($art_id, $old_order);
			if ($old_key === false) {
				$old_key = $max + 1;
				$max++;
			}
			$old_keys[] = $old_key;
		}

		sort($old_keys);
		foreach ($order as $art_id) {
			$old_order[array_shift($old_keys)] = $art_id;
		}

		$this->db->update($this->table . '_item',
			array('order' => Database_Action::get(Database_Action::ADD, $max)),
			$id_field . ' = ?', $id);

		foreach ($old_order as $key => $art_id) {
			$this->db->replace($this->table . '_item',
				array('order' => $key, $id_field => $id, 'id_art' => $art_id),
				array($id_field, 'id_art'));
		}

		$this->db->commit();
	}
}