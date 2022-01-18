<?php

namespace ADIOS\Core\UI;

/**
 * Renders a button element. Used by many other UI elements.
 *
 * Example code to render button:
 *
 * ```php
 *   $adios->ui->Button([
 *     "type" => "close",
 *     "onclick" => "window_close('{$this->uid}');",
 *   ]);
 * ```
 *
 * @package UI\Elements
 */
class Button extends \ADIOS\Core\UI\View {
  
  /**
   * Type of the button. Determines default configuration.
   * Possible values: 'save', 'search', 'apply', 'close', 'copy', 'add', 'delete', 'cancel', 'confirm'.
   *
   * @var string
   */
  public $type = "";
  
  /**
   * DOM element's ID.
   *
   * @var string
   */
  // public $id = "";
  
  /**
   * If not empty, will be used as href attribute. Otherwise will href attribute be set to javascript:void(0).
   *
   * @var string
   */
  public $href = "";
  
  /**
   * FontAwesome icon in the form of a CSS class name. E.g. 'fas fa-home'.
   *
   * @var string
   */
  public $faIcon = "";
  
  /**
   * Text on the button, sanitized by htmlspecialchars().
   *
   * @var string
   */
  public $text = "";
  
  /**
   * Text on the button, not processed. If set, the text property is ignored.
   *
   * @var string
   */
  public $textRaw = "";
  
  /**
   * Additional CSS classes of the button.
   *
   * @var string
   */
  public $class = "";
  
  /**
   * onClick functionality used as an inline Javascript.
   *
   * @var string
   */
  public $onClick = "";
    
  /**
   * A <i>title</i> attribute of the button.
   *
   * @var string
   */
  public $title = "";  

  /**
   * CSS styling in the form of inline style.
   *
   * @var string
   */
  public $style = "";

  /**
   * If set to TRUE, the disabled attribute will be rendered.
   *
   * @var boolean
   */
  public $disabled = FALSE;

  /**
   * @internal
   */
  public function __construct(&$adios, $params = null) {
    $this->adios = $adios;

    // $this->languageDictionary = $this->adios->loadLanguageDictionary($this);

    $defParams = [];
    switch ($params['type'] ?? "") {
      case 'save':
        $defParams['fa_icon'] = 'fas fa-download';
        $defParams['text'] = $this->translate("Save");
        $defParams['class'] = "btn-success btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_save()";
        unset($params['class']);
      break;
      case 'search':
        $defParams['fa_icon'] = 'fas fa-search';
        $defParams['text'] = $this->translate("Search");
        $defParams['class'] = "btn-light btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_search()";
        unset($params['class']);
      break;
      case 'apply':
        $defParams['fa_icon'] = 'fas fa-check';
        $defParams['text'] = $this->translate("Apply");
        $defParams['class'] = "btn-success btn-icon-split {$params['class']}";
        $defParams['onclick'] = "{$this->adios->uid}_apply()";
        unset($params['class']);
      break;
      case 'close':
        $defParams['fa_icon'] = 'fas fa-times';
        $defParams['class'] = "btn-light btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Close");
        $defParams['onclick'] = "{$this->adios->uid}_close()";
        unset($params['class']);
      break;
      case 'copy':
        $defParams['fa_icon'] = 'fas fa-copy';
        $defParams['class'] = "btn-secondary btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Copy");
        $defParams['onclick'] = "{$this->adios->uid}_copy()";
        unset($params['class']);
      break;
      case 'add':
        $defParams['fa_icon'] = 'fas fa-plus';
        $defParams['text'] = $this->translate("Add");
        $defParams['onclick'] = "{$this->adios->uid}_add()";
        unset($params['class']);
      break;
      case 'delete':
        $defParams['fa_icon'] = 'fas fa-trash-alt';
        $defParams['class'] = "btn-danger btn-icon-split {$params['class']}";
        $defParams['text'] = $this->translate("Delete");
        $defParams['onclick'] = "{$this->adios->uid}_delete()";
        unset($params['class']);
      break;
      case 'cancel':
        $defParams['fa_icon'] = 'app/x-mark-3.png';
        $defParams['text'] = $this->translate("Cancel");
        $defParams['onclick'] = "{$this->adios->uid}_cancel()";
        unset($params['class']);
      break;
      case 'confirm':
        $defParams['fa_icon'] = 'app/ok.png';
        $defParams['text'] = $this->translate("Confirm");
        $defParams['onclick'] = "{$this->adios->uid}_confirm()";
        unset($params['class']);
      break;
    }

    $this->params = array_merge($defParams, $params);

    parent::__construct($adios, $this->params);

    // $this->id = $this->params['id'];
    $this->href = $this->params['href'];
    $this->faIcon = $this->params['fa_icon'];
    $this->text = $this->params['text'];
    $this->textRaw = $this->params['textRaw'];
    $this->title = $this->params['title'];
    $this->class = $this->params['class'];
    $this->onClick = $this->params['onclick'];
    $this->style = $this->params['style'];
    $this->disabled = $this->params['disabled'];

  }

  public function render($render_panel = '') {
    if (_count($this->params['dropdown'])) {
      $dropdowns_html = "";
      foreach ($this->params['dropdown'] as $dropdown) {
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
          class='".($this->faIcon == "" ? "" : "no-arrow")." dropdown'
          title='".ads($this->title)."'
        >
          <a
            href='javascript:void(0);'
            role='button'
            class='
              btn
              dropdown-toggle
              ".($this->faIcon == "" ? "" : "btn-icon-split")."
              ".($this->class == "" ? "btn-primary" : $this->class)."
            '
            id='{$this->uid}_dropdown_menu_button'
            style='{$this->style}'
            data-toggle='dropdown'
            aria-haspopup='true'
            aria-expanded='false'
            {$this->params['html_attributes']}
          >
            <span class='icon'>
              <i class='{$this->faIcon}'></i>
            </span>
            ".(empty($this->text) && empty($this->textRaw)
              ? ""
              : "<span class='text'>".(empty($this->textRaw) ? hsc($this->text) : $this->textRaw)."</span>"
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
          href='".($this->href ?? "javascript:void(0);")."'
          ".(empty($this->id) ? "" : "x-id='{$this->id}'")."
          id='".ads($this->uid)."'
          class='
            btn
            ".($this->class == "" ? "btn-primary btn-icon-split" : $this->class)."
          '
          style='{$this->style}'
          ".(empty($this->onClick)
            ? ""
            : "
            onclick=\"
              let _this = $(this);
              ".($this->params['cancel_bubble'] ? 'event.cancelBubble = true;' : '')."

              if (!_this.hasClass('disabled')) {
                {$this->onClick}
              }

              _this.addClass('disabled');
              setTimeout(function() {
                _this.removeClass('disabled');
              }, 300);

            \"
            "
          )."
          ".($this->disabled ? "disabled='disabled'" : '')."
          title='".ads($this->title)."'
          {$this->params['html_attributes']}
        >
          <span class='icon'>
            <i class='{$this->faIcon}'></i>
          </span>
          ".(empty($this->text) && empty($this->textRaw)
            ? ""
            : "<span class='text'>".(empty($this->textRaw) ? hsc($this->text) : $this->textRaw)."</span>"
          )."
        </a>
      ";
    }
  }
}
