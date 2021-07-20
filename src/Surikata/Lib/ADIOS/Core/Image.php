<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL ^ E_NOTICE);

$width = (int) ($_GET['width'] ?? 0);
$cfg_name = $_GET['cfg'] ?? "";
$f = $_GET['f'] ?? "";

class SimpleImage
{
    public $image;
    public $image_type;

    public function load($filename)
    {
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

    public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 70, $permissions = null)
    {
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

    public function output($image_type = IMAGETYPE_JPEG)
    {
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

    public function getWidth()
    {
        return imagesx($this->image);
    }

    public function getHeight()
    {
        return imagesy($this->image);
    }

    public function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }

    public function zoom($scale)
    {
        if (1 != $scale) {
            $this->resize($this->getWidth() * $scale, $this->getheight() * $scale);
        }
    }

    public function resize($width, $height)
    {
        $img = imagecreatetruecolor($width, $height);
        imagecolortransparent($img, imagecolorallocatealpha($img, 0, 0, 0, 127));
        imagealphablending($img, false);
        imagesavealpha($img, true);
        $result = imagecopyresampled($img, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $img;

        return $result;
    }
}

if (!isset($this->adios->config['image']['configurations']['main_datapub_image'])) {
    $this->config['image']['configurations']['main_datapub_image'] = ['width' => 48, 'height' => 48, 'background' => 'transparent', 'use_cache' => false];
}

$this->config['image']['configurations']['pdw_thumb_tiles'] = ['width' => 48, 'height' => 48];
$this->config['image']['configurations']['pdw_thumb_content'] = ['width' => 32, 'height' => 32];
$this->config['image']['configurations']['pdw_thumb_large'] = ['width' => 97, 'height' => 97];
$this->config['image']['configurations']['pdw_thumb_small'] = ['width' => 48, 'height' => 48];

if (!isset($this->config['image']['configurations']['wa_list'])) {
    $this->config['image']['configurations']['wa_list'] = ['height' => 50, 'background' => 'transparent', 'use_cache' => false, 'constrain_proportions' => true];
}
if (!isset($this->config['image']['configurations']['input'])) {
    $this->config['image']['configurations']['input'] = ['width' => 120, 'height' => 84, 'background' => 'transparent', 'use_cache' => false, 'constrain_proportions' => true, 'fill_empty_area' => true];
}

$configurations = $this->config['image']['configurations'];
$output_header = isset($this->config['image']['output_header']) ? $this->config['image']['output_header'] : true;
$debug = isset($this->config['image']['debug']) ? $this->config['image']['debug'] : false;
$use_cache = isset($this->config['image']['use_cache']) ? $this->config['image']['use_cache'] : (isset($configurations[$cfg_name]['use_cache']) ? $configurations[$cfg_name]['use_cache'] : true);

if ($debug) {
    print_r($configurations);
}

$orig_filename = (empty($f) ? $this->config['image']['no_image_file'] : $f); /// orig filename je aj s castou cesty
if ($debug) {
    print_r("{$this->config['files_dir']}/{$orig_filename}");
}
if (!is_file("{$this->config['files_dir']}/{$orig_filename}") || !@is_array(getimagesize("{$this->config['files_dir']}/{$orig_filename}"))) {
    $orig_filename = $this->config['image']['no_image_file'];
}

$cfg = $configurations[$cfg_name] ?? [];
if ($debug) {
    print_r('use cfg: '.$cfg_name);
}
if ($debug) {
    print_r($cfg);
}

$no_image_render = FALSE;

if ('' == $orig_filename || !file_exists($this->config['files_dir'].'/'.$orig_filename)) {
    $orig_filename = dirname(__FILE__).'/../Assets/images/empty.png';
    $no_image_render = TRUE;
}

$file = realpath($this->config['files_dir'].'/'.$f);
if (!$no_image_render && realpath($this->config['files_dir']) != substr($file, 0, strlen(realpath($this->config['files_dir'])))) {
    echo 'ilegall access';
    if ($this->config['devel_mode']) {
        echo "<br/>{$this->config['files_dir']} -- $file ";
    }
    die();
}

if (!isset($configurations[$cfg_name])) { // ak nie je zadana konfiguracia, vratim povodny obrazok
    if ($output_header) {
        if ($no_image_render) {
            $tmp_h_file = "{$orig_filename}";
        } else {
            $tmp_h_file = "{$this->config['files_dir']}/{$orig_filename}";
        }

        $last_modified_time = filemtime($tmp_h_file);
        $etag = md5_file($tmp_h_file);

        header('Cache-Control: public, max-age=1209600');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
        header("Etag: $etag");

        if (
          strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? "") == $last_modified_time
          || trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? "") == $etag
        ) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    $img = new SimpleImage();
    if ($no_image_render) {
        $img->load("{$orig_filename}");
    } else {
        $img->load("{$this->config['files_dir']}/{$orig_filename}");
    }

    if (IMAGETYPE_JPEG == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/jpg');
        }
        $img->output(IMAGETYPE_JPEG);
    } elseif (IMAGETYPE_GIF == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/gif');
        }
        $img->output(IMAGETYPE_GIF);
    } elseif (IMAGETYPE_PNG == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/png');
        }
        $img->output(IMAGETYPE_PNG);
    }
}

