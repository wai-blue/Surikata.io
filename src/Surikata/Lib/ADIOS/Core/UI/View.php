<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class View {
  
  var $useSession = FALSE;
  
  /**
   * languageDictionary
   *
   * @internal
   * @var array
   */
  // public $languageDictionary = [];
  
  /**
   * __construct
   *
   * @internal
   * @param  mixed $adios
   * @param  mixed $params
   * @return void
   */
  public function __construct(object $adios, array $params = []) {
    if (!isset($adios->viewsCounter)) {
      $adios->viewsCounter = 0;
    }
    ++$adios->viewsCounter;

    $this->adios = $adios;

    if ($params['lpfs']) {
      $params = $this->loadParamsFromSession($params['uid']);
    }

    if (empty($params['uid'])) {
      $params['uid'] =
        $this->adios->uid."_"
        .str_replace("\\", "", str_replace("ADIOS\\Core\\", "", get_class($this)))."_"
        .$adios->viewsCounter
      ;
    }

    if ($this->useSession) {
      $tmpParams = $params;
      unset($tmpParams["_REQUEST"]);
      unset($tmpParams["_COOKIE"]);
      unset($tmpParams["uid"]);
      $this->saveParamsToSession($params['uid'], $tmpParams);
    }

    $this->params = $params;
    $this->uid = $params['uid'];
    $this->views = [];
    $this->classes = ['adios', 'ui', $params['component_class']];

    $this->add_class($params['class']);
  }

  public function saveParamsToSession(string $uid = "", $params = NULL) {
    $_SESSION[_ADIOS_ID]['views'][$uid ?? $this->uid] = is_array($params) ? $params : $this->params;
  }
  
  public function loadParamsFromSession(string $uid = "") {
    $params = $_SESSION[_ADIOS_ID]['views'][$uid ?? $this->uid];
    $params["uid"] = $uid ?? $this->uid;
    return $params;
  }
  
  /**
   * translate
   *
   * @internal
   * @param  mixed $string
   * @param  mixed $context
   * @param  mixed $toLanguage
   * @return void
   */
  public function translate($string) {
    return $this->adios->translate($string, $this);
  }
  
  /**
   * @internal
   */
  public function add($subviews, $panel = 'default') {
      if (is_array($subviews)) {
          foreach ($subviews as $subview) {
              $this->add($subview, $panel);
          }
      } elseif (is_string($subviews) || '' == $subviews) {
          $this->views[$panel][] = $subviews;
      } else {
          $subviews->param('parent_uid', $this->uid);
          if ('' != $subviews->params['key']) {
              $this->views[$panel][$subviews->params['key']] = $subviews;
          } else {
              $this->views[$panel][] = $subviews;
          }
      }

      return $this;
  }
  
  /**
   * cadd
   *
   * @internal
   * @param  mixed $component_name
   * @param  mixed $params
   * @return void
   */
  public function cadd($component_name, $params = null) {
    $this->add($this->adios->ui->create($component_name, $params));

    return $this;
  }
  
  /**
   * param
   *
   * @internal
   * @param  mixed $param_name
   * @param  mixed $param_value
   * @return void
   */
  public function param($param_name, $param_value = null) {
    if (null === $param_value) {
      return $this->params[$param_name];
    } else {
      $this->params[$param_name] = $param_value;
    }

    return $this;
  }

  /**
   * Funkcia slúži na rekurzívny merge viacúrovňových polí.
   *
   * @version 1
   *
   * @internal
   * @param array $params Pole pôvodných parametrov
   * @param array $update Pole parametrov, ktoré aktualizujú a dopĺňajú $params
   *
   * @return array Zmergované výsledné pole
   */
  public function params_merge($params, $update) {
    if (!is_array($params)) {
      $params = [];
    }

    // ak je na danej urovni zapnuty disable merge, cele paramsy su nahradene updatom
    if (true === $update['disable_merge']) {
      unset($update['disable_merge']);
      $params = $update;
    } else {
      // ak je vypnuty merge parametrov v prvej urovni disable_merge pola, unsetnu sa params a samotny merge
      if (_count($update['disable_merge'])) {
        foreach ($update['disable_merge'] as $disable_key => $disable) {
          if (true === $disable) {
            unset($params[$disable_key]);
            unset($update['disable_merge'][$disable_key]);
          }
        }
        // ak v disable merge nezostali parametre, cele sa unsetne
        if (!_count($update['disable_merge'])) {
          unset($update['disable_merge']);
        }
      }

      if (_count($update)) {
        foreach ($update as $key => $val) {
          if (_count($val)) {
            if (_count($update['disable_merge'][$key])) {
              $val['disable_merge'] = $update['disable_merge'][$key];
              unset($update['disable_merge'][$key]);
            }
            if ('disable_merge' !== $key) {
              $params[$key] = $this->params_merge($params[$key], $val);
            }
          } else {
            $params[$key] = $val;
          }
        }
      }
    }

    return $params;
  }
  
  /**
   * html
   *
   * @internal
   * @param  mixed $new_html
   * @param  mixed $panel
   * @return void
   */
  public function html($new_html = null, $panel = 'default') {
    if (null === $new_html) {
      return $this->html[$panel];
    } else {
      $this->html[$panel] = $new_html;
    }

    return $this;
  }
  
  /**
   * on
   *
   * @internal
   * @param  mixed $event_name
   * @param  mixed $event_js
   * @return void
   */
  public function on($event_name, $event_js) {
    $this->params['on'][$event_name] .= $event_js;

    return $this;
  }
  
  /**
   * add_class
   *
   * @internal
   * @param  mixed $class_name
   * @param  mixed $target
   * @return void
   */
  public function add_class($class_name, $target = '') {
    if (empty($class_name)) return;

    if (!in_array($target, ['', 'desktop', 'mobile', 'tablet'])) {
      $target = '';
    }

    $add = false;
    //if (Akernel()->is_mobile && $target == "mobile") $add = TRUE;
    //else if (Akernel()->is_tablet && $target == "tablet") $add = TRUE;
    //else
    if ('desktop' == $target || '' == $target) {
      $add = true;
    }

    if ($add) {
      $classes = explode(' ', $class_name);
      foreach ($classes as $class_name) {
        $this->classes[] = $class_name;
        $this->classes = array_unique($this->classes);
      }
    }

    return $this;
  }
  
  /**
   * remove_class
   *
   * @internal
   * @param  mixed $class_name
   * @return void
   */
  public function remove_class($class_name) {
    $tmp_classes = [];
    foreach ($this->classes as $tmp_class) {
      if ($tmp_class != $class_name) {
        $tmp_classes[] = $tmp_class;
      }
    }
    $this->classes = $tmp_classes;

    return $this;
  }
  
  /**
   * render
   *
   * @internal
   * @param  mixed $panel
   * @return void
   */
  public function render(string $panel = '') {
    $html = '';

    if ('' != $this->html[$panel]) {
      $html = $this->html[$panel];
      if (_count($this->views[$panel])) {
        foreach ($this->views[$panel] as $view) {
          $html = str_replace("{%UI:{$view->uid}%}", $view->render(), $html);
        }
      }
    } else {
      if (_count($this->views[$panel])) {
        foreach ($this->views[$panel] as $view) {
          if (is_string($view) || '' == $view) {
            $html .= $view;
          } else {
            $html .= $view->render();
          }
        }
      }
    }

    return $html;
  }
  
  /**
   * main_params
   *
   * @internal
   * @return void
   */
  public function main_params() {
    // pre inputy, ktore su disabled sa nastavi tento parameter, aby sa nedostali do udajov selectovanych cez ui_form_get_values
    if ('m_ui_input' == get_class($this)) {
      if ($this->params['disabled']) {
        $adios_disabled_attribute = "adios-do-not-serialize='1'";
      }
    }

    return "
      id='{$this->params['uid']}'
      class='".join(' ', $this->classes)."'
      style='{$this->params['style']}'
      {$adios_disabled_attribute}
    ";
  }
  
  /**
   * attr
   *
   * @internal
   * @param  mixed $attr
   * @param  mixed $val
   * @return void
   */
  public function attr($attr, $val) {
    $this->attrs[$attr] = $val;
  }
}
