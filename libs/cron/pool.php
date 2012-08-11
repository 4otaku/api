<?php

class Cron_Pool extends Cron_Abstract
{
	protected function delete_empty()
	{
		$this->db->sql('delete from art_pack where id not in (SELECT id_pack FROM art_pack_item)');
		$this->db->sql('delete from art_manga where id not in (SELECT id_manga FROM art_manga_item)');
		$this->db->sql('delete from art_group where id not in (SELECT id_group FROM art_group_item)');

		return memory_get_usage();
	}
}