if ($width > 0) {
    $cfg['width'] = $width;
    $cfg['height'] = round($width / ($configurations[$cfg_name]['width'] / $configurations[$cfg_name]['height']));
}

$img_cache_dir = "{$this->config['files_dir']}/___cache".(empty($cfg['subdir']) ? '' : "/{$cfg['subdir']}");

// kontrola ci adresar do ktoreho ma ist skonvertovany obrazok existuje ak neexistuje tak ho vytvorim
if (!is_dir($img_cache_dir)) {
    umask(0);
    @mkdir($img_cache_dir, 0777);
}

// vygenerujem nazov obrazka ulozeneho do cache

$tmp_new_filename = substr($orig_filename, 0, strrpos($orig_filename, '.'));
$tmp_new_extension = end(explode('.', $orig_filename));
if (strrpos($tmp_new_filename, '/')) {
    $tmp_new_filename = substr($tmp_new_filename, strrpos($tmp_new_filename, '/') + 1, strlen($tmp_new_filename));
}

$new_filename = md5($tmp_new_filename)."_{$cfg_name}_".$width.'.'.$tmp_new_extension;

// skontrolujem ci uz tento novy subor nahodou neexistuje ak existuje nemusim spracovavat
if (is_file("{$img_cache_dir}/{$new_filename}") && !$debug && !$this->config['devel_mode']) {
    if ($output_header) {
        $tmp_h_file = "{$img_cache_dir}/{$new_filename}";
        $last_modified_time = filemtime($tmp_h_file);
        $etag = md5_file($tmp_h_file);

        header('Cache-Control: public, max-age=1209600');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
        header("Etag: $etag");

        if (
          strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? "") == $last_modified_time
          || trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? "") == $etag
        ) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    $img = new SimpleImage();
    $img->load($img_cache_dir.'/'.$new_filename);
    if (IMAGETYPE_JPEG == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/jpg');
        }
        $img->output(IMAGETYPE_JPEG);
    } elseif (IMAGETYPE_GIF == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/gif');
        }
        $img->output(IMAGETYPE_GIF);
    } elseif (IMAGETYPE_PNG == $img->image_type) {
        if ($output_header) {
            header('Content-Type: image/png');
        }
        $img->output(IMAGETYPE_PNG);
    }
} else { // ak neexistuje tak ho musim spracovat a ulozit do cache
    $result = null;

    $img = new SimpleImage();

    if ($no_image_render) {
        $img->load("{$orig_filename}");
    } else {
        $img->load("{$this->config['files_dir']}/{$orig_filename}");
    }

    $tmp_cfg_width = (int) ($configurations[$cfg_name]['width'] ?? 0);
    $tmp_cfg_height = (int) ($configurations[$cfg_name]['height'] ?? 0);
    if ($tmp_cfg_width > 0 && $tmp_cfg_height > 0) {
        $cfg['width'] = $tmp_cfg_width;
        $cfg['height'] = $tmp_cfg_height;
    } elseif ($tmp_cfg_width > 0) {
        $cfg['width'] = $tmp_cfg_width;
        $cfg['height'] = round($img->getHeight() / ($img->getWidth() / $tmp_cfg_width));
    } elseif ($tmp_cfg_height > 0) {
        $cfg['height'] = $tmp_cfg_height;
        $cfg['width'] = round($img->getWidth() / ($img->getHeight() / $tmp_cfg_height));
    } else {
        $cfg['width'] = $img->getWidth();
        $cfg['height'] = $img->getHeight();
    }

    // zistim, ci je "na sirku" alebo "na vysku"
    if ($img->getWidth() > $img->getHeight()) {
        $orientation = 'landscape';
    } else {
        $orientation = 'portrait';
    }

    if ($cfg['width'] > $cfg['height']) {
        $orientation_cfg = 'landscape';
    } else {
        $orientation_cfg = 'portrait';
    }

    if ($cfg['crop'] ?? FALSE) {
        if ($img->getWidth() / $cfg['width'] * $cfg['height'] > $img->getHeight()) {
            $new_height = $cfg['height'];
            $new_width = $img->getWidth() / ($img->getHeight() / $cfg['height']);
        } else {
            $new_width = $cfg['width'];
            $new_height = $img->getHeight() / ($img->getWidth() / $cfg['width']);
        }

        $tmp_img = imagecreatetruecolor($cfg['width'], $cfg['height']);

        $white = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
        imagefill($tmp_img, 0, 0, $white);
        imagealphablending($tmp_img, false);
        imagesavealpha($tmp_img, true);

        imagecopyresampled(
            $tmp_img,
            $img->image,
            0 - ($new_width - $cfg['width']) / 2, // Center the image horizontally
            0 - ($new_height - $cfg['height']) / 2, // Center the image vertically
            0,
            0,
            $new_width,
            $new_height,
            $img->getWidth(),
            $img->getHeight()
        );

        $img->image = $tmp_img;

    } elseif ($cfg['constrain_proportions'] ?? FALSE) {
        $zoom = 1;

        if ('portrait' == $orientation && 'portrait' == $orientation_cfg) {
            $zoom = $cfg['width'] / $img->getWidth();
        }
        if ('landscape' == $orientation && 'portrait' == $orientation_cfg) {
            $zoom = $cfg['width'] / $img->getWidth();
        }
        if ('portrait' == $orientation && 'landscape' == $orientation_cfg) {
            $zoom = $cfg['height'] / $img->getHeight();
        }
        $zoom = min($cfg['width'] / $img->getWidth(), $cfg['height'] / $img->getHeight());

        if (!$output_header) {
            echo "<br/>$orientation, $orientation_cfg; $zoom; W_orig = ".$img->getWidth().', H_orig = '.$img->getHeight().'; W_zoom = '.($img->getWidth() * $zoom).', H_zoom = '.($img->getHeight() * $zoom).'<br/>';
        }

        $img->zoom($zoom);

        if ($cfg['fill_empty_area'] ?? FALSE) {
            $tmp_img = imagecreatetruecolor($cfg['width'], $cfg['height']);

            if ('transparent' != $cfg['background']) {
                $tmp_bkg_color = imagecolorallocate($tmp_img, $cfg['background'][0], $cfg['background'][1], $cfg['background'][2]);
                imagefill($tmp_img, 0, 0, $tmp_bkg_color);
            } else {
                $white = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
                imagefill($tmp_img, 0, 0, $white);
                imagealphablending($tmp_img, false);
                imagesavealpha($tmp_img, true);
            }

            $dst_x = round(($cfg['width'] - $img->getWidth()) / 2);
            $dst_y = round(($cfg['height'] - $img->getHeight()) / 2);
            $dst_w = $img->getWidth();
            $dst_h = $img->getHeight();
            $src_x = 0;
            $src_y = 0;
            $src_w = $img->getWidth();
            $src_h = $img->getHeight();
            $result = imagecopyresampled($tmp_img, $img->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

            $img->image = $tmp_img;
        }
    } else {
        $img->resize($cfg['width'], $cfg['height']);
    }

    if (!empty($cfg['watermark'])) {
        if (is_file($cfg['watermark']) && !stristr($orig_filename, '_wm.')) {
            $watermark = new SimpleImage();
            $watermark->load($cfg['watermark']);
            $watermark_width = $watermark->getWidth();
            $watermark_height = $watermark->getHeight();

            $dest_x = ($img->getWidth() / 2) - ($watermark_width / 2);
            $dest_y = ($img->getHeight() / 2) - ($watermark_height / 2);

            imagecopymerge($img->image, $watermark->image, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 45);
        }
    }

    if ($use_cache) {
        $img->save($img_cache_dir.'/'.$new_filename, $img->image_type);
    }

    if ($output_header) {
        if ($no_image_render) {
            $tmp_h_file = "{$orig_filename}";
        } else {
            $tmp_h_file = "{$this->config['files_dir']}/{$orig_filename}";
        }
        $last_modified_time = filemtime($tmp_h_file);
        $etag = md5_file($tmp_h_file);

        header('Cache-Control: public, max-age=1209600');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
        header("Etag: $etag");

        if (
          strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? "") == $last_modified_time
          || trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? "") == $etag
        ) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
    }

    if (!$debug) {
        if (IMAGETYPE_JPEG == $img->image_type) {
            if ($output_header) {
                header('Content-Type: image/jpeg');
            }
            $img->output(IMAGETYPE_JPEG);
        } elseif (IMAGETYPE_GIF == $img->image_type) {
            if ($output_header) {
                header('Content-Type: image/gif');
            }
            $img->output(IMAGETYPE_GIF);
        } elseif (IMAGETYPE_PNG == $img->image_type) {
            if ($output_header) {
                header('Content-Type: image/png');
            }
            $img->output(IMAGETYPE_PNG);
        }
    }
}
