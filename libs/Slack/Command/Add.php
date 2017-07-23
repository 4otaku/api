<?php

namespace Otaku\Api;

use Otaku\Framework\Error;

class SlackCommandAdd extends SlackCommandAbstractNamed
{
    use SlackTraitFetcher;

    protected function process($params)
    {
        $fetchMeta = false;
        if (reset($params) == 'протегай') {
            array_shift($params);
            $fetchMeta = true;
        }

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
                return "Арт уже есть под номером <https://art.4otaku.org/$file[error_text]/|$file[error_text]>";
            } else {
                return "Не удалось скачать файл $url";
            }
        }

        $file = $this->addCookie($file);

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

        $key = substr($file["upload_key"], 0, 32);

        if ($fetchMeta) {
            try {
                $this->fetchMeta($data['id'], $key);
                $error = "";
            } catch (Error $e) {
                $error = "\n" . $e->getMessage();
            }
        }

        return "Успешно добавлено как <https://art.4otaku.org/$data[id]/|$data[id]>\n"
            . "https://images.4otaku.org/art/" . $key . "_largethumb.jpg"
            . $error;
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