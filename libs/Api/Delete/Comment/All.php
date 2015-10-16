<?php

namespace Otaku\Api;

use Otaku\Framework\DatabaseAction;

class ApiDeleteCommentAll extends ApiDeleteAbstract
{
        public function process()
        {
                $id = $this->get('id');

                if (empty($id)) {
                        throw new ErrorApi(ErrorApi::MISSING_INPUT);
                }

                if (!$this->is_moderator()) {
                        throw new ErrorApi(ErrorApi::INSUFFICIENT_RIGHTS);
                }

                $data = $this->db->get_row('comment', array('ip', 'sortdate'), $id);
		$ip = $data['ip'];

		if ($ip == 2130706433) {
			throw new ErrorApi(ErrorApi::MISSING_INPUT);
		}

		$time = $this->db->date_to_unix($data['sortdate']);
		$start = $this->db->unix_to_date($time - DAY);
		$end = $this->db->unix_to_date($time + DAY);
		$ids = $this->db->get_vector('comment', 'id', 'deleted = 0 and ip = ? and sortdate > ? and sortdate < ?', array($ip, $start, $end));
		$ids = array_keys($ids);

                $this->db->insert('ip_ban', array(
                        'ip' => $ip,
			'date' => $this->db->unix_to_date(),
                ));

		$count = 0;
		foreach ($ids as $id) {
                        $request = new ApiRequestInner(array(
				'id' => $id,
				'cookie' => $this->get('cookie')
			));
                        $worker = new ApiDeleteComment($request);

                        $data = $worker->process_request()->get_response();

                        if (!empty($data['success'])) {
                                $count++;
                        }
		}

                $this->add_answer('total', count($ids));
                $this->add_answer('deleted', $count);
		$this->set_success(true);
	}
}
