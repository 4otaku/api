<?php

namespace Otaku\Api;

class ApiUpdateArtTranslation extends ApiUpdateAbstract
{
	protected $next_translation_id = false;

	public function process()
	{
		$id = $this->get('id');
		$add = (array) $this->get('add');
		$change = (array) $this->get('change');
		$remove = (array) $this->get('remove');

		if (empty($id) || (empty($add) && empty($change) && empty($remove))) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		foreach ($add as $item) {
			try {
				$this->do_insert($id, $item);
			} catch (ErrorApi $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($change as $item) {
			try {
				$this->do_update($id, $item);
			} catch (ErrorApi $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		foreach ($remove as $item) {
			try {
				$this->do_delete($id, $item);
			} catch (ErrorApi $e) {
				$this->add_error($e->getCode(), $e->getMessage());
			}
		}

		$this->add_single_meta(Meta::ART, $id, Meta::TRANSLATION_DATE, time());

		if (!$this->db->get_count('meta',
			'item_type = ? and meta_type = ? and id_item = ? and meta = ?',
			array(Meta::ART, Meta::TRANSLATOR, $id, $this->get_user()))) {

			$this->db->insert('meta', array(
				'item_type' => Meta::ART,
				'meta_type' => Meta::TRANSLATOR,
				'id_item' => $id,
				'meta' => $this->get_user()
			));
		}

		$this->set_success(true);
	}

	protected function do_insert($id, $data)
	{
		if (!isset($data['x1']) || !isset($data['x2']) || !isset($data['y1']) ||
			!isset($data['y2']) || !isset($data['text'])) {

			throw new ErrorApi('Недостаточно данных для создания перевода',
				ErrorApi::INCORRECT_INPUT);
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
			throw new ErrorApi('Для редактирования перевода нужно указать его id',
				ErrorApi::INCORRECT_INPUT);
		}

		$this->set_old($id, $data['id']);

		$last = $this->db->order('sortdate')->get_full_row('art_translation',
			'id_art = ? and id_translation = ?', array($id, $data['id']));

		if (empty($last)) {
			throw new ErrorApi('Перевода с id ' . $data['id'] .
				' не существует', ErrorApi::INCORRECT_INPUT);
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
			throw new ErrorApi('Для удаления перевода необходимо указать его id',
				ErrorApi::INCORRECT_INPUT);
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