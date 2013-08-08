<?php

namespace Otaku\Api;

class ApiUpdateArtArtist extends ApiUpdateAbstract
{
	public function process()
	{
		$id = $this->get('id');

		if (!$this->is_moderator()) {
			$author = $this->db->get_field('art_artist', 'id_user', $id);
			if ($this->get_user() != $author) {
				throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
			}
		}

		if (empty($id) || !$this->have_changes()) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		if (!(bool) $this->db->get_count('art_artist', $id)) {
			throw new ErrorApi(ErrorApi::INCORRECT_INPUT);
		}

		foreach ((array) $this->get('add') as $item) {
			$this->add_single_meta(Meta::ART, (int) $item['id'],
				Meta::ART_ARTIST, $id);
		}

		foreach ((array) $this->get('remove') as $item) {
			$this->remove_meta(Meta::ART, (int) $item, Meta::ART_ARTIST, $id);
		}

		if ($this->get('text') !== null) {
			$this->db->update('art_artist', array('text' => $this->get('text')),
				$id);
		}

		$this->set_success(true);
	}

	protected function have_changes()
	{
		return $this->get('add') || $this->get('remove') ||
			$this->get('text') !== null;
	}
}