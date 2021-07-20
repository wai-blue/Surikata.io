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
      return "
        <div class='row mb-3'>
          <div class='col-lg-6 px-0'>
            <h1 class='h3 mb-0'>".parent::render('center')."</h1>
          </div>
          <div class='col-lg-6 text-right'>
            ".parent::render('left')."
            ".parent::render('right')."
          </div>
        </div>
      ";
    }
}
