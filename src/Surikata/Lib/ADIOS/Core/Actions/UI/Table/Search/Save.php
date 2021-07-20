<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table\Search;
class Save extends \ADIOS\Core\Action {
  public function render() {
    $search = json_encode([
      "model" => $this->params['model'],
      "searchGroup" => $this->params['searchGroup'],
      "searchName" => $this->params['searchName'],
      "search" => $this->params['search'],
    ]);

    $this->adios->saveConfig(
      [
        "model" => $this->params['model'],
        "search" => $this->params['search'],
      ],
      "UI/Table/savedSearches/{$this->params['searchGroup']}/{$this->params['searchName']}/"
    );

    return $searchUID;
  }
}
