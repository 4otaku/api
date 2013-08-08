<?php

namespace Otaku\Api;

class ApiReadHeadMenu extends ApiReadAbstract
{
	public function process() {

		$menu = $this->db->order('order', 'asc')
			->get_full_vector('head_menu');
		$return = array();
		if (!empty($menu)) {
			foreach ($menu as $key => $element) {
				if ($element['parent'] == 0) {
					$return[$key] = $element;
					$return[$key]['items'] = array();
					unset($menu[$key]);
				}
			}

			foreach ($menu as $key => $element) {
				if (array_key_exists($element['parent'], $return)) {
					$return[$element['parent']]['items'][$key] = $element;
				}
			}
		}

		$this->add_answer('menu', $return);

		$this->set_success(true);
	}
}
