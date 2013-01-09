<?php

class Api_Create_Art extends Api_Create_Abstract
{
	public function process()
	{
		$key = $this->get('upload_key');

		if (empty($key)) {
			throw new Error_Api('upload_key', Error_Api::MISSING_INPUT);
		}

		$md5 = substr($key, 0, 32);
		$id = substr($key, 32);

		$data = $this->db->get_full_row('art_upload', $id);

		if (empty($data) ||$data['md5'] != $md5) {
			throw new Error_Api('upload_key', Error_Api::INCORRECT_INPUT);
		}

		$insert = $data;
		unset($insert['id'], $insert['date'], $insert['name']);
		$insert['id_user'] = $this->get_user();
		$insert['sortdate'] = $this->db->unix_to_date();
		if (
			function_exists('puzzle_fill_cvec_from_file') &&
			function_exists('puzzle_compress_cvec')
		) {
			$imagelink = $this->get_images_path()
				. 'art' . SL . $md5 . '_largethumb.jpg';

			$vector = puzzle_fill_cvec_from_file($imagelink);
			$vector = base64_encode(puzzle_compress_cvec($vector));
			$insert['vector'] = $vector;
		}

		$success = (bool) $this->db->insert('art', $insert);

		if (!$success) {
			$id = $this->db->get_field('art', 'id', 'md5 = ?', $md5);
			if ($id) {
				throw new Error_Api($id, Error_Upload::ALREADY_EXISTS);
			} else {
				throw new Error_Api(Error_Api::UNKNOWN_ERROR);
			}
		}

		$id = $this->db->last_id();
		$this->db->update('art', array('id_parent' => $id), $id);

		$this->add_meta(Meta::ART, $id, Meta::ART_RATING, 0);
		$this->add_meta(Meta::ART, $id, Meta::COMMENT_COUNT, 0);
		$this->add_meta(Meta::ART, $id, Meta::TAG_COUNT, 0);

		if ($this->get('approved') && $this->is_moderator()) {
			$this->add_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_APPROVED);
		} else {
			$this->add_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_UNAPPROVED);
		}
		$this->add_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_UNTAGGED);

		if ($this->get('artist')) {
			$id_artist = $this->db->get_field('art_artist',
				'id', 'id_user = ?', $this->get_user());
			if (!$id_artist) {
				$this->db->insert('art_artist', array(
					'id_user' => $this->get_user(),
					'text' => ''
				));
				$id_artist = $this->db->last_id();
			}
			$this->add_meta(Meta::ART, $id, Meta::ART_ARTIST, $id_artist);
		}

		if ($this->get('tag')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'add' => (array) $this->get('tag')
			));
			$worker = new Api_Update_Art_Tag($request);
			$worker->process_request();
		}

		if ($this->get('source')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'source' => (string) $this->get('source')
			));
			$worker = new Api_Update_Art_Source($request);
			$worker->process_request();
		}

		if ($this->get('group')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'add' => (array) $this->get('group')
			));
			$worker = new Api_Update_Art_Group($request);
			$worker->process_request();
		}

		if ($this->get('pack')) {
			$add = (array) $this->get('pack');
			foreach ($add as &$item) {
				if (empty($item['filename'])) {
					$item['filename'] = $data['name'];
				}
			}

			$request = new Api_Request_Inner(array(
				'id' => $id,
				'add' => $add
			));
			$worker = new Api_Update_Art_Pack($request);
			$worker->process_request();
		}

		if ($this->get('manga')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'add' => (array) $this->get('manga')
			));
			$worker = new Api_Update_Art_Manga($request);
			$worker->process_request();
		}

		$this->add_answer('id', $id);
		$this->set_success(true);
	}
}