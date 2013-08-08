<?php

namespace Otaku\Api;

class Api_Update_Art_Approve extends Api_Update_Abstract
{
	public function process()
	{
		$id = $this->get('id');
		$state = (string) $this->get('state');

		if (!$this->is_moderator()) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		if (empty($id) || empty($state)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		$state = Meta::parse($state);

		if (empty($state)) {
			throw new Error_Api(Error_Api::INCORRECT_INPUT);
		}

		$this->db->update('art', array('sortdate' =>
			$this->db->unix_to_date()), $id);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_APPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_UNAPPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_DISAPPROVED);
		$this->remove_meta(Meta::ART, $id, Meta::STATE, Meta::STATE_DELETED);
		$this->add_meta(Meta::ART, $id, Meta::STATE, $state);

		$this->set_success(true);
	}
}