<?php

namespace Otaku\Api;

use Otaku\Framework\DatabaseInstance;

abstract class SlackCommandAbstractNamed extends SlackCommandAbstractBase
{
    /**
     * @var DatabaseInstance
     */
    protected $db;

    protected $user;

    public function __construct($params, $db, $user)
    {
        $this->db = $db;
        $this->user = $user;
        parent::__construct($params);
    }

    protected function addCookie($request)
    {
        $cookie = $this->db->join('slack_user', 'u.id = su.user_id')->get_field('user',
            'cookie', 'su.slack_id = ?', $this->user);

        if ($cookie) {
            $request['cookie'] = $cookie;
        }

        return $request;
    }
}