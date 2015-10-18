<?php

namespace Otaku\Api;

class ApiSlack extends ApiAbstract
{
    const BOT_ID = 'USLACKBOT';
    const COMMAND_WORD = 'чотач';

    public function process()
    {
        $user = $this->get('user_name');
        $user_id = $this->get('user_id');
        $text = $this->get('text');

        if (empty($user) || empty($user_id) || empty($text)) {
            throw new ErrorApi(ErrorApi::MISSING_INPUT);
        }

        $this->db->insert('slack_log', array(
            'user_id' => $user_id,
            'user_name' => $user,
            'text' => $text,
            'is_link' => (int) $this->is_link($text)
        ));

        $this->set_success(true);

        // Ignore slackbot himself
        if ($user_id == self::BOT_ID) {
            return;
        }

        // Check for commands
        if (preg_match('/^' . self::COMMAND_WORD . '\b\s*(.*)/i', $text, $command)) {
            $this->add_answer('text', $this->process_command($command));
        }
    }

    protected function is_link($text)
    {
        return strpos($text, '<') !== false;
    }

    protected function process_command($command)
    {
        return $command;
    }
}