<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\FileBrowser;

/**
 * @package UI\Actions\FileBrowser
 */
class Upload extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function render() {
    if (!empty($_FILES['upload'])) {

      $folderPath = $_REQUEST['folderPath'] ?? "";

      if (strpos($folderPath, "..") !== FALSE) {
        $folderPath = "";
      }

      $uploadedFilename = $_FILES['upload']['name'];

      if (empty($folderPath)) $folderPath = ".";

      if (!is_dir("{$this->adios->config['files_dir']}/{$folderPath}")) {
        @mkdir("{$this->adios->config['files_dir']}/{$folderPath}", 0775);
      }

      $sourceFile = $_FILES['upload']['tmp_name'];
      $destinationFile = "{$this->adios->config['files_dir']}/{$folderPath}/{$uploadedFilename}";

      $uploadedFileExtension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));

      $error = "";
      if (in_array($uploadedFileExtension, ['php', 'sh', 'exe', 'bat', 'htm', 'html'])) {
        $error = "This file type cannot be uploaded";
      } elseif (!empty($_FILES['upload']['error'])) {
        $error = "File is too large. Maximum size of file to upload is ".round(ini_get('upload_max_filesize'), 2)." MB.";
      } elseif (empty($_FILES['upload']['tmp_name']) || 'none' == $_FILES['upload']['tmp_name']) {
        $error = "Failed to upload the file for an unknown error. Try again in few minutes.";
      // } elseif (file_exists($destinationFile)) {
      //   $error = "File with this name is already uploaded.";
      }

      if (empty($error)) {
        move_uploaded_file($sourceFile, $destinationFile);

        echo json_encode([
          'uploaded' => 1,
          'fileName' => $uploadedFilename,
          'fileSize' => filesize($destinationFile),
          'url' => "{$this->adios->config['files_url']}/{$folderPath}/{$uploadedFilename}",
        ]);
      } else {
        echo json_encode([
          'uploaded' => 0,
          'error' => $error,
        ]);
      }

    }
  }
}