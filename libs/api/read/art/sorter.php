<?php

class Api_Read_Art_Sorter
{
	protected $item = 0;
	protected $value = false;
	protected $type = 'none';
	protected $direction = 'desc';

	public function __construct($item, $type = 'none', $direction = 'desc', $value = false) {
		if (method_exists($this, $type)) {
			$this->type = $type;
		}

		if ($direction == 'asc') {
			$this->direction = $direction;
		}

		$this->value = $value;
		$this->item = (int) $item;
	}

	public function apply_to($sql) {
		$method = $this->type;
		$this->$method($sql);
	}

	protected function none($sql) {}

	protected function random($sql) {
		$sorter = new Database_Sorter('id');
		$sorter->add_operation('random');
		$sql->order($sorter);
	}

	protected function date($sql) {
		$sql->order('sortdate', $this->direction);
	}

	protected function width($sql) {
		$sql->order('width', $this->direction);
	}

	protected function height($sql) {
		$sql->order('height', $this->direction);
	}

	protected function weight($sql) {
		$sql->order('weight', $this->direction);
	}

	protected function size($sql) {
		$sorter = new Database_Sorter('width', $this->direction);
		$sorter->add_operation('multiply', 'height');
		$sql->order($sorter);
	}

	protected function parent_order($sql) {
		$sql->order('id_parent_order', $this->direction);
	}

	protected function rating($sql) {
		$sql->join('meta', 'm.id_item = id and m.item_type = ' .
			$this->item . ' and meta_type = ' . Meta::ART_RATING)
			->order('m.meta', $this->direction);
	}

	protected function comment_count($sql) {
		$sql->join('meta', 'm.id_item = id and m.item_type = ' .
			$this->item . ' and meta_type = ' . Meta::COMMENT_COUNT)
			->order('m.meta', $this->direction);
	}

	protected function comment_date($sql) {
		$sql->join('meta', 'm.id_item = id and m.item_type = ' .
			$this->item . ' and meta_type = ' . Meta::COMMENT_DATE)
			->order('m.meta', $this->direction);
	}

	protected function tag_count($sql) {
		$sql->join('meta', 'm.id_item = id and m.item_type = ' .
			$this->item . ' and meta_type = ' . Meta::TAG_COUNT)
			->order('m.meta', $this->direction);
	}

	protected function translation_date($sql) {
		$sql->join('meta', 'm.id_item = id and m.item_type = ' .
			$this->item . ' and meta_type = ' . Meta::TRANSLATION_DATE)
			->order('m.meta', $this->direction);
	}

	protected function manga($sql) {
		$sql->join('art_manga_item', 'ami.id_art = id and ami.id_manga = ' .
			(int) $this->value)->order('ami.order', $this->direction);
	}

	protected function pack($sql) {
		$sql->join('art_pack_item', 'api.id_art = id and api.id_pack = ' .
			(int) $this->value)->order('api.order', $this->direction);
	}
}
