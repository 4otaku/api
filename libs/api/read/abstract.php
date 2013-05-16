<?php

abstract class Api_Read_Abstract extends Api_Abstract
{
	protected $default_page = 1;
	protected $default_per_page = 100;
	protected $default_sorter = 'id';
	protected $default_sorter_order = 'desc';
	protected $max_per_page = 1000;

	public function process_request() {
		$this->add_answer('data', array());
		$this->add_answer('count', 0);
		return parent::process_request();
	}

	protected function get_per_page() {
		$per_page = $this->get('per_page');
		if (empty($per_page)) {
			return $this->default_per_page;
		}

		$per_page = (int) $per_page;
		if ($per_page <= 0 || $per_page > $this->max_per_page) {
			return $this->default_per_page;
		}

		return $per_page;
	}

	protected function get_offset() {
		$offset = (int) $this->get('offset');
		if ($offset >= 0) {
			return $offset;
		}

		$page = (int) $this->get('page');
		if (empty($page) || $page <= 0) {
			$page = $this->default_page;
		}

		return ($page - 1) * $this->get_per_page();
	}

	protected function get_sort_by() {
		$sorter = $this->get('sort_by');
		if (empty($sorter)) {
			return $this->default_sorter;
		}

		return (string) $sorter;
	}

	protected function get_sort_order() {
		$order = $this->get('sort_order');
		if (empty($order) || !in_array($order, array('asc', 'desc'))) {
			return $this->default_sorter_order;
		}

		return $order;
	}
}
