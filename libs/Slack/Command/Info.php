<?php

namespace Otaku\Api;

class SlackCommandInfo extends SlackCommandAbstractBase
{
    /**
     * @var bool
     */
    protected $english;

    public function __construct($params = array(), $english = false)
    {
        $this->english = $english;
        parent::__construct($params);
    }

    protected function process($params)
    {
        if (empty($params)) {
            return "Вас приветствует библиотекарь. Моя основная задача это связь между этой конференцией и http://art.4otaku.org \n".
            "Для получения справки по команде напишите пачи инфо {имя команды}. \n" .
            "Доступные команды: найди, добавь, случайный, покажи, теги. \n" .
            "Также можно просто перечислить теги, в этом случае будет тот же эффект как и от команды случайный. \n" .
                "Например 'пачи yuri' даст тот же эффект, что и 'пачи случайный yuri'";
        }

        $result = array();
        foreach ($params as $param) {
            switch ($param) {
                case "найди":
                case "find":
                    $result[] = "Ищет арты по определенной выборке и показывает три последних. \n"
                        . "В качестве аргумента принимает теги, а также фразы 'в барахолке', 'на премодерации', 'везде', 'недотеганное'. \n"
                        . "Перед аргументами можно использовать модификаторы. "
                        . "В качестве модификатора число от 1 до 10 задаст сколько артов вернуть (по умолчанию 3). "
                        . "Слово 'случайное' или слово 'лучшее' в качестве модификатора зададут сортировку. \n"
                        . "Примеры использования: \n"
                        . "пачи найди \n"
                        . "пачи найди юри \n"
                        . "пачи найди недотеганное в барахолке \n"
                        . "пачи найди yuri red_hair везде \n"
                        . "пачи найди 10 hakurei_reimu \n"
                        . "пачи найди лучшее юри \n"
                        . "пачи найди 1 случайное юри";
                    break;
                case "добавь":
                case "add":
                    $result[] = "Загружает арт. Если явно указана ссылка, то возьмет арт по этой ссылке. \n"
                        . "Если ссылки не указано, возьмет последнюю ссылку из этого канала за последний час. \n"
                        . "Если вы хотите, чтобы арты добавлялись от лица вашего аккаунта на art.4otaku.org, вы можете привязать свой аккаунт. "
                        . "Для этого зайдите в личные сообщения к <@slackbot> и напишите там: /login username password \n"
                        . "Примеры использования: \n"
                        . "пачи добавь http://i.4cdn.org/c/1445783108222.jpg \n"
                        . "пачи добавь";
                    break;
                case "случайный":
                case "random":
                    $result[] = "Показывает случайный арт согласно критериям выборки. \n"
                        . "В качестве аргумента принимает теги, а также фразы 'в барахолке', 'на премодерации', 'везде', 'недотеганное'. \n"
                        . "Слово 'случайный' можно опустить, если есть хотя бы один аргумент. \n"
                        . "Примеры использования: \n"
                        . "пачи случайный \n"
                        . "пачи юри \n"
                        . "пачи недотеганное в барахолке \n"
                        . "пачи yuri red_hair везде";
                    break;
                case "покажи":
                case "show":
                    $result[] = "Показывает подробную информацию по одному арту. В качестве аргумента принимает номер арта. \n"
                        . "Можно запросить несколько разных артов одним запросом. \n"
                        . "Примеры использования: \n"
                        . "пачи покажи 124799 \n"
                        . "пачи покажи 120893 120894 120890";
                    break;
                case "теги":
                case "tag":
                    $result[] = "Добавляет теги к арту. В качестве первого аргумента принимает номер арта, дальше идет либо список тегов либо указание попробовать найти их на Данбору. \n"
                        . "Отсутствие тегов считается указанием попробовать Данбору \n"
                        . "Примеры использования: \n"
                        . "пачи теги к 124799 red_hair yuri swimsuit \n"
                        . "пачи теги к 124799 \n"
                        . "пачи теги к 120893 данбору";
                    break;
                default:
                    $result[] = "Неизвестная команда $param";
                    break;
            }
        }

        $result = implode("\n", $result);

        if ($this->english) {
            $result = str_replace(
                array(
                    'пачи', 'инфо', 'найди', 'добавь', 'случайный',
                    'покажи', 'случайное', 'лучшее', 'юри', 'теги к',
                    'теги', 'данбору'
                ),
                array(
                    'pachi', 'info', 'find', 'add', 'random',
                    'show', 'random', 'best', 'yuri', 'tag',
                    'tag', 'danbooru'
                ),
                $result);
        }

        return $result;
    }
}