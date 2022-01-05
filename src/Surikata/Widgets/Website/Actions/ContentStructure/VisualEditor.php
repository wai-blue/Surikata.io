<?php

namespace ADIOS\Actions\Website\ContentStructure;

class VisualEditor extends \ADIOS\Core\Widget\Action {
  public function render() {

    $idWebPage = (int) $this->params['idWebPage'];
    $domainName = $this->params['domain'];

    $domainInfo = $this->adios->getDomainInfo($domainName);
    $iframeToken = uniqid("", TRUE);

    $modelWebPage = new \ADIOS\Widgets\Website\Models\WebPage($this->adios);
    $webPage = $modelWebPage->getById($idWebPage);
    
    $windowContentHtml = print_r($webPage, TRUE);
    $windowContentHtml = "
      <div style='background:#F8F8F8'>
        <iframe
          src='//{$domainInfo['rootUrl']}/{$webPage['url']}?_vce=1&_vcetkn={$iframeToken}'
          style='
            width:100%;
            height:calc(100vh - 200px);
          '
        >Loading...</iframe>
      </div>

      <script>
        window.addEventListener(
          'message',
          {$this->uid}_onOpenPanel,
          '{$iframeToken}'
        );

        function {$this->uid}_close(res) {
          window.removeEventListener(
            'message',
            {$this->uid}_onOpenPanel,
            '{$iframeToken}'
          );
          window_close('{$this->uid}_window', res);
        }

        function {$this->uid}_onOpenPanel(event) {
          if (
            event.data.initiator == 'visualContentEditor'
            && event.data.action == 'openPanel'
            && event.data.iframeToken == '{$iframeToken}'
          ) {
            window_render(
              'Website/ContentStructure/PluginSettings',
              {
                'idWebPage': {$idWebPage},
                'panelName': event.data.panelName,
                'domain': '{$domainName}'
              },
              function(res) {
                {$this->uid}_close(
                  {
                    'panelName': event.data.panelName,
                    'panelContent': res,
                  }
                );
              }
            );

            return false;
          }
        }
      </script>
    ";

    return $this->adios->ui->Window([
      "uid" => "{$this->uid}_window",
      "content" => $windowContentHtml,
      "title" => $this->translate("Visual content editor")." (BETA)",
      "header" => [
        $this->adios->ui->Button(["type" => "close", "onclick" => "{$this->uid}_close();"]),
      ]
    ])->render();
  }
}