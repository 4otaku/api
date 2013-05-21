<?php

class Api_Update_Art_Translation extends Api_Update_Abstract
{
	protected $next_translation_id = false;

	public function process()
	{
		$id = $this->get('id');
		$add = (array) $this->get('add');
		$change = (array) $this->get('change');
		$remove = (array) $this->get('remove');

		if (empty($id) || (empty($add) && empty($change) && empty($remove))) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		foreach ($add as $item) {
			try {
				$this->do_insert($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($change as $item) {
			try {
				$this->do_update($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($remove as $item) {
			try {
				$this->do_delete($id, $item);
			} catch (Error_Api $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		$this->set_success(true);
	}

	protected function do_insert($id, $data)
	{
		if (!isset($data['x1']) || !isset($data['x2']) || !isset($data['y1']) ||
			!isset($data['y2']) || !isset($data['text'])) {

			throw new Error_Api('Недостаточно данных для создания перевода',
				Error_Api::INCORRECT_INPUT);
		}

		if (!$this->next_translation_id) {
			$max = $this->db->order('id_translation')->get_field(
				'art_translation', 'id_translation', 'id_art = ?', $id);
			$this->next_translation_id = $max + 1;
		}

		$this->db->insert('art_translation', array(
			'id_translation' => $this->next_translation_id,
			'id_art' => $id,
			'id_user' => $this->get_user(),
			'x1' => $data['x1'],
			'x2' => $data['x2'],
			'y1' => $data['y1'],
			'y2' => $data['y2'],
			'text' => $data['text'],
		));

		$this->next_translation_id++;
	}

	protected function do_update($id, $data)
	{
		if (empty($data['id'])) {
			throw new Error_Api('Для редактирования перевода нужно указать его id',
				Error_Api::INCORRECT_INPUT);
		}

		$this->set_old($id, $data['id']);

		$last = $this->db->order('sortdate')->get_full_row('art_translation',
			'id_art = ? and id_translation = ?', array($id, $data['id']));

		if (empty($last)) {
			throw new Error_Api('Перевода с id ' . $data['id'] .
				' не существует', Error_Api::INCORRECT_INPUT);
		}

		$this->db->insert('art_translation', array(
			'id_translation' => $data['id'],
			'id_art' => $id,
			'id_user' => $this->get_user(),
			'x1' => isset($data['x1']) ? $data['x1'] : $last['x1'],
			'x2' => isset($data['x2']) ? $data['x2'] : $last['x2'],
			'y1' => isset($data['y1']) ? $data['y1'] : $last['y1'],
			'y2' => isset($data['y2']) ? $data['y2'] : $last['y2'],
			'text' => isset($data['text']) ? $data['text'] : $last['text'],
		));
	}

	protected function do_delete($id, $data)
	{
		$data = (int) $data;

		if (empty($data)) {
			throw new Error_Api('Для удаления перевода необходимо указать его id',
				Error_Api::INCORRECT_INPUT);
		}

		$this->set_old($id, $data);

		$this->db->insert('art_translation', array(
			'id_translation' => $data,
			'id_art' => $id,
			'id_user' => $this->get_user(),
			'state' => 3
		));
	}

	protected function set_old($art, $translation)
	{
		$this->db->update('art_translation', array('state' => 2),
			'id_art = ? and id_translation = ?', array($art, $translation));
	}
}