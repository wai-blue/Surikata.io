<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

/* UI komponenta, ktora okrem Input Tree generuje aj nejake buttony/titles a save funkcionalitu okolo */
/* pouziva \ADIOS\Core\UI\Input\Tree */
class Tree extends \ADIOS\Core\UI\View {
  public function render(string $panel = "") {
    $this->model = $this->adios->getModel($this->params['model']);

    $inputUid = $this->adios->getUid($this->model->name);

    $contentHtml = (new \ADIOS\Core\UI\Input\Tree($this->adios, $inputUid, $this->params))->render();

    $contentHtml .= "
      <script>
        function {$this->uid}_close() {
          try {
            window_close('{$this->uid}_window');
          } catch(e) { }
        }

        function {$this->uid}_save() {
          let serialized = {$inputUid}_serialize();
          let data = {
            'model': '{$this->model->name}',
            'values': serialized,
          };

          _ajax_read('UI/Tree/Save', data, function(res) {
            if (isNaN(res)) {
              _alert(res);
            } else {
              $('#{$this->uid}_save_info_span').fadeIn();
              setTimeout(function() {
                $('#{$this->uid}_save_info_span').fadeOut();
              }, 1000);
            }
          });
        }

      </script>
    ";
    
    if ($this->params['__IS_WINDOW__']) {
      $html = $this->adios->ui->Window(
        [
          'uid' => "{$this->uid}_window",
          'content' => $contentHtml,
          'header' => [
            $this->adios->ui->Button(["text" => $this->translate("Close"), "type" => "close", "onclick" => "{$this->uid}_close();"]),
            $this->adios->ui->Button(["text" => $this->translate("Save"), "type" => "save", "onclick" => "{$this->uid}_save();"]),
            "
              <span id='{$this->uid}_save_info_span' class='pl-4' style='color:green;display:none'>
                <i class='fas fa-check'></i>
                  ".$this->translate("Saved")."
              </span>
            ",
          ],
          'form_close_click' => $this->params['onclose'],
          'title' => htmlspecialchars($this->params['title']),
        ]
      )->render();
    } else {
      $html = $this->adios->ui->Title([
        'left' => [
          "
            <span id='{$this->uid}_save_info_span' class='pr-4' style='color:green;display:none'>
              <i class='fas fa-check'></i>
                ".$this->translate("Saved")."
            </span>
          ",
          $this->adios->ui->Button(["text" => $this->translate("Save"), "type" => "save", "onclick" => "{$this->uid}_save();"]),
        ],
        'center' => $this->params['title']
      ])->render();
      $html .= $contentHtml;
    }

    return $html;
  }
}
