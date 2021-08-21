<?php

namespace ADIOS\Core\Lib;

/**
 * Lightweight image manipulation class. Used by 'Image' action.
 *
 * @package Misc
 */
class SimpleImage {
  public $image;
  public $image_type;

  public function load($filename) {
    $image_info = getimagesize($filename);
    $this->image_type = $image_info[2];
    if (IMAGETYPE_JPEG == $this->image_type) {
      $this->image = imagecreatefromjpeg($filename);
    } elseif (IMAGETYPE_GIF == $this->image_type) {
      $this->image = imagecreatefromgif($filename);
    } elseif (IMAGETYPE_PNG == $this->image_type) {
      $this->image = imagecreatefrompng($filename);
    }
  }

  public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 70, $permissions = null) {
    if (IMAGETYPE_JPEG == $image_type) {
      imagejpeg($this->image, $filename, $compression);
    } elseif (IMAGETYPE_GIF == $image_type) {
      imagegif($this->image, $filename);
    } elseif (IMAGETYPE_PNG == $image_type) {
      imagepng($this->image, $filename, (100 - $compression) * 0.1);
    }

    if (null != $permissions) {
      chmod($filename, $permissions);
    }
  }

  public function output($image_type = IMAGETYPE_JPEG) {
    if (IMAGETYPE_JPEG == $image_type) {
      imagejpeg($this->image);
    } elseif (IMAGETYPE_GIF == $image_type) {
      imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
      imagealphablending($this->image, false);
      imagesavealpha($this->image, true);
      imagegif($this->image);
    } elseif (IMAGETYPE_PNG == $image_type) {
      imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
      imagealphablending($this->image, false);
      imagesavealpha($this->image, true);
      imagepng($this->image);
    }
  }

  public function getWidth() {
    return imagesx($this->image);
  }

  public function getHeight() {
    return imagesy($this->image);
  }

  public function resizeToHeight($height) {
    $ratio = $height / $this->getHeight();
    $width = $this->getWidth() * $ratio;
    $this->resize($width, $height);
  }

  public function resizeToWidth($width) {
    $ratio = $width / $this->getWidth();
    $height = $this->getheight() * $ratio;
    $this->resize($width, $height);
  }

  public function zoom($scale) {
    if (1 != $scale) {
      $this->resize($this->getWidth() * $scale, $this->getheight() * $scale);
    }
  }

  public function resize($width, $height) {
    $img = imagecreatetruecolor($width, $height);
    imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, false);
    imagesavealpha($img, true);
    $result = imagecopyresampled($img, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
    $this->image = $img;

    return $result;
  }
}