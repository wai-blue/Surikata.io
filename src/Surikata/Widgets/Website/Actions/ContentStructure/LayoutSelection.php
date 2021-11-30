<?php

namespace ADIOS\Actions\Website\ContentStructure;

class LayoutSelection extends \ADIOS\Core\Widget\Action {
  public function render() {

    $contentStructure = @json_decode($this->params['contentStructure'], TRUE);
    $themeName = $this->adios->config['settings']['web'][$this->params['domain']]['design']['theme'];
    $activatedLayoutName = $contentStructure['layout'];

    $theme = $this->adios->widgets['Website']->themes[$themeName];
    $layouts = $theme->getLayouts();

    $layoutsHtml = "";
    foreach ($layouts as $layoutName) {

      $layout = $theme->getLayout($layoutName);
      if (is_object($layout)) {
        $layoutPreviewHtml = $layout->getPreviewHtml();
      } else {
        $layoutPreviewHtml = "Can't find layout definition for <i>{$layoutName}</i> in theme <i>{$themeName}</i>.";
      }

      $layoutsHtml .= "
        <div class='col col-4 card shadow'>
          <div class='card-header py-3'>
            <h6 class='m-0 font-weight-bold text-primary'>".hsc($layoutName)."</h6>
          </div>
          <div class='card-body'>
            <div
              class='surikata-theme-preview-item ".($layoutName == $activatedLayoutName ? "selected" : "")."'
              onclick='{$this->uid}.apply(\"".ads($layoutName)."\");'
            >
              {$layoutPreviewHtml}
            </div>
          </div>
        </div>
      ";
    }

    $windowContentHtml = "
      <script>
        let {$this->uid} = {
          uid: '{$this->uid}',

          close: function(res) {
            window_close(this.uid + '_window', res);
          },

          apply: function(layoutName) {
            this.close(layoutName);
          },
        }

      </script>
      <div class='container'>
        <div id='{$this->uid}_wrapper' class='row surikata-theme-preview-wrapper'>
          {$layoutsHtml}
        </div>
      </div>
      <script>
        $('#{$this->uid}_wrapper *[data-panel-name=\"{$panelName}\"]').addClass('selected');
      </script>
    ";

    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "content" => $windowContentHtml,
      "title" => "Choose page layout",
      "header" => [
        $this->adios->ui->Button(["type" => "close", "onclick" => "{$this->uid}.close()"]),
        // $this->adios->ui->Button(["type" => "apply", "onclick" => "{$this->uid}.apply();"]),
      ]
    ])->render();
  }
}