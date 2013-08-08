<?php

namespace Otaku\Api;

class Transform_Upload_Art extends Transform_Upload_Abstract_Image
{
	protected $md5 = 'unknown';

	public function __construct($file, $name, $base_path = false) {
		parent::__construct($file, $name, $base_path);

		$this->md5 = md5_file($this->file);
	}

	protected function get_max_size() {
		return Config::get('art', 'filesize');
	}

	protected function test_file() {
		parent::test_file();

		$id = Database::db('api')->get_field('art', 'id', 'md5 = ?', $this->md5);
		if (!empty($id)) {
			throw new Error_Upload($id, Error_Upload::ALREADY_EXISTS);
		}
	}

	protected function process() {
		$pathinfo = pathinfo($this->name);
		$extension = strtolower($pathinfo['extension']);
		$base = $this->get_base_path();

		$file = $base.SL.'art'.SL.$this->md5.'.'.$extension;
		$resized = $base.SL.'art'.SL.$this->md5.'_resize.jpg';
		$thumb = $base.SL.'art'.SL.$this->md5.'_thumb.jpg';
		$largethumb = $base.SL.'art'.SL.$this->md5.'_largethumb.jpg';

		chmod($this->file, 0755);

		if (!move_uploaded_file($this->file, $file)) {
			file_put_contents($file, file_get_contents($this->file));
		}

		$this->worker = Transform_Image::get_worker($file);
		$this->animated = $this->is_animated($file);
		$width = $this->worker->get_image_width();
		$height = $this->worker->get_image_height();

		$resized = $this->check_resize($resized);

		$this->scale(Config::get('art', 'largethumbsize'), $largethumb);
		$this->scale(Config::get('art', 'thumbsize'), $thumb);

		$this->set(array(
			'image' => 'art/' . $this->md5 . '.' . $extension,
			'thumbnail' => 'art/' . $this->md5 . '_thumb.jpg',
			'md5' => $this->md5,
			'name' => $this->name,
			'extension' => $extension,
			'resized' => (int) $resized,
			'animated' => (int) $this->animated,
			'width' => (int) $width,
			'height' => (int) $height,
			'weight' => (int) $this->size
		));
	}

	public function resize() {
		$base = $this->get_base_path();
		$resized = $base.SL.'art'.SL.$this->md5.'_resize.jpg';

		$this->worker = Transform_Image::get_worker($this->file);
		$this->animated = $this->is_animated($this->file);

		$resized = $this->check_resize($resized);

		return $resized;
	}

	protected function check_resize($target) {
		$resized = false;
		$this->sizes = $this->worker->get_image_width().'x'.$this->worker->get_image_height();

		$resize_width = Config::get('art', 'resizewidth') * Config::get('art', 'resizestep');
		if (
			$this->worker->get_image_width() > $resize_width ||
			$this->info[0] > $resize_width
		) {
			if ($this->scale(Config::get('art', 'resizewidth'), $target, 95, false)) {
				$resized = true;
			}
		} elseif ($this->size > Config::get('art', 'resizeweight')) {
			if ($this->scale(false, $target, 95, false)) {
				$resized = true;
			}
		}

		return $resized;
	}
}
