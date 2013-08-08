<?php

namespace Otaku\Api;

class ApiCreateArt extends ApiCreateAbstract
{
	public function process()
	{
		$key = $this->get('upload_key');

		if (empty($key)) {
			throw new ErrorApi('Пропущен ключ загрузки', ErrorApi::MISSING_INPUT);
		}

		$insert = $this->get_upload_data($key);
		$insert['id_user'] = $this->get_user();
		$insert['sortdate'] = $this->db->unix_to_date();

		$success = (bool) $this->db->insert('art', $insert);

		if (!$success) {
			throw new ErrorApi(ErrorApi::UNKNOWN_ERROR);
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
			$request = new ApiRequestInner(array(
				'id' => $id_artist,
				'add' => array(array('id' => $id))
			));
			$worker = new ApiUpdateArtArtist($request);
			$worker->process_request();
		}

		if ($this->get('tag')) {
			$request = new ApiRequestInner(array(
				'id' => $id,
				'add' => (array) $this->get('tag')
			));
			$worker = new ApiUpdateArtTag($request);
			$worker->process_request();
		}

		if ($this->get('source')) {
			$request = new ApiRequestInner(array(
				'id' => $id,
				'source' => (string) $this->get('source')
			));
			$worker = new ApiUpdateArtSource($request);
			$worker->process_request();
		}

		if ($this->get('group')) {
			foreach ((array) $this->get('group') as $group) {
				$request = new ApiRequestInner(array(
					'id' => $group['id'],
					'add' => array(array('id' => $id))
				));
				$worker = new ApiUpdateArtGroup($request);
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
				$request = new ApiRequestInner(array(
					'id' => $pack['id'],
					'add' => array($item)
				));
				$worker = new ApiUpdateArtPack($request);
				$worker->process_request();
			}
		}

		if ($this->get('manga')) {
			foreach ((array) $this->get('manga') as $manga) {
				$request = new ApiRequestInner(array(
					'id' => $manga['id'],
					'add' => array(array('id' => $id))
				));
				$worker = new ApiUpdateArtManga($request);
				$worker->process_request();
			}
		}

		if ($this->get('comment')) {
			$request = new ApiRequestInner(array(
				'id' => $id,
				'comment' => (string) $this->get('comment')
			));
			$worker = new ApiUpdateArtComment($request);
			$worker->process_request();
		}

		$this->add_answer('id', $id);
		$this->set_success(true);
	}
}