<?php

namespace Otaku\Api;

abstract class SlackCommandAbstractList extends SlackCommandAbstractBase
{
    protected $per_page = 1;
    protected $sort = 'date';

    abstract protected function format_result($data);

    protected function process($params)
    {
        $request = new ApiRequestInner(array(
            'filter' => $this->get_filters($params),
            'per_page' => $this->per_page,
            'sort_by' => $this->sort
        ));
        $worker = new ApiReadArtList($request);
        $worker->process_request();
        $data = $worker->get_response();

        if (empty($data['data'])) {
            return "По запросу " . implode(" ", $params) . " ничего не найдено";
        }

        return $this->format_result($data);
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