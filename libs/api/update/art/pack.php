<?php

class Api_Update_Art_Pack extends Api_Update_Art_Pool_Sortable
{
	protected $table = 'art_pack';

	public function process()
	{
		parent::process();

		if ($this->get('cover')) {
			$this->db->update($this->table,
				array('cover' => (int) $this->get('cover')), $this->get('id'));
		}
	}

	protected function have_changes()
	{
		return parent::have_changes() || $this->get('cover');
	}

	protected function add_item($id, $data)
	{
		if (empty($data['filename'])) {
			$name = $this->db->get_row('art', array('md5', 'ext'), $data['id']);
			if (empty($name)) {
				return false;
			} else {
				$data['filename'] = $name['md5'] . '.' . $name['ext'];
			}
		}

		$order = $this->db->order('order')->get_field('art_pack_item',
			'order', 'id_pack = ?', $id);

		$this->db->insert('art_pack_item', array(
			'id_pack' => $id,
			'id_art' => $data['id'],
			'order' => $order + 1,
			'filename' => $data['filename']
		));
		return true;
	}

	protected function remove_item($id, $data)
	{
		$this->db->delete('art_pack_item', 'id_pack = ? and id_art = ?',
			array($id, $data));

		if ($this->db->get_count('art_pack', 'id = ? and cover = ?',
			array($id, $data))) {

			$this->db->update('art_pack', array('cover' => 0), $id);
		}

		return true;
	}
}