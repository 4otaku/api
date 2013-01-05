<?php

class Api_Read_Art_Artist extends Api_Read_Art_Pool
{
	protected $table = 'art_artist';
	protected $fields = array('aa.id', 'aa.text', 'u.login as artist');

	protected function get_data($ids) {
		$data = $this->db->set_counter()->join('user', 'u.id = aa.id_user')
			->get_table($this->table, $this->fields, $this->db->array_in('aa.id', $ids), $ids);

		$this->add_answer('data', $data);
		$this->add_answer('count', $this->db->get_counter());
	}
}
