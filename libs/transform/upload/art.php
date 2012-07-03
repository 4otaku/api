<?php

class Transform_Upload_Art extends Transform_Upload_Abstract_Image
{
	protected $md5 = 'unknown';

	protected function get_max_size() {
		return Config::get('art', 'filesize');
	}

	protected function test_file() {
		parent::test_file();

		$md5 = md5_file($this->file);
		if ($id = Database::db('api')->get_field('art', 'id', 'md5 = ?', $md5)) {
			throw new Error_Upload($id, Error_Upload::ALREADY_EXISTS);
		}

		$this->md5 = $md5;
	}

	protected function process() {
		$pathinfo = pathinfo($this->name);

		$extension = strtolower($pathinfo['extension']);

		$newfile = IMAGES.SL.'art'.SL.$this->md5.'.'.$extension;
		$newresized = IMAGES.SL.'art'.SL.$this->md5.'_resize.jpg';
		$newthumb = IMAGES.SL.'art'.SL.$this->md5.'_thumb.jpg';
		$newlargethumb = IMAGES.SL.'art'.SL.$this->md5.'_largethumb.jpg';

		chmod($this->file, 0755);

		if (!move_uploaded_file($this->file, $newfile)) {
			file_put_contents($newfile, file_get_contents($this->file));
		}

		$this->worker = Transform_Image::get_worker($newfile);
		$this->animated = $this->is_animated($newfile);

		$resized = $this->check_resize($newresized);

		$this->scale(Config::get('art', 'largethumbsize'), $newlargethumb);
		$this->scale(Config::get('art', 'thumbsize'), $newthumb);

		$this->set(array(
			'image' => '/images/art/'.$this->md5.'_thumb.jpg',
			'md5' => $this->md5,
			'extension' => $extension,
			'resized' => (int) $resized,
			'animated' => (int) $this->animated,
			'width' => (int) $this->worker->get_image_width(),
			'height' => (int) $this->worker->get_image_height(),
			'weight' => (int) $this->size,
			'meta' => $this->get_file_meta($pathinfo['filename'])
		));
	}

	public function resize() {
		$md5 = md5_file($this->file);
		$newresized = IMAGES.SL.'booru'.SL.'resized'.SL.$md5.'.jpg';

		$this->worker = Transform_Image::get_worker($this->file);
		$this->animated = $this->is_animated($this->file);

		$resized = $this->check_resize($newresized);

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

	protected function get_file_meta ($filename) {

		if (stripos($filename, 'auto_tag=') !== 0) {
			return array();
		}

		$filename = str_ireplace('auto_tag=', '', $filename);

		$filename = explode('=', $filename);

		if (count($filename) != 3 || !is_numeric($filename[0]) || !is_numeric($filename[1])) {
			return array();
		}

		$tags = preg_split('/[\+\s]+/u', $filename[2]);

		return array(
			'tags' => $tags,
			'id_group' => $filename[0],
			'id_in_group' => $filename[1],
		);
	}
}
