<?php

namespace Otaku\Api;

class Cron_Pool extends Cron_Abstract
{
	protected function delete_empty() {
		$this->db->sql('delete from art_pack where id not in (SELECT id_pack FROM art_pack_item)');
		$this->db->sql('delete from art_manga where id not in (SELECT id_manga FROM art_manga_item)');
		$this->db->sql('delete from art_group where id not in (SELECT id_group FROM art_group_item)');

		return memory_get_usage();
	}

	protected function create_pack_archive() {
		$id = $this->db->get_field('art_pack', 'id', 'weight = 0');

		if (!empty($id)) {
			$address = FILES . SL . 'pack' . SL . $id . '.zip';
			$arts = $this->db->join('art', 'a.id = api.id_art')->get_table('art_pack_item',
				array('api.filename', 'a.md5', 'a.ext'), 'api.id_pack = ?', $id);

			$files= array();
			foreach ($arts as $art) {
				$files[$art['filename']] = IMAGES . SL . 'art' .
					SL . $art['md5'] . '.' . $art['ext'];
			}

			$weight = $this->create_archive($files, $address);
			if ($weight) {
				$this->db->update('art_pack', array('weight' => $weight), $id);
			}
		}

		return memory_get_usage();
	}

	protected function create_manga_archive() {
		$id = $this->db->get_field('art_manga', 'id', 'weight = 0');

		if (!empty($id)) {
			$address = FILES . SL . 'manga' . SL . $id . '.zip';
			$arts = $this->db->join('art', 'a.id = ami.id_art')->get_table('art_manga_item',
				array('ami.order', 'a.md5', 'a.ext'), 'ami.id_manga = ?', $id);

			$files= array();
			foreach ($arts as $art) {
				$order = $art['order'] + 1;
				$zero_len = max(0, 5 - strlen($order));
				$name = str_repeat('0', $zero_len) . $order . '.' . $art['ext'];
				$files[$name] = IMAGES . SL . 'art' . SL . $art['md5'] .
					'.' . $art['ext'];
			}

			$weight = $this->create_archive($files, $address);
			if ($weight) {
				$this->db->update('art_manga', array('weight' => $weight), $id);
			}
		}

		return memory_get_usage();
	}

	protected function create_archive($files, $address) {
		if (file_exists($address)) {
			unlink($address);
		}

		$zip = new ZipArchive();
		if (!$zip->open($address, ZipArchive::CREATE) === true) {
			return false;
		}

		foreach ($files as $name => $file) {
			if (file_exists($file)) {
				$zip->addFile($file, $name);
			}
		}
		$zip->close();

		return filesize($address);
	}
}
