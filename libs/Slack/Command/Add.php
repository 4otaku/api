<?php

namespace Otaku\Api;

use Otaku\Framework\DatabaseInstance;

class SlackCommandAdd extends SlackCommandAbstract
{
    /**
     * @var DatabaseInstance
     */
    protected $db;

    public function __construct($params, $db)
    {
        $this->db = $db;
        parent::__construct($params);
    }

    protected function process($params)
    {
        $url = empty($params) ?
            $this->fetchUrlFromDB() :
            $this->fetchUrlFromParams($params);

        if (empty($url)) {
            return "Не удалось найти валидный url для загрузки";
        }


        $request = new ApiRequestInner(array(
            'file' => $url
        ));
        $worker = new ApiUploadArt($request);
        $worker->process_request();
        $data = $worker->get_response();
        $file = reset($data['files']);

        if (!empty($file['error_code'])) {
            if ($file['error_code'] == 30) {
                return "Арт уже есть под номером $file[error_text]";
            } else {
                return "Не удалось скачать файл $url";
            }
        }

        $request = new ApiRequestInner($file);
        $worker = new ApiCreateArt($request);
        $worker->process_request();
        $data = $worker->get_response();
        $error = reset($data['errors']);

        if (!empty($error)) {
            if ($error['code'] == 30) {
                return "Арт уже есть под номером $error[message]";
            } else {
                return "Произошла неизвестная ошибка, приносим свои извинения";
            }
        }

        return "Успешно добавлено как <http://art.4otaku.org/$data[id]/|$data[id]>";
    }

    protected function fetchUrlFromDB()
    {
        $text = $this->db->order('timestamp')->get_field('slack_log',
            'text', 'is_link = 1 and timestamp > ?',
            $this->db->unix_to_date(time() - 3600));
        return $this->fetchUrlFromParams(array($text));
    }

    protected function fetchUrlFromParams($params)
    {
        foreach ($params as $param) {
            if (preg_match('/<(https?:\/\/[^>]*)/', $param, $match)) {
                return $match[1];
            }
        }

        return false;
    }
}