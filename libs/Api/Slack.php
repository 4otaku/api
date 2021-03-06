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
        if (preg_match('/^(пачи|pachi)\s*(.*)/ui', $text, $command)) {
            $this->add_answer('text', (string) $this->process_command($command));
        }
    }

    protected function is_link($text)
    {
        return strpos($text, 'http') !== false;
    }

    protected function process_command($command)
    {
        $params = array_filter(preg_split('/\s+/', $command[2]));
        $type = array_shift($params);

        if (empty($type)) {
            return $command[1] == 'pachi' ?
                new SlackCommandInfo(array(), true) :
                new SlackCommandInfo();
        }

        switch ($type) {
            case "инфо": return new SlackCommandInfo($params);
            case "info": return new SlackCommandInfo($params, true);
            case "найди":
            case "find":
                return new SlackCommandSearch($params);
            case "добавь":
            case "add":
                return new SlackCommandAdd($params, $this->db, $this->get('user_id'));
            case "случайный":
            case "random":
                return new SlackCommandRandom($params);
            case "покажи":
            case "show":
                return new SlackCommandShow($params);
            case "теги":
            case "tag":
                return new SlackCommandTag($params, $this->db, $this->get('user_id'));
            case "одобри":
            case "approve":
                return new SlackCommandStatusApproved($params, $this->db, $this->get('user_id'));
            case "проверь":
            case "verify":
                return new SlackCommandStatusUndecided($params, $this->db, $this->get('user_id'));
            case "забракуй":
            case "decline":
                return new SlackCommandStatusDeclined($params, $this->db, $this->get('user_id'));
            case "молодец":
                return "https://images.4otaku.org/art/9a363e2acf728cf9f283d188ce77aac8_resize.jpg?" . substr(md5(microtime(true)), 0, 6);
            case "бака":
                return "https://images.4otaku.org/art/93659c411495b089e3aabb9aaa17856f_resize.jpg?" . substr(md5(microtime(true)), 0, 6);
            default:
                array_unshift($params, $type);
                return new SlackCommandRandom($params);
        }
    }
}