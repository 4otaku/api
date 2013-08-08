<?php

namespace otaku\api;

class Cron_Art extends Cron_Abstract
{
	protected function resize()
	{
		$request = new Api_Request_Inner(array(
			'filter' => array(array(
				'name' => 'art_tag',
				'type' => 'is',
				'value' => 'need_resize'
			)),
			'per_page' => 100
		));
		$worker = new Api_Read_Art_List($request);
		$response = $worker->process_request()->get_response();
		$data = $response['data'];
		foreach ($data as $art) {
			$name = $art['md5'].'.'.$art['ext'];
			$path = IMAGES . SL . 'art' . SL . $name;
			$worker = new Transform_Upload_Art($path, $name, IMAGES);
			$resized = $worker->resize();
			$this->db->update('art', array('resized' => (int) $resized),
				$art['id']);

			$request = new Api_Request_Inner(array(
				'id' => $art['id'],
				'remove' => array('need_resize')
			));
			$worker = new Api_Update_Art_Tag($request);
			$worker->process_request();
		}
	}

	protected function track_similar() {
		if (
			!function_exists('puzzle_fill_cvec_from_file') ||
			!function_exists('puzzle_vector_normalized_distance') ||
			!function_exists('puzzle_compress_cvec') ||
			!function_exists('puzzle_uncompress_cvec')
		) {
			return;
		}

		$unparsed = $this->db->limit(100)->get_vector('art',
			array('id', 'md5', 'ext'), 'vector = ""');
		foreach ($unparsed as $id => $image) {
			$file = IMAGES .SL . 'art' . SL . $image['md5'] . '.' . $image['ext'];
			$vector = puzzle_fill_cvec_from_file($file);
			$vector = base64_encode(puzzle_compress_cvec($vector));
			$this->db->update('art',
				array('similar_tested' => 0, 'vector' => $vector), $id);
		}

		$all = $this->db->get_vector('art', array('id', 'vector'),
			'vector != ""');
		$arts = $this->db->limit(100)->get_vector('art', array('id', 'vector'),
			'vector != "" and similar_tested = 0');

		foreach ($all as $id => $vector) {
			$all[$id] = puzzle_uncompress_cvec(base64_decode($vector));
		}

		foreach ($arts as $id => $vector) {
			$vector = puzzle_uncompress_cvec(base64_decode($vector));
			foreach ($all as $compare_id => $compare_vector) {
				if (
					$id != $compare_id &&
					puzzle_vector_normalized_distance($vector, $compare_vector) < 0.3
				) {
					$id_first = min($id, $compare_id);
					$id_second = max($id, $compare_id);
					$this->db->insert('art_similar', array(
						'id_first' => $id_first,
						'id_second' => $id_second
					));
				}
			}
			$this->db->update('art', array('similar_tested' => 1), $id);
		}
	}
}