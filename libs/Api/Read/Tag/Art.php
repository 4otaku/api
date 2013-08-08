<?php

namespace Otaku\Api;

class Api_Read_Tag_Art extends Api_Read_Tag
{
	protected $fields = array('at.id', 'at.name', 'at.color');
	protected $table = 'art_tag';

	/**
	 * @param Database_Instance $sql
	 * @return mixed
	 */
	protected function fetch_data($sql)
	{
		$condition = '';
		$params = array();

		$id = (int) $this->get('id');
		$name = (string) $this->get('name');
		$filter = (string) $this->get('filter');

		if ($id) {
			$condition = 'at.id = ?';
			$params[] = $id;
		} elseif ($name) {
			$condition = 'at.name = ? or atv.name = ?';
			$name = trim($name);
			$params[] = $name;
			$params[] = $name;
		} elseif ($filter) {
			$condition = 'at.name like ? or atv.name like ?';
			$filter = '%' . str_replace('\\', '\\\\', trim($filter)) . '%';
			$params[] = $filter;
			$params[] = $filter;
		}

		$sql->join('art_tag_variant', 'atv.id_tag = at.id');
		$sql->group('at.id');

		return $sql->get_table($this->table, $this->fields,
			$condition, $params);
	}

	protected function add_additional_data(&$data)
	{
		$ids = array();
		$links = array();
		foreach ($data as &$item) {
			$item['variant'] = array();
			$ids[] = $item['id'];
			$links[$item['id']] = &$item['variant'];
		}

		$variants = $this->db->get_table('art_tag_variant',
			array('id_tag', 'name'), $this->db->array_in('id_tag', $ids), $ids);
		$count = $this->db->get_table('art_tag_count',
			array('id_tag', 'count'), 'original = ? and ' .
				$this->db->array_in('id_tag', $ids), array_merge([1], $ids));
		$count_artist = $this->db->get_table('art_artist_tag_count',
			array('id_tag', 'count'), $this->db->array_in('id_tag', $ids), $ids);
		$count_group = $this->db->get_table('art_group_tag_count',
			array('id_tag', 'count'), $this->db->array_in('id_tag', $ids), $ids);
		$count_manga = $this->db->get_table('art_manga_tag_count',
			array('id_tag', 'count'), $this->db->array_in('id_tag', $ids), $ids);
		$count_pack = $this->db->get_table('art_pack_tag_count',
			array('id_tag', 'count'), $this->db->array_in('id_tag', $ids), $ids);

		foreach ($variants as $variant) {
			$links[$variant['id_tag']][] = $variant['name'];
		}

		foreach ($data as &$item) {
			$item['count'] = 0;
			foreach ($count as $number) {
				if ($item['id'] == $number['id_tag']) {
					$item['count'] = $number['count'];
					continue 2;
				}
			}
		}
		foreach ($data as &$item) {
			$item['count_artist'] = 0;
			foreach ($count_artist as $number) {
				if ($item['id'] == $number['id_tag']) {
					$item['count'] = $number['count'];
					continue 2;
				}
			}
		}
		foreach ($data as &$item) {
			$item['count_group'] = 0;
			foreach ($count_group as $number) {
				if ($item['id'] == $number['id_tag']) {
					$item['count'] = $number['count'];
					continue 2;
				}
			}
		}
		foreach ($data as &$item) {
			$item['count_manga'] = 0;
			foreach ($count_manga as $number) {
				if ($item['id'] == $number['id_tag']) {
					$item['count'] = $number['count'];
					continue 2;
				}
			}
		}
		foreach ($data as &$item) {
			$item['count_pack'] = 0;
			foreach ($count_pack as $number) {
				if ($item['id'] == $number['id_tag']) {
					$item['count'] = $number['count'];
					continue 2;
				}
			}
		}

		return $data;
	}
}