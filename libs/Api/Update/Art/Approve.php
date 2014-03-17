<?php

namespace Otaku\Api;

class ApiUpdateArtApprove extends ApiUpdateAbstract
{
	public function process()
	{
		$id = $this->get('id');
		$state = (string) $this->get('state');

		if (!$this->is_moderator()) {
			throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
		}

		if (empty($id) || empty($state)) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$state = Meta::parse($state);

		if (empty($state)) {
			throw new ErrorApi(ErrorApi::INCORRECT_INPUT);
		}

		$this->db->update('art', array('sortdate' =>
			$this->db->unix_to_date()), $id);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_APPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_UNAPPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_DISAPPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_DELETED);
		$this->add_meta(Meta::ART, $id, Meta::STATE, $state);

		$cache = new \Memcached("access_checker");
		$cache->addServer("localhost", 11211);
		$cache->delete('is_pic_safe_' . $this->db->get_field('art', 'md5', $id));

		$this->set_success(true);
	}
}