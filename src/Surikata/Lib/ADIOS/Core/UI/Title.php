<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Title extends \ADIOS\Core\UI\View {
    public function __construct(&$adios, $params = null) {

      parent::__construct($adios, $params);

      if ($this->params['fixed']) {
          $this->add_class('fixed');
      }

      $this->add($this->params['left'], 'left');
      $this->add($this->params['right'], 'right');
      $this->add($this->params['center'], 'center');
    }

    public function render($render_panel = '') {
      $center = (string) parent::render('center');
      $center = trim($center);

      return "
        <div class='adios ui Title'>
          ".(empty($center) ? "" : "
            <div class='row mb-3'>
              <div class='col-lg-12 p-0'>
                <div class='h3 text-primary mb-0'>{$center}</div>
              </div>
            </div>
          ")."
          <div class='row mb-3'>
            <div class='col-lg-6 p-0'>
              ".parent::render('left')."
            </div>
            <div class='col-lg-6 text-right'>
              ".parent::render('right')."
            </div>
          </div>
        </div>
      ";
    }
}
