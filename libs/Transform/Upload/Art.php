<?php

namespace Otaku\Api;

use Otaku\Framework\TransformUploadAbstractImage;
use Otaku\Framework\TransformImage;
use Otaku\Framework\Config;
use Otaku\Framework\Database;
use Otaku\Framework\ErrorUpload;

class TransformUploadArt extends TransformUploadAbstractImage
{
	protected $md5 = 'unknown';

	public function __construct($file, $name, $base_path = false) {
		parent::__construct($file, $name, $base_path);

		$this->md5 = md5_file($this->file);
	}

	protected function get_max_size() {
		return Config::getInstance()->get('art', 'filesize');
	}

	protected function test_file() {
		parent::test_file();

		$id = Database::db('api')->get_field('art', 'id', 'md5 = ?', $this->md5);
		if (!empty($id)) {
			throw new ErrorUpload($id, ErrorUpload::ALREADY_EXISTS);
		}
	}

	protected function process() {
		$pathinfo = pathinfo($this->name);
		$extension = strtolower($pathinfo['extension']);
		$base = $this->get_base_path();

		/**
		 * При передаче картинки по ссылке, например, из твиттера, в расширении файла может быть суффикс:
		 * https://pbs.twimg.com/media/D2gMVwtVAAAjRzv.jpg:orig
		 */
		if (preg_match('/(png|jpe?g|gif)/', $extension, $matches)) {
			$extension = $matches[0];
		}

		$file = $base.SL.'art'.SL.$this->md5.'.'.$extension;
		$resized = $base.SL.'art'.SL.$this->md5.'_resize.jpg';
		$thumb = $base.SL.'art'.SL.$this->md5.'_thumb.jpg';
		$largethumb = $base.SL.'art'.SL.$this->md5.'_largethumb.jpg';

		chmod($this->file, 0755);

		if (!move_uploaded_file($this->file, $file)) {
			file_put_contents($file, file_get_contents($this->file));
		}

		$this->worker = TransformImage::get_worker($file);
		$this->animated = $this->is_animated($file);
		$width = $this->worker->get_image_width();
		$height = $this->worker->get_image_height();

		$resized = $this->check_resize($resized);

		$config = Config::getInstance();
		$this->scale($config->get('art', 'largethumbsize'), $largethumb);
		$this->scale($config->get('art', 'thumbsize'), $thumb);

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

		$this->worker = TransformImage::get_worker($this->file);
		$this->animated = $this->is_animated($this->file);

		$resized = $this->check_resize($resized);

		return $resized;
	}

	protected function check_resize($target) {
		$config = Config::getInstance();
		$resized = false;
		$this->sizes = $this->worker->get_image_width().'x'.$this->worker->get_image_height();

		$resize_width = $config->get('art', 'resizewidth') *
			$config->get('art', 'resizestep');

		if (
			$this->worker->get_image_width() > $resize_width ||
			$this->info[0] > $resize_width
		) {
			if ($this->scale($config->get('art', 'resizewidth'), $target, 95, false)) {
				$resized = true;
			}
		} elseif ($this->size > $config->get('art', 'resizeweight')) {
			if ($this->scale(false, $target, 95, false)) {
				$resized = true;
			}
		}

		return $resized;
	}
}
