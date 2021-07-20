<?php

namespace Surikata\Core\Web;

class ImageProcessor {
  public $image;
  public $imageType;

  public function __construct($filename = "") {
    if (!empty($filename)) {
      $this->load($filename);
    }
  }

  public function load($filename) {
    $imageInfo = getimagesize($filename);
    $this->imageType = $imageInfo[2];

    switch ($this->imageType) {
      case IMAGETYPE_JPEG: $this->image = imagecreatefromjpeg($filename); break;
      case IMAGETYPE_GIF: $this->image = imagecreatefromgif($filename); break;
      case IMAGETYPE_PNG: $this->image = imagecreatefrompng($filename); break;
    }
  }

  public function save($filename, $imageType = NULL, $compression = 70) {
    if ($imageType === NULL) {
      $imageType = $this->imageType;
    }

    switch ($imageType) {
      case IMAGETYPE_JPEG: imagejpeg($this->image, $filename, $compression); break;
      case IMAGETYPE_GIF: imagegif($this->image, $filename); break;
      case IMAGETYPE_PNG: imagepng($this->image, $filename, (100 - $compression) * 0.1); break;
    }
  }

  public function output($imageType = NULL) {
    if ($imageType === NULL) {
      $imageType = $this->imageType;
    }

    switch ($imageType) {
      case IMAGETYPE_JPEG:
        imagejpeg($this->image);
      break;
      case IMAGETYPE_GIF:
        imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagegif($this->image);
      break;
      case IMAGETYPE_PNG:
        imagecolortransparent($this->image, imagecolorallocatealpha($this->image, 0, 0, 0, 127));
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        imagepng($this->image);
      break;
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