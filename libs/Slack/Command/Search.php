<?php

namespace Otaku\Api;

class SlackCommandSearch extends SlackCommandAbstract
{
    protected function process($params)
    {
        $request = new ApiRequestInner(array(
            'filter' => $this->get_filters($params),
            'per_page' => 3,
            'sort_by' => 'random'
        ));
        $worker = new ApiReadArtList($request);
        $worker->process_request();
        $data = $worker->get_response();
        $result = "Всего по этому запросу есть $data[count] артов";
        foreach ($data['data'] as $art) {
            $result .= "\nАрт номер $art[id]" .
                "http://images.4otaku.org/art/$art[md5]_thumb.jpg";
        }
        return $result;
    }

    protected function get_filters($params)
    {
        // Фильтр от синтаксического сахара "на премодерации", "в мастерской"
        $params = array_filter($params, function($element){
           return $element != "в" && $element != "на";
        });

        $approval = 'approved';
        $tag_state = 'tagged';
        $filters = array();

        foreach ($params as $element) {
            if ($element == 'везде') {
                $approval = false;
                continue;
            }

            if ($element == 'барахолке') {
                $approval = 'disapproved';
                continue;
            }

            if ($element == 'премодерации') {
                $approval = 'unapproved';
                continue;
            }

            if ($element == 'недотеганное') {
                $tag_state = 'untagged';
                continue;
            }

            $filters[] = array(
                'name' => 'art_tag',
                'type' => 'is',
                'value' => $element
            );
        }

        $filters[] = array(
            'name' => 'state',
            'type' => 'is',
            'value' => $tag_state
        );
        if ($approval) {
            $filters[] = array(
                'name' => 'state',
                'type' => 'is',
                'value' => $approval
            );
        }

        return $filters;
    }
}