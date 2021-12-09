<?php

namespace ADIOS\Widgets\Website\Inputs;

class ContentStructure extends \ADIOS\Core\Input {
  public function render() {
    $domain = $this->params['form_data']['domain'];
    $themeName = $this->adios->config['settings']['web'][$domain]['design']['theme'];

    $theme = $this->adios->widgets['Website']->themes[$themeName];

    $layouts = $theme->getLayouts();

    $value = @json_decode($this->value, TRUE);

    $randUid = $this->uid.time().rand(1000, 9999);

    $layoutsHtml = "";
    foreach ($layouts as $layoutName) {
      $layout = $theme->getLayout($layoutName);
      $layoutsHtml .= "
        <div
          class='col col-12 surikata-theme-preview-item selected'
          data-layout-name='".ads($layoutName)."'
          style='display:none;position:absolute;width:100%;'
        >
          ".(is_object($layout) ? $layout->getPreviewHtml() : "")."
        </div>
      ";
    }

    $inputHtml = "
      <div>
        <div
          class='row surikata-theme-preview-wrapper'
          data-input-id='{$randUid}'
          style='position:relative;height:calc(100vh - 27em);overflow:auto;'
        >
          {$layoutsHtml}
        </div>
        <textarea style='display:none' id='{$this->uid}'>{$this->value}</textarea>

        ".$this->adios->ui->Button([
          "text" => $this->translate("Change layout"),
          "fa_icon" => "fas fa-th",
          "class" => "btn btn-info btn-icon-split my-2",
          "onclick" => "
            window_render(
              'Website/ContentStructure/LayoutSelection',
              {
                'contentStructure': {$this->uid}_data,
                'domain': '{$domain}'
              },
              function(res) {
                if (res) {
                  {$this->uid}_data['layout'] = res;
                  {$this->uid}_serialize();
                  {$this->uid}_showActivatedLayout();
                }
              }
            );
          ",
        ])->render()."

      </div>
      <script>
        var {$this->uid}_data = {'layout': ''};
        ".(empty($this->value) ? "" : "
          {$this->uid}_data = JSON.parse('".ads($this->value)."');
        ")."


        function {$this->uid}_serialize() {
          $('#{$this->uid}').val(JSON.stringify({$this->uid}_data));
        }

        function {$this->uid}_init() {
          $('.surikata-theme-preview-wrapper[data-input-id=\"{$randUid}\"]')
            .find('*')
            .filter(function() { return typeof $(this).attr('data-panel-name') != 'undefined'; })
            .click(function() {
              if ($(this).closest('.surikata-theme-preview-item').hasClass('selected')) {

                let panelName = $(this).attr('data-panel-name');
                let _this = $(this);

                window_render(
                  'Website/ContentStructure/PluginSettings',
                  {
                    'contentStructure': {$this->uid}_data,
                    'panelName': panelName,
                    'domain': '{$domain}'
                  },
                  function(res) {
                    if (res) {
                      if (!{$this->uid}_data['panels']) {
                        {$this->uid}_data['panels'] = {};
                      }

                      {$this->uid}_data['panels'][panelName] = res;

                      {$this->uid}_serialize();
                      {$this->uid}_updatePanelInfo();
                    }
                  }
                );

                return false;
              }
            })
          ;
        }

        function {$this->uid}_showActivatedLayout() {
          $('.surikata-theme-preview-wrapper[data-input-id={$randUid}]')
            .find('.surikata-theme-preview-item:visible')
            .fadeOut(500)
          ;
          $('.surikata-theme-preview-wrapper[data-input-id={$randUid}]')
            .find('.surikata-theme-preview-item[data-layout-name=' + {$this->uid}_data['layout'] + ']')
            .fadeIn(500)
          ;
          {$this->uid}_updatePanelInfo();
        }

        function {$this->uid}_updatePanelInfo() {
          if (!{$this->uid}_data['panels']) {
            {$this->uid}_data['panels'] = {};
          }

          for (var panelName in {$this->uid}_data['panels']) {
            let panelInfo = {$this->uid}_data['panels'][panelName];
            let settingsHtml = '';
            let panelInfoHtml = '<div>[' + panelName + ']</div>';

            if (panelInfo.plugin) {
              for (var i in panelInfo.settings) {
                let str = panelInfo.settings[i].toString().replace(/(<([^>]+)>)/gi, '');
                if (str.length > 0) {
                  if (str.length > 100) {
                    str = str.substring(0, 100) + ' ...';
                  }

                  settingsHtml +=
                    '<b>' + i + '</b>: ' + str + '<br/>'
                  ;
                }
              }

              panelInfoHtml = 
                '<div>[' + panelName + '] ' + panelInfo.plugin + '</div>' +
                (settingsHtml == '' ? '' : '<div>' + settingsHtml + '</div>')
              ;
            }

            $('.surikata-theme-preview-wrapper[data-input-id=\"{$randUid}\"]')
              .find('.surikata-theme-preview-item[data-layout-name=' + {$this->uid}_data['layout'] + ']')
              .find('*[data-panel-name=' + panelName + ']')
              .html(panelInfoHtml)
            ;
          }
        }


        {$this->uid}_init();
        {$this->uid}_showActivatedLayout();
        {$this->uid}_updatePanelInfo();
      </script>
    ";

    return $inputHtml;
  }

  public function formatValueToHtml() {
    // return "<div style='background:orange;padding:3px;color:white;'>Rozlozenie: #{$this->value}</div>";
    if (empty($this->value)) {
      return NULL;
    } else {
      $themeName = $this->adios->config['settings']['web']['design']['theme'];
      $theme = $this->adios->widgets['Website']->themes[$themeName];
      $value = @json_decode($this->value, TRUE);

      if (is_object($theme)) {
        $layout = $theme->getLayout($value['layout']);
        if (is_object($layout)) {
          return "
            <div class='surikata-theme-preview-wrapper table-cell'>
              <div>".$layout->getPreviewHtml()."</div>
            </div>
          ";
        } else {
          return NULL;
        }
      } else {
        return NULL;
      }
    }
  }
}
