<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Button extends \ADIOS\Core\UI\View {
  public function __construct(&$adios, $params = null) {
    $this->adios = $adios;

    $this->languageDictionary["en"] = [
      "Uložiť" => "Save",
      "Použiť" => "Use",
      "Zavrieť" => "Close",
      "Kopírovať" => "Copy",
      "Pridať" => "Add",
      "Zmazať" => "Delete",
      "Zrušiť" => "Cancel",
      "Potvrdiť" => "Confirm",
      "Hľadať" => "Search",
    ];

    $defParams = [];
    switch ($params['type'] ?? "") {
      case 'save':
        $defParams['fa_icon'] = 'fas fa-download';
        $defParams['text'] = $this->translate("Uložiť");
        $defParams['class'] = "btn-success btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_save()";
        unset($params['class']);
      break;
      case 'search':
        $defParams['fa_icon'] = 'fas fa-search';
        $defParams['text'] = $this->translate("Hľadať");
        $defParams['class'] = "btn-light btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_search()";
        unset($params['class']);
      break;
      case 'apply':
        $defParams['fa_icon'] = 'fas fa-check';
        $defParams['text'] = $this->translate("Použiť");
        $defParams['class'] = "btn-success btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_apply()";
        unset($params['class']);
      break;
      case 'close':
        $defParams['fa_icon'] = 'fas fa-times';
        $defParams['class'] = "btn-light btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Zavrieť");
        $defParams['onclick'] = "{$this->adios->uid}_close()";
        unset($params['class']);
      break;
      case 'copy':
        $defParams['fa_icon'] = 'fas fa-copy';
        $defParams['class'] = "btn-secondary btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Kopírovať");
        $defParams['onclick'] = "{$this->adios->uid}_copy()";
        unset($params['class']);
      break;
      case 'add':
        $defParams['fa_icon'] = 'fas fa-plus';
        $defParams['text'] = $this->translate("Pridať");
        $defParams['onclick'] = "{$this->adios->uid}_add()";
        unset($params['class']);
      break;
      case 'delete':
        $defParams['fa_icon'] = 'fas fa-trash-alt';
        $defParams['class'] = "btn-danger btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Zmazať");
        $defParams['onclick'] = "{$this->adios->uid}_delete()";
        unset($params['class']);
      break;
      case 'cancel':
        $defParams['fa_icon'] = 'app/x-mark-3.png';
        $defParams['text'] = $this->translate("Zrušiť");
        $defParams['onclick'] = "{$this->adios->uid}_cancel()";
        unset($params['class']);
      break;
      case 'confirm':
        $defParams['fa_icon'] = 'app/ok.png';
        $defParams['text'] = $this->translate("Potvrdiť");
        $defParams['onclick'] = "{$this->adios->uid}_confirm()";
        unset($params['class']);
      break;
    }

    $this->params = array_merge($defParams, $params);

    parent::__construct($adios, $this->params);
  }

  public function render($render_panel = '') {
    $params = $this->params;

    // icon
    if ($params['fa_icon'] != '') {
      $icon = "<i class='{$params['fa_icon']} {$this->params['color']}'></i>";
    }

    if (_count($params['dropdown'])) {
      $dropdowns_html = "";
      foreach ($params['dropdown'] as $key => $dropdown) {
        if ($dropdown['fa_icon'] != '') {
          $tmp_icon = "<i class='{$dropdown['fa_icon']} mr-2'></i>";
        } else {
          $tmp_icon = "";
        }

        $dropdowns_html .= "
          <a
            class='dropdown-item'
            href='javascript:void(0)'
            onclick=\"{$dropdown['onclick']}\"
          >
            {$tmp_icon}
            ".hsc($dropdown['text'])."
          </a>
        ";
      }

      return "
        <span
          class='".($params['fa_icon'] == "" ? "" : "no-arrow")." dropdown'
          title='".ads($params['title'])."'
        >
          <a
            href='javascript:void(0);'
            role='button'
            class='
              btn
              dropdown-toggle
              ".($params['fa_icon'] == "" ? "" : "btn-icon-split")."
              ".($params['class'] == "" ? "btn-primary" : $params['class'])."
            '
            id='{$this->uid}_dropdown_menu_button'
            style='{$params['style']}'
            data-toggle='dropdown'
            aria-haspopup='true'
            aria-expanded='false'
            {$params['html_attributes']}
          >
            <span class='icon'>
              <i class='{$params['fa_icon']}'></i>
            </span>
            ".(empty($params['text']) && empty($params['textRaw'])
              ? ""
              : "<span class='text'>".(empty($params['textRaw']) ? hsc($params['text']) : $params['textRaw'])."</span>"
            )."
          </a>
          <div class='dropdown-menu' aria-labelledby='{$this->uid}_dropdown_menu_button'>
            {$dropdowns_html}
          </div>
        </span>
      ";
    } else {
      return "
        <a
          href='".(empty($params['href']) ? "javascript:void(0);" : $params['href'])."'
          ".(empty($params['id']) ? "" : "id='{$params['id']}'")."
          class='
            btn
            ".($params['class'] == "" ? "btn-primary btn-icon-split" : $params['class'])."
          '
          style='{$params['style']}'
          ".(empty($params['onclick'])
            ? ""
            : "
            onclick=\"
              let _this = $(this);
              _this.css('opacity', 0.5);
              setTimeout(function() {
              _this.css('opacity', 1);
              }, 300);

              ".($this->params['cancel_bubble'] ? 'event.cancelBubble = true;' : '')."
              {$params['onclick']}
            \"
            "
          )."
          ".($params['disabled'] ? "disabled='disabled'" : '')."
          title='".ads($params['title'])."'
          {$params['html_attributes']}
        >
          <span class='icon'>
            <i class='{$params['fa_icon']}'></i>
          </span>
          ".(empty($params['text']) && empty($params['textRaw'])
            ? ""
            : "<span class='text'>".(empty($params['textRaw']) ? hsc($params['text']) : $params['textRaw'])."</span>"
          )."
        </a>
      ";
    }
  }
}
