<?php

class Api_Read_Art_Nextprev extends Api_Read_Art_List_Art
{
	public function process() {
		$mode = $this->get('mode');
		if ($mode == 'translation') {
			$this->default_filter[] = Api_Read_Art_Filter::$translated;
			$this->default_sorter = 'translation_date';
		} elseif($mode == 'comment') {
			$this->default_filter[] = Api_Read_Art_Filter::$commented;
			$this->default_sorter = 'comment_date';
		}

		parent::process();
	}

	protected function process_query($sql) {
		$id = $this->get('id');

		if (empty($id) || !is_numeric($id)) {
			$this->add_error(Error_Api::INCORRECT_INPUT);
			return;
		}

		if (empty($this->local_filters)) {
			$data = $sql->get_table($this->table, 'id');
		} else {
			$data = $sql->get_table($this->table, 'id',
				implode(' and ', $this->local_filters), $this->local_filter_vars);
		}

		$pos = array_search(array('id' => $id), $data);

		if ($pos === false) {
			$this->set_success(false);
			return;
		}

		if (isset($data[$pos + 1])) {
			$this->add_answer('next', $data[$pos + 1]['id']);
		}
		if (isset($data[$pos - 1])) {
			$this->add_answer('prev', $data[$pos - 1]['id']);
		}

		$this->set_success(true);
	}

	protected function get_per_page($params) {
		return 0;
	}

	protected function get_page($params) {
		return 0;
	}
}
