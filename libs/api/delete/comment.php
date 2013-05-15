<?php

class Api_Delete_Comment extends Api_Delete_Abstract
{
	public function process()
	{
		$id = $this->get('id');

		if (empty($id)) {
			throw new Error_Api(Error_Api::MISSING_INPUT);
		}

		if (!$this->is_moderator()) {
			throw new Error_Api(Error_Api::INSUFFICIENT_RIGHTS);
		}

		$parent = $this->db->get_field('comment', 'parent', $id);

		$this->db->begin();
		$this->db->update('comment', array('deleted' => 1), $id);
		$this->db->update('comment', array('parent' => $parent),
			'parent = ?', $id);

		if (!$parent) {
			$comments = $this->db->get_vector('comment',
				array('id', 'parent', 'rootparent'),
				'rootparent = ? and deleted = 0', $id);

			foreach ($comments as $comment_id => $one) {
				$temp = $one;
				$i = 0;
				$rootparent = 0;
				while($temp['parent'] && $i < 20) {
					$i++;
					$rootparent = $temp['parent'];
					$temp = $comments[$temp['parent']];
				}
				$this->db->update('comment', array('rootparent' => $rootparent),
					$comment_id);
			}
		}

		$data = $this->db->get_row('comment', array('id_item', 'area'), $id);

		$this->db->update('meta', array(
			'meta' => Database_Action::get(Database_Action::DECREMENT),
		), 'item_type = ? and id_item = ? and meta_type = ?', array(
			$data['area'], $data['id_item'], Meta::COMMENT_COUNT
		));

		$time = $this->db->order('sortdate')->get_field('comment', 'sortdate',
			'area = ? and id_item = ? and deleted = 0',
			array($data['area'], $data['id_item']));

		$this->db->update('meta', array(
			'meta' => strtotime($time),
		), 'item_type = ? and id_item = ? and meta_type = ?', array(
			$data['area'], $data['id_item'], Meta::COMMENT_DATE
		));

		$this->db->commit();
	}
}