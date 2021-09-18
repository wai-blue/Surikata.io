<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI;

class Tabs extends \ADIOS\Core\UI\View {
    public function __construct(&$adios, $params = null)
    {
        parent::__construct($adios, $params);

        if (_count($this->params['tabs'])) {
          foreach ($this->params['tabs'] as $key => $val) {
            if ('' == $val['key']) {
              $val['key'] = $key;
            }
            $this->add($val['content'], 'tab_'.$key);
            $this->add($val['title'], 'title_'.$key);
          }

          $found = false;
          foreach ($this->params['tabs'] as $tab_name => $tab_details) {
            if ($tab_name == $this->params['active_tab']) {
              $found = true;
            }
          }
          if (!$found) {
            $this->params['active_tab'] = reset(array_keys($this->params['tabs']));
          }
        }
    }

    public function render(string $panel = '') {

      $contents = "";
      $titles = "";

      if (_count($this->params['tabs'])) {
        $i = 0;
        
        foreach ($this->params['tabs'] as $key => $val) {
          $contents .= "
            <div
              class='shadow-sm tab_content tab_content_{$key} {$val['content_class']} ".($key == 0 ? "active" : "")."'
              style='{$val['content_style']}'
              onclick=\"
                $(this).closest('.tab_contents').find('.tab_content').removeClass('active');
                $(this).addClass('active');
              \"
            >
              <div class='tab_title_tag' onclick='$(this).next(\"div\").slideToggle(180);'>
                ".hsc($val['title'])."
              </div>
              <div class='px-2'>
                ".parent::render('tab_'.$key)."
              </div>
            </div>
          ";

          $titles .= "
            <li class='nav-item'>
              <a
                class='nav-link tab_title tab_title_{$key} ".($i == 0 ? "active" : "")."'
                href='javascript:void(0);'
                onclick=\"
                  ui_tabs_change_tab('{$this->uid}', '{$key}');
                  {$val['onclick']} 
                \"
              >
                ".parent::render('title_'.$key)."
              </a>
            </li>
          ";

          $i++;
        }

        $html = "
          <div ".$this->main_params().">
            <ul class='nav nav-tabs'>
              {$titles}
            </ul>
            <div
              class='tab_contents'
              style='height:".($this->params['height'] ?? "100px").";'
              onscroll=\"
                let st = $(this).scrollTop();
                let tab = 0;

                $(this).find('.tab_content').each(function() {
                  if ($(this).position().top < 200) {
                    tab++;
                  }
                });

                $('#{$this->uid} .tab_title').removeClass('active');
                $('#{$this->uid} .tab_title_' + (tab - 1)).addClass('active');
              \"
            >
              {$contents}
            </div>
          </div>
        ";
      }

      return $html;
    }
}
