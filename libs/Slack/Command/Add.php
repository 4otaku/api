<?php

namespace Otaku\Api;

use Otaku\Framework\DatabaseInstance;

class SlackCommandAdd extends SlackCommandAbstract
{
    /**
     * @var DatabaseInstance
     */
    protected $db;

    public function __construct($params = array(), $db)
    {
        parent::__construct($params);
        $this->db = $db;
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

        $id = $data['data'][0]['id'];
        return "Успешно добавлено под номером $id";
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
            if (preg_match('/<(https?://.*)(?:>|\|)/', $param, $match)) {
                return $match[1];
            }
        }

        return false;
    }
}

/*
 *
  val UPLOAD_ROOT = API_ROOT + "upload/art?"
  val ADD_ROOT = API_ROOT + "create/art?"

        $this->db->insert('slack_log', array(
            'user_id' => $user_id,
            'user_name' => $user,
            'text' => $text,
            'is_link' => (int) $this->is_link($text)
        ));

  case class NotFoundException(url: String) extends Exception
  case class AlreadyExistException(id: String) extends Exception

  override def process(cm: ChatMessage, params: Regex.Match): Future[Either[Unit, String]] = {
    request(UPLOAD_ROOT, Map("file" -> params.group("url"))).map((res) => {
      val response = new ResponseUpload(res)
      response.file.get("error") match {
        case Some(error) =>
          if (response.file("error_code") == 30)
            throw new AlreadyExistException(response.file("error_text").asInstanceOf[String])
          else
            throw new NotFoundException(params.group("url"))
        case _ => response.file("upload_key")
      }
    }).flatMap((key) => {
      request(ADD_ROOT, Map("upload_key" -> key.asInstanceOf[String]))
    }).map((res) => {
      val response = new ResponseBasic(res)
      response.error match {
        case Some(error) =>
          if (error("code") == 30)
            throw new AlreadyExistException(error("message").asInstanceOf[String])
          else
            throw new Exception
        case _ => Right("Успешно добавлено под номером " + response.parsed("id"))
      }
    }).recover {
      case NotFoundException(url) => Right("Не удалось скачать файл " + url)
      case AlreadyExistException(id) => Right("Арт уже есть под номером " + id)
      case _ => Right("Произошла неизвестная ошибка, приносим свои извинения")
    }
 */