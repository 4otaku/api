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
        if (preg_match('/^' . self::COMMAND_WORD . '\W\s*(.*)/i', $text, $command)) {
            $this->add_answer('text', $this->process_command($command[1]));
        }
    }

    protected function is_link($text)
    {
        return strpos($text, '<') !== false;
    }

    protected function process_command($command)
    {
        if (empty($command)) {
            return $this->do_info();
        }

        if (!preg_match('/(\w+)\W\s*(.*)/i', $command, $match)) {
            return '';
        }

        $type = $match[1];
        $params = preg_split('\s+', $match[2]);

        switch ($type) {
            case "инфо": return $this->do_info($params);
            case "найди": return $this->do_search($params);
            case "добавь": return $this->do_add($params);
            default: return '';
        }
    }

    protected function do_info($params = array())
    {
        if (empty($params)) {
            return "Вас приветствует бакабот. \n".
            "Для получения справки по команде напишите " . self::COMMAND_WORD . " инфо {имя команды}. \n" .
            "Доступные команды: найди, добавь";
        }

        $result = array();
        foreach ($params as $param) {
            switch ($param) {
                case "найди": $result[] = "To be described"; break;
                case "добавь": $result[] = "To be described"; break;
                default: $result[] = "Неизвестная команда $param"; break;
            }
        }

        return implode("\n", $result);
    }

    protected function do_search($params)
    {
        return serialize($params);
    }

    protected function do_add($params)
    {
        return serialize($params);
    }
}