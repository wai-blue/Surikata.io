<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Actions\UI;

/**
 * 'UI/Cards' action. Renders a UI/Cards element.
 *
 * Example call inside **Javascript** code (using AJAX):
 * ```
 * _ajax_update(
 *   'UI/Cards',
 *   {'model': 'MyWidget/Models/MyModel'},
 *   'DOM_element_id'
 * );
 * ```
 *
 * Example call inside **PHP** code (works but is *not optimal*):
 * ```
 * echo $adios->renderAction(
 *   "UI/Cards",
 *   ["model" => "MyWidget/Models/MyModel"]
 * );
 * ```
 *
 * @package UI\Actions
 */

class Cards extends \ADIOS\Core\Action {
  function render() {
    return (new \ADIOS\Core\UI\Cards($this->adios, $this->params))->render();
  }
}