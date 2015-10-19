<?php

namespace Otaku\Api;

class ApiSlack extends ApiAbstract
{
    const BOT_ID = 'USLACKBOT';

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
        if (preg_match('/^чотач\s*(.*)/i', $text, $command)) {
            $this->add_answer('text', (string) $this->process_command($command[1]));
        }
    }

    protected function is_link($text)
    {
        return strpos($text, '<') !== false;
    }

    protected function process_command($command)
    {
        $params = array_filter(preg_split('/\s+/', $command));
        $type = array_shift($params);

        if (empty($type)) {
            return new SlackCommandInfo();
        }

        switch ($type) {
            case "инфо": return new SlackCommandInfo($params);
            case "найди": return new SlackCommandSearch($params);
            case "добавь": return new SlackCommandAdd($params);
            case "случайный": return new SlackCommandRandom($params);
            case "покажи": return new SlackCommandShow($params);
            default:
                array_unshift($params, $type);
                return new SlackCommandRandom($params);
        }
    }
}