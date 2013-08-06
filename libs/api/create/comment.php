<?php

class Api_Create_Comment extends Api_Create_Abstract
{
	public function process()
	{
		$id_item = (int) $this->get('id_item');
		$area = Meta::parse($this->get('area'));
		$parent = (int) $this->get('parent');
		$name = (string) $this->get('name');
		$mail = (string) $this->get('mail');
		$text = (string) $this->get('text');
		$text = trim($text);

		if (empty($text) || empty($id_item) || empty($area)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!empty($parent) && !$this->db->get_count('comment', $parent)) {
			throw new Error_Api(Error_Api::COMMENT_PARENT_DO_NOT_EXIST);
		}

		if ($parent) {
			$root = $this->db->get_field('comment', 'rootparent', $parent);
			if (!$root) {
				$root = $parent;
			}
		} else {
			$root = 0;
		}

		$name = $name ? $name : $this->db->get_field('user', 'login', 1);
		$mail = $mail ? $mail : $this->db->get_field('user', 'email', 1);
		$time = time();

		$this->db->insert('comment', array(
			'parent' => $parent,
			'rootparent' => $root,
			'id_item' => $id_item,
			'area' => $area,
			'username' => $name,
			'email' => $mail,
			'ip' => ip2long($this->get_ip()),
			'cookie' => $this->get_cookie(),
			'text' => $text,
			'sortdate' => $this->db->unix_to_date($time),
		));

		$this->db->update('meta', array(
			'meta' => Database_Action::get(Database_Action::INCREMENT),
		), 'item_type = ? and id_item = ? and meta_type = ?', array(
			Meta::ART, $id_item, Meta::COMMENT_COUNT
		));
		$this->add_single_meta(Meta::ART, $id_item, Meta::COMMENT_DATE, $time);

		$this->set_success(true);
	}
}
