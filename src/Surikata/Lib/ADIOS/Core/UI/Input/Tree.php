<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class Tree extends \ADIOS\Core\Input {
  public function itemDropdownButton($text, $hasSubItems) {
    return $this->adios->ui->Button([
      "fa_icon" => "fas fa-angle-".($hasSubItems ? "down" : "right"),
      "text" => $text,
      "class" => "item btn btn-sm btn-secondary btn-icon-split my-1",
      "dropdown" => [
        [
          "fa_icon" => "fas fa-pencil-alt",
          "text" => $this->translate("Edit"),
          "onclick" => "
            let li = $(this).closest('li');
            let btn = $(this).closest('.dropdown').find(' > .btn');

            window_render(
              '".$this->model->getFullUrlBase($this->params)."/' + li.data('id') + '/Edit',
              '',
              function(res) {
                _ajax_read('UI/Tree/GetItemText', { model: '{$this->model->name}', id: res.data.id }, function(res2) {
                  btn.find('.text').text(res2);
                });
              }
            );
          ",
        ],
        [
          "fa_icon" => "fas fa-level-up-alt",
          "text" => $this->translate("Move level up"),
          "onclick" => "
            var src = $(this).closest('li');
            var ul = src.closest('ul');
            var dst = ul.closest('li');
            var itemCnt = src.closest('ul').find('> li').length;
            src.insertBefore(dst);
            if (ul.find('li').length == 0) {
              ul.hide();
            }

            {$this->uid}_serialize();
          ",
        ],
        [
          "fa_icon" => "fas fa-level-down-alt",
          "text" => $this->translate("Move level down"),
          "onclick" => "
            var src = $(this).closest('li');
            var ul = src.next('li').find('> ul');
            var dst = ul.find('li').eq(0);

            src.insertBefore(dst);
            ul.show();

            {$this->uid}_serialize();
          ",
        ],
        [
          "fa_icon" => "fas fa-trash",
          "text" => $this->translate("Select for deletion"),
          "onclick" => "
            let li = $(this).closest('li');
            li.addClass('to-delete');
            li.find('li').addClass('to-delete');
            li.find('.btn').addClass('btn-danger');

            {$this->uid}_serialize();
          ",
        ],
        [
          "fa_icon" => "fas fa-trash-restore",
          "text" => $this->translate("Unselect from deletion"),
          "onclick" => "
            let li = $(this).closest('li');
            li.removeClass('to-delete');
            li.find('li').removeClass('to-delete');
            li.find('.btn').removeClass('btn-danger');

            {$this->uid}_serialize();
          ",
        ],
      ],
    ]);
  }

  public function renderTree(&$items, $parentColumn, $parent = 0) {
    $itemsHtml = "";

    foreach ($items as $item) {
      if ((int) $item[$parentColumn] == (int) $parent) {
        $subItemsCnt = 0;
        foreach ($items as $subItem) {
          if ((int) $subItem[$parentColumn] == (int) $item['id']) {
            $subItemsCnt++;
          }
        }

        $itemsHtml .= "
          <li class='sortable node' data-id='{$item['id']}'>
            ".$this->itemDropdownButton($this->enumValues[$item['id']], $subItemsCnt > 0)->render()."
            ".$this->renderTree($items, $parentColumn, $item['id'])."
          </li>
        ";
      }
    }

    $treeHtml = "
      <ul class='adios ui Tree'>
        {$itemsHtml}
        <li class='node' data-id='-1' style='display:none'>
          ".$this->itemDropdownButton("Pridať", FALSE)->render()."
        </li>
        <li>
          ".$this->adios->ui->button([
            "fa_icon" => "fas fa-plus",
            "text" => $this->translate("Add"),
            "class" => "item btn btn-sm btn-light btn-icon-split my-1",
            "onclick" => "
              let ul = $(this).closest('ul');
              let li = ul.find(' > li[data-id=-1]');

              window_render(
                '".$this->model->getFullUrlBase($this->params)."/{$parent}/Add',
                {},
                function(res) {
                  if (res.data.id > 0) {
                    _ajax_read('UI/Tree/GetItemText', { model: '{$this->model->name}', id: res.data.id }, function(res2) {
                      let clone = li.clone(true);
                      clone
                        .data('id', res.data.id)
                        .addClass('sortable')
                        .insertBefore(li)
                        .show()
                      ;
                      clone.find('.dropdown').find('.text').text(res2);
                      ul.show();

                      {$this->uid}_serialize();
                      {$this->uid}_make_sortable();
                    });
                  }
                }
              );
            ",
          ])->render()."
        </li>
      </ul>
    ";

    return $treeHtml;
  }

  public function render() {
    $params = $this->params;
    $this->model = $this->adios->getModel($params['model']);

    $params = $this->model->treeParams($params);

    // najdem stlpec pre rodica
    $parentColumn = "";
    $orderColumn = "";

    foreach ($this->model->columns() as $colName => $colDef) {
      if ($colDef["type"] == "lookup" && $colDef["model"] == $this->model->name) {
        $parentColumn = $colName;
        $orderColumn = $colDef["order_column"];
      }
    }

    // nacitam data
    $items = $this->model;
    if (!empty($orderColumn)) {
      $items = $items->orderBy($orderColumn);
    }
    if (!empty($params['where'])) {
      $items = $items->whereRaw($params['where']);
    }
    $items = $items->get()->toArray();

    $this->enumValues = $this->model->getEnumValues();

    $treeHtml = $this->renderTree($items, $parentColumn);

    // $html

    $html = "
      <style>
        .adios.ui.Tree {
          list-style: none;
        }

        .adios.ui.Tree li ul {
          margin-left: 1em;
          margin-bottom: 0em;
          // box-shadow: -4px 4px 4px -2px #EEEEEE;
          border-left: 2px dashed #DDDDDD;
          // border-bottom: 2px dashed #EEEEEE;
          padding-left: 1em;
        }

        .adios.ui.Tree li .item {
          cursor: pointer;
        }

        .adios.ui.Tree li .btn-secondary .icon {
          width: 2em;
        }

        .adios.ui.Tree li.to-delete .btn {
          opacity: 0.3;
        }

        .adios.ui.Tree li.to-delete ul {
          display: none;
        }

        .adios.ui.Tree li .handle:hover {
          background: #DDDDDD;
        }
      </style>

      <input type='hidden' id='{$this->uid}' />

      <div class='row mb-3'>
        <div id='{$this->uid}_wrapper'>
          {$treeHtml}
        </div>
      </div>

      <script>

        $('.adios.ui.Tree li .btn-secondary .icon').click(function() {
          $(this).closest('li.node').find(' > ul').toggle();
          
          let i = $(this).find('i');
          if (i.hasClass('fa-angle-down')) {
            i.removeClass('fa-angle-down');
            i.addClass('fa-angle-right');
          } else {
            i.addClass('fa-angle-down');
            i.removeClass('fa-angle-right');
          }

          return false;
        });

        function {$this->uid}_serialize() {
          let serialized = [];

          $('#{$this->uid}_wrapper').find('li.node').each(function() {
            serialized.push({
              id: $(this).data('id'),
              toDelete: ($(this).hasClass('to-delete') ? true : false),
              parent: $(this).closest('ul').closest('li').data('id') || 0,
            });
          });

          $('#{$this->uid}').val(JSON.stringify(serialized));

          return serialized;
        }

        function {$this->uid}_make_sortable() {
          $('#{$this->uid}_wrapper ul').sortable({
            // handle: '.btn .icon',
            delay: 100,
            opacity: 0.3,
            placeholder: 'sortable-placeholder',
            revert: 100,
            items : 'li.sortable',
            zIndex: 999999999,
            tolerance: 'pointer',
            start: function(event, ui) {
              ui.placeholder.height(ui.item.height());
            },
            stop: function(event, ui) {
              {$this->uid}_serialize();
            }
          });
        }

        {$this->uid}_serialize();
        {$this->uid}_make_sortable();
      </script>
    ";

    return $html;
  }
}
