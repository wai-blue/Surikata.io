<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Window extends \ADIOS\Core\UI\View {

  public function __construct(&$adios, $params = null) {
    $this->adios = $adios;

    $this->params = [
      'title' => 'Adios',
      'subtitle' => '',
      'content' => '',
      'footer' => '',
      'window' => [],
      'onclose' => '',
      'show_close_button' => false,
    ];

    parent::__construct($adios, $params);

    if (empty($this->params['header'])) {
      $this->params['header'] = [
        $this->adios->ui->Button([
          "type" => "close",
          "onclick" => "window_close('{$this->uid}');",
        ])
      ];
    }

  }

  public function setContent($content) {
    $this->params['content'] = $content;
  }

  public function render(string $panel = '') {
    $this->add($this->params['header'], 'header');
    $this->add($this->params['content'], 'content');
    $this->add($this->params['footer'], 'footer');

    $_REQUEST_without_action = $_REQUEST;
    unset($_REQUEST_without_action['action']);

    $html = "
      <span
        class='adios ui Window adios_window'
        id='{$this->params['uid']}'
      >
        <div class='header'>
          <div class='bg-white p-4 mb-4 shadow'>
            ".parent::render('header')."
            ".parent::render('footer')."
          </div>
        </div>
        <div class='content'>
          <div class='container-fluid'>
            <div class='h4 mb-2 text-primary'>
              ".(empty($this->params['titleRaw']) ? hsc($this->params['title']) : $this->params['titleRaw'])."
            </div>
            ".(empty($this->params['subtitle']) ? "" : "
              <div class='h6 mb-4'>
                ".hsc($this->params['subtitle'])."
              </div>
            ")."
            ".parent::render('content')."
          </div>
        </div>
      </span>

      <script>
        setTimeout(function() {
          $('#{$this->params['uid']}').addClass('activated');
        }, 0)
      </script>
    ";

    return $html;
  }
}
