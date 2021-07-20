<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

//echo "downloading file";

if (function_exists('_adios_file_download_callback')) {
    _adios_file_download_callback($_GET['f']);
}

$file = realpath($this->config['files_dir'].'/'.$_GET['f']);
if (realpath($this->config['files_dir']) != substr($file, 0, strlen(realpath($this->config['files_dir'])))) {
    echo 'ilegall access';
    die();
}

if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: '.filesize($file));
    readfile($file);
    exit;
}
