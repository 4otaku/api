<?php

namespace Otaku\Api;

class ApiSlack extends ApiAbstract
{
    public function process()
    {
        $user = $this->get('user_name');
        $user_id = $this->get('user_id');
        $text = $this->get('text');

        $this->db->insert('slack_log', array(
            'user_id' => $user_id,
            'user_name' => $user,
            'text' => $text,
            'is_link' => (int) $this->is_link($text)
        ));

        $this->set_success(true);

        if ($user == 'slackbot') {
            return;
        }

        $this->add_answer('text', $user);
    }

    protected function is_link($text)
    {
        return false;
    }
}