<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\FileBrowser;

class Upload extends \ADIOS\Core\Action {
  public static $hideDefaultDesktop = TRUE;

  public function render() {
    if (!empty($_FILES['upload'])) {

      $folderPath = $_REQUEST['folderPath'] ?? "";
      $uploadedFilename = $_FILES['upload']['name'];

      if (empty($folderPath)) $folderPath = ".";

      $sourceFile = $_FILES['upload']['tmp_name'];
      $destinationFile = "{$this->adios->config['files_dir']}/{$folderPath}/{$uploadedFilename}";

      $uploadedFileExtension = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));

      $error = "";
      if (in_array($uploadedFileExtension, ['php', 'sh', 'exe', 'bat', 'htm', 'html'])) {
        $error = l('Súbor tohto typu nie je povolený.');
      } elseif (!empty($_FILES['upload']['error'])) {
        $error = l('Nastala chyba pri nahrávaní súboru.').' '.l('Maximálna povolená veľkosť je:').' '.ini_get('upload_max_filesize');
      } elseif (empty($_FILES['upload']['tmp_name']) || 'none' == $_FILES['upload']['tmp_name']) {
        $error = l('Nastala chyba pri nahrávaní súboru.');
      } elseif (file_exists($destinationFile)) {
        $error = l("Súbor s týmto názvom už existuje. {$destinationFile}");
      }

      if (empty($error)) {
        move_uploaded_file($sourceFile, $destinationFile);

        echo json_encode([
          'uploaded' => 1,
          'fileName' => $uploadedFilename,
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