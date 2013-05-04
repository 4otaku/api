<?php

class Api_Read_Art_Artist extends Api_Read_Art_Pool
{
	protected $table = 'art_artist';
	protected $fields = array('aa.id', 'aa.text', 'u.login as artist',
		'aa.id_user');

	protected function get_data($ids) {
		$data = $this->db->set_counter()->join('user', 'u.id = aa.id_user')
			->get_table($this->table, $this->fields,
				$this->db->array_in('aa.id', $ids), $ids);
		foreach ($data as &$item) {
			$item['is_author'] = ($item['id_user'] == $this->get_user());
			unset($item['id_user']);
		}

		$this->add_answer('count', $this->db->get_counter());
		return $data;
	}
}
