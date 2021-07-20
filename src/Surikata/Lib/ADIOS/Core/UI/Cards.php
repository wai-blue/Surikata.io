<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Cards extends \ADIOS\Core\UI\View {
  public function render(string $panel = "") {
    $model = $this->adios->getModel($this->params['model']);

    $params = $model->cardsParams($this->params);
    $model->tmpCardParams = $params;

    switch ($params['columns'] ?? 3) {
      case 1: default: $bootstrapColumnSize = 12; break;
      case 2: $bootstrapColumnSize = 6; break;
      case 3: $bootstrapColumnSize = 4; break;
      case 4: $bootstrapColumnSize = 3; break;
      case 6: $bootstrapColumnSize = 2; break;
    }
    
    $cards = $model->getWithLookups(function($model, $query) {
      if (!empty($model->tmpCardParams['where'])) {
        $query = $query->whereRaw($model->tmpCardParams['where']);
      }
      return $query;
    });

    // $html = _print_r($cards, TRUE);
    $html = "
      <div class='row'>
    ";
    foreach ($cards as $card) {
      $html .= "
        <div class='col-lg-{$bootstrapColumnSize} col-md-12'>
          ".$model->cardsCardHtmlFormatter($card)."
        </div>
      ";
    }
    $html .= "
      </div>
    ";

    if ($this->adios->isWindow()) {
      $html = $this->adios->ui->Window([
        'content' => $html,
        'titleRaw' => $params['window']['titleRaw'],
        'title' => $params['window']['title'],
        'subtitle' => $params['window']['subtitle'],
      ])->render();
    }

    return $html;
  }
}
