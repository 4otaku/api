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

		$id_field = str_replace('art_', 'id_', $this->table);
		$max = $this->db->order('order')->limit(1)->get_field(
			$this->table . '_item', 'order', $id_field . ' = ?', $id);
		$this->db->update($this->table . '_item',
			array('order' => Database_Action::get(Database_Action::ADD, $max)),
			$id_field . ' = ?', $id);
		foreach ($order as $key => $art_id) {
			$this->db->replace($this->table . '_item',
				array('order' => $key + 1, $id_field => $id, 'id_art' => $art_id),
				array($id_field, 'id_art'));
		}

		$this->db->commit();
	}
}