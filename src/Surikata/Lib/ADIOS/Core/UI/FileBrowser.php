<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

/* akcia, ktora okrem Input FileBrowser generuje aj nejake window/buttony/titles a funkcionalitu okolo */
/* pouziva \ADIOS\Core\UI\Input\FileBrowser */
class FileBrowser extends \ADIOS\Core\UI\View {
  public function render(string $panel = "") {
    $this->params['onchange'] = "{$this->uid}_close($(this).val());";

    $inputHtml = (new \ADIOS\Core\UI\Input\FileBrowser(
      $this->adios,
      $this->params['uid'],
      $this->params
    ))->render();
    
    if ($this->params['__IS_WINDOW__']) {
      $html = $this->adios->ui->Window(
        [
          'uid' => "{$this->uid}_window",
          'content' => "
            {$inputHtml}
            <script>
              function {$this->uid}_close(res) {
                try {
                  window_close('{$this->uid}_window', res);
                } catch(e) { }
              }
            </script>
          ",
          'header' => [
            $this->adios->ui->Button(["text" => "Close", "type" => "close", "onclick" => "{$this->uid}_close();"]),
          ],
          'title' => htmlspecialchars($this->params['title']),
        ]
      )->render();
    } else {
      $html = $this->adios->ui->Title([
        'center' => $this->params['title'] ?? "File browser",
      ])->render();

      $html .= $inputHtml;
    }

    return $html;
  }
}
