<?php

class Api_Read_Art_Tip_Tag extends Api_Read_Abstract
{
	public function process() {

		if (!$this->get('left')) {
			$this->add_error(Error_Api::MISSING_INPUT);
			return;
		}

		$left = $this->get('left');
		$length = mb_strlen($left);
		$page = max(1, (int) $this->get('page'));
		$per_page = $this->get('per_page') ? $this->get('per_page') : 10;
		$per_page = min($per_page, 100);

		$tags = $this->db->order('count')->limit($per_page * 2, $per_page * ($page - 1))
			->join('art_tag', 'at.id = atc.id_tag')->set_counter()
			->get_table('art_tag_count', array('atc.*', 'at.color'),
				'LEFT(at.name, ' . $length . ') = ?', $left);

		$return = array();
		foreach ($tags as $tag) {
			if (!isset($return[$tag['id_tag']]) || $tag['original']) {
				$return[$tag['id_tag']] = array(
					'name' => $tag['name'],
					'color' => $tag['color'],
					'count' => $tag['count'],
				);
			}
			if (count($return) >= $per_page) {
				break;
			}
		}

		$this->add_answer('data', array_values($return));
		$this->add_answer('count', $this->db->get_counter());
		$this->set_success(true);
	}
}
