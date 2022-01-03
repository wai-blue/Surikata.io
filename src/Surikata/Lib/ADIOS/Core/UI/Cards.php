<?php

namespace ADIOS\Core\UI;

/**
 * Renders Card-based list of elements.
 *
 * @package UI\Elements
 */
class Cards extends \ADIOS\Core\UI\View {
  var $useSession = TRUE;
  
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

    $html = "<div id='{$this->uid}'>";

    if ($params['show_add_button'] ?? FALSE) {
      $html .= "
        <div class='row mb-3'>
          ".$this->adios->ui->Button([
            "type" => "add",
            "onclick" => "
              window_render(
                '".$model->getFullUrlBase($this->params)."/Add',
                {},
                function(res) {
                  ui_cards_refresh('{$this->uid}')
                }
              );

            "
          ])->render()."
        </div>
      ";
    }

    $html .= "<div class='row'>";
    foreach ($cards as $card) {
      $html .= "
        <div class='col-lg-{$bootstrapColumnSize} col-md-12'>
          ".$model->cardsCardHtmlFormatter($this, $card)."
        </div>
      ";
    }
    $html .= "</div>"; // class='row'

    $html .= "</div>"; // id='{$this->uid}_wrapper_div'


    if ($this->params['__IS_WINDOW__']) {
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
