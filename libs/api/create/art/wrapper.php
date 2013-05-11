<?php

class Api_Create_Art extends Api_Create_Abstract
{
	public function process()
	{
		$key = $this->get('upload_key');

		if (empty($key)) {
			throw new Error_Api('upload_key', Error_Api::MISSING_INPUT);
		}

		$insert = $this->get_upload_data($key);
		$insert['id_user'] = $this->get_user();
		$insert['sortdate'] = $this->db->unix_to_date();

		$success = (bool) $this->db->insert('art', $insert);

		if (!$success) {
			throw new Error_Api(Error_Api::UNKNOWN_ERROR);
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
			foreach ((array) $this->get('group') as $group) {
				$request = new Api_Request_Inner(array(
					'id' => $group['id'],
					'add' => array(array('id' => $id))
				));
				$worker = new Api_Update_Art_Group($request);
				$worker->process_request();
			}
		}

		if ($this->get('pack')) {
			$add = (array) $this->get('pack');
			foreach ($add as $pack) {
				$item = array(
					'id' => $id,
					'filename' => empty($pack['filename']) ?
						$this->get_upload_name($key) : $pack['filename']
				);
				$request = new Api_Request_Inner(array(
					'id' => $pack['id'],
					'add' => array($item)
				));
				$worker = new Api_Update_Art_Pack($request);
				$worker->process_request();
			}
		}

		if ($this->get('manga')) {
			foreach ((array) $this->get('manga') as $manga) {
				$request = new Api_Request_Inner(array(
					'id' => $manga['id'],
					'add' => array(array('id' => $id))
				));
				$worker = new Api_Update_Art_Manga($request);
				$worker->process_request();
			}
		}

		if ($this->get('comment')) {
			$request = new Api_Request_Inner(array(
				'id' => $id,
				'comment' => (string) $this->get('comment')
			));
			$worker = new Api_Update_Art_Comment($request);
			$worker->process_request();
		}

		$this->add_answer('id', $id);
		$this->set_success(true);
	}
}