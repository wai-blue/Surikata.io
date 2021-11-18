<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI\Table\Search;

/**
 * @package UI\Actions
 */
class SavedSearchesOverview extends \ADIOS\Core\Action {
  public function render() {
    $searchGroup = $this->params["searchGroup"];
    $savedSearches = $this->adios->config["UI"]["Table"]["savedSearches"][$searchGroup];
    $parentUid = $this->params["parentUid"];

    $this->adios->checkUid($parentUid);
    
    $savedSearchesHtml = "";
    if (_count($savedSearches)) {
      foreach ($savedSearches as $searchName => $savedSearch) {
        $savedSearchesHtml .= "
          <div class='mb-1'>
            <a
              href='javascript:void(0);'
              onclick='
                {$parentUid}_search(\"{$savedSearch['search']}\")
              '
              class='btn btn-primary btn-icon-split'
            >
              <span class='icon text-white-50'>
                <i class='fas fa-flag'></i>
              </span>
              <span class='text'>".hsc($searchName)."</span>
            </a>
            <a
              href='javascript:void(0);'
              onclick='
                {$parentUid}_delete_saved_search(\"".ads($searchName)."\");
              '
              class='btn btn-danger float-right'
            >
              <span class='icon text-white-50'>
                <i class='fas fa-trash'></i>
              </span>
            </a>
            <!-- <a
              href='javascript:void(0);'
              onclick='
                {$parentUid}_load_saved_search(\"".ads($searchName)."\");
              '
              class='btn btn-info float-right'
            >
              <span class='icon text-white-50'>
                <i class='fas fa-sync'></i>
              </span>
            </a> -->
          </div>
        ";
      }
    } else {
      $savedSearchesHtml = $this->translate("You have no saved searches yet.");
    }

    return $savedSearchesHtml;
  }
}
