<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\FileBrowser;

class RenameFolder extends \ADIOS\Core\Action {
  public function render() {
    $folder = $this->params['folder'];
    $newFolderName = $this->params['newFolderName'];

    foreach (explode("/", $folder) as $tmp) {
      if ($tmp == "..") return "Invalid folder path.";
    }

    $dir = $this->adios->config['files_dir'];

    if (!empty($dir) && rename("{$dir}/{$folder}", "{$dir}/".dirname($folder)."/{$newFolderName}")) {
      return "1"; // "1 = {$dir}/{$folder}, {$dir}/".dirname($folder)."/{$newFolderName}";
    } else {
      return "Failed to rename folder: {$folder}.";
    }
  }
}