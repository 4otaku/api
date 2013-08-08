<?php

namespace Otaku\Api;

class ApiReadTipUser extends ApiReadAbstract
{
	public function process() {
		if (!$this->get('left')) {
			$condition = false;
			$params = false;
		} else {
			$left = $this->get('left');
			$length = mb_strlen($left);
			$condition = 'LEFT(login, ' . $length . ') = ?';
			$params = array($left);
		}

		$page = max(1, (int) $this->get('page'));
		$per_page = $this->get('per_page') ? $this->get('per_page') : 10;
		$per_page = min($per_page, 100);

		$this->db->set_counter()->limit($per_page, $per_page * ($page - 1));

		$users = $this->db->get_table('user', 'login', $condition, $params);

		$this->add_answer('data', array_values($users));
		$this->add_answer('count', $this->db->get_counter());
		$this->set_success(true);
	}
}
