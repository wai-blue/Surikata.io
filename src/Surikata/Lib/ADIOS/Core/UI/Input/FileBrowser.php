<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core\UI\Input;

class FileBrowser extends \ADIOS\Core\Input {
  const MODE_SELECT = "select";
  const MODE_BROWSE = "browse";

  var $folderTreeHtmlItems = [];

  public function renderFolderTree($rootDir, $subDir = "", $level = 0) {
    $rootDir = str_replace("\\", "/", $rootDir);
    $subDir = str_replace("\\", "/", $subDir);

    $subDir = rtrim($subDir, "/");

    $dir = rtrim("{$rootDir}/{$subDir}", "/");

    if (!is_dir($dir)) return "";

    $tmp = explode("/", $subDir);
    array_pop($tmp);
    // $levelUpSubDir = implode("/", $tmp);

    // $title = end(explode("/", $subDir));

    $html = "{$this->uid}_dirTree['".ads($subDir)."'] = [";

    foreach (scandir($dir) as $file) {
      if (in_array($file, [".", ".."])) continue;
      if ($level == 0 && $file == "___cache") continue;

      if (is_dir("{$dir}/{$file}")) {
        $html .= "['D','".ads($file)."'],\n";
        $this->renderFolderTree($rootDir, trim("{$subDir}/{$file}", "/"), $level + 1);

      }
    }
    foreach (scandir($dir) as $file) {
      if (in_array($file, [".", ".."])) continue;

      if (!is_dir("{$dir}/{$file}")) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $faIcon = "";

        if (in_array($ext, ["bmp", "gif", "tiff", "jpg", "jpeg", "webp", "png"])) {
          $faIcon = "image";
        } else if (in_array($ext, ["doc", "docx", "odt"])) {
          $faIcon = "word";
        } else if (in_array($ext, ["mp3", "wav"])) {
          $faIcon = "audio";
        } else if (in_array($ext, ["zip", "rar", "tgz", "gz"])) {
          $faIcon = "archive";
        } else if (in_array($ext, ["pdf"])) {
          $faIcon = "pdf";
        }

        $html .= "['F','".ads($file)."','".ads($faIcon)."','".round(filesize("{$dir}/{$file}"))."'],\n";
      }
    }

    $html = trim($html, ",")."];";

    $this->folderTreeHtmlItems[] = $html;
  }

  public function render() {
    // $this->languageDictionary = $this->adios->loadLanguageDictionary($this);

    // $params = $this->params;
    $mode = $this->params["mode"] ?? self::MODE_BROWSE;

    $rootDir = $this->adios->config['files_dir'].(empty($this->params['subdir']) ? "" : "/{$this->params['subdir']}");
    $rootUrl = $this->adios->config['files_url'].(empty($this->params['subdir']) ? "" : "/{$this->params['subdir']}");

    $this->renderFolderTree($rootDir);

    if ($mode == self::MODE_SELECT) {
      $btnFileOnclick = "
        $('#{$this->uid}_filebrowser_wrapper .btn-success').addClass('btn-light').removeClass('btn-success');
        $(this).removeClass('btn-light').addClass('btn-success');
        $('#{$this->uid}').val('".(empty($this->params['subdir']) ? "" : "{$this->params['subdir']}/")."{{ subDir }}/{{ file }}').trigger('change');
      ";
    }

    $html = "
      <input
        id='{$this->uid}'
        disabled
        style='display:none;width:100%;margin-bottom:1em'
        value='".ads($this->params['value'])."'
        placeholder='Select file...'
        onchange=\"
          let file = $(this).val();
          {$this->params['onchange']}
        \"
      />

      <script>
        var {$this->uid}_dirTree = {};
        ".join("\n", array_reverse($this->folderTreeHtmlItems))."
      </script>

      <div id='{$this->uid}_filebrowser_wrapper' class='adios ui FileBrowser'>
        <div class='folders'>
        </div>

        <div
          class='template adios ui FileBrowser folder card shadow mr-1' id='{$this->uid}_dir_{{ dirId }}'
          data-level='{{ level }}' data-dir='{{ dirId }}'
        >
        
          <form enctype='multipart/form-data' style='display:none'>
            <input
              type='file'
              style='visibility:hidden;'
              name='{$this->uid}_{{ dirId }}_file_input'
              id='{$this->uid}_{{ dirId }}_file_input'
              onchange=\"
                let fileInput = $('#{$this->uid}_{{ dirId }}_file_input');
                let folderPath = {$this->uid}_getCurrentFolderPath();
                let fileUploaderUrl = _APP_URL + '/UI/FileBrowser/Upload?folderPath=' + encodeURIComponent(folderPath);
                let formData = new FormData();
                
                formData.append('upload', fileInput[0].files[0]);

                if (fileInput.val() != '') {
                  $.ajax({
                    url: fileUploaderUrl,
                    type: 'post',
                    data: formData,
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false
                  }).done(function(data) {
                    fileInput.val('');

                    let res = jQuery.parseJSON(data);

                    if (res.uploaded == 1) {
                      let btnFileTemplate = $('#{$this->uid}_filebrowser_wrapper').find('.btn-file.template').get(0).innerHTML;
                      let btnHtml = {$this->uid}_addButton(
                        res.fileName,
                        btnFileTemplate,
                        '{{ subDir }}',
                        res.fileName,
                        {{ level }} + 1,
                        'file',
                        res.fileName,
                        Math.round(res.fileSize/1024/1024 * 100)/100
                      );

                      $('#{$this->uid}_dir_{{ dirId }}')
                        .find('.card-body .file-list')
                        .append(btnHtml)
                      ;
                    } else {
                      alert(res.error);
                    };
                  });
                }
              \"
            />
          </form>

          <div class='card-header py-3 d-flex flex-row align-items-center justify-content-between'>
            <h6 class='m-0 font-weight-bold text-primary dir-name'>{{ title }}</h6>
          </div>

          <div class='card-body'>
            <div class='file-list mb-4'>
              <div class='pb-2'>Loading ...</div>
            </div>

            <div class='operation-buttons'>
              ".$this->adios->ui->Button([
                "fa_icon" => "fas fa-folder-plus",
                "class" => "btn-new btn btn-sm btn-light btn-icon-split",
                "text" => $this->translate("Create folder"),
                "onclick" => "
                  let folderName = prompt('The name of the new folder');
                  if (folderName) {
                    {$this->uid}_showDir(
                      '{{ subDir }}',
                      '{{ title }}',
                      {{ level }}
                    );

                    let folderPath = {$this->uid}_getCurrentFolderPath();

                    _ajax_read(
                      'UI/FileBrowser/CreateFolder',
                      {
                        'folder': folderPath + '/' + folderName,
                      },
                      function(res) {
                        if (isNaN(res)) {
                          alert(res);
                        } else {
                          let btnFolderTemplate = $('#{$this->uid}_filebrowser_wrapper').find('.btn-folder.template').get(0).innerHTML;
                          let btnHtml = {$this->uid}_addButton(
                            folderName,
                            btnFolderTemplate,
                            '{{ subDir }}',
                            folderName,
                            {{ level }} + 1,
                            'folder',
                            folderName,
                            0
                          );

                          $('#{$this->uid}_dir_{{ dirId }}')
                            .find('.card-body .file-list')
                            .append(btnHtml)
                          ;
                        }
                      }
                    );
                  }
                ",
              ])->render()."

              ".$this->adios->ui->Button([
                "fa_icon" => "fas fa-file-upload",
                "class" => "btn-new btn btn-sm btn-light btn-icon-split",
                "text" => $this->translate("Upload file"),
                "onclick" => "$('#{$this->uid}_{{ dirId }}_file_input').click();",
              ])->render()."

              ".$this->adios->ui->Button([
                "fa_icon" => "fas fa-ellipsis-v",
                "class" => "btn-new btn btn-sm btn-light btn-icon-split btn-more-operations",
                "dropdown" => [
                  [
                    "fa_icon" => "fas fa-font",
                    "text" => "Rename folder",
                    "onclick" => "
                      let newFolderName = prompt('The name of the new folder');
                      if (newFolderName) {
                        {$this->uid}_showDir(
                          '{{ subDir }}',
                          '{{ title }}',
                          {{ level }}
                        );

                        let _this = $(this);
                        let folderPath = {$this->uid}_getCurrentFolderPath();

                        _ajax_read(
                          'UI/FileBrowser/RenameFolder',
                          {
                            'folder': folderPath,
                            'newFolderName': newFolderName,
                          },
                          function(res) {
                            if (isNaN(res)) {
                              alert(res);
                            } else {
                              _this.closest('.adios.ui.folder').remove();
                            }
                          }
                        );
                      }
                    ",
                  ],
                  [
                    "fa_icon" => "fas fa-trash",
                    "text" => "Delete folder",
                    "onclick" => "
                      if (confirm('Are you sure you want to delete the folder?')) {
                        {$this->uid}_showDir(
                          '{{ subDir }}',
                          '{{ title }}',
                          {{ level }}
                        );

                        let _this = $(this);

                        _ajax_read(
                          'UI/FileBrowser/DeleteFolder',
                          {
                            'folder': {$this->uid}_getCurrentFolderPath(),
                          },
                          function(res) {
                            if (isNaN(res)) {
                              alert(res);
                            } else {
                              _this.closest('.adios.ui.folder').remove();
                            }
                          }
                        );
                      }
                    ",
                  ],
                ],
              ])->render()."
            </div>
          </div>
        </div>

        <div class='template btn-folder-up'>
          ".$this->adios->ui->Button([
            "fa_icon" => "fas fa-reply",
            "text" => "..",
            "class" => "btn btn-folder btn-sm btn-light btn-icon-split mb-1",
            "title" => "Go up",
            "onclick" => "$(this).closest('.folder').remove();",
          ])->render()."
        </div>

        <div class='template btn-folder'>
          ".$this->adios->ui->Button([
            "uid" => "{{ btnUid }}",
            "fa_icon" => "{{ icon }}",
            "text" => "{{ text }}",
            "class" => "btn btn-folder btn-sm btn-primary btn-icon-split mb-1",
            "title" => "{{ file }}",
            "onclick" => "
              {$this->uid}_showDir(
                ('{{ subDir }}' == '' ? '{{ file }}' : '{{ subDir }}/{{ file }}'),
                $(this).find('.text').text(),
                {{ level }} + 1
              );
            ",
          ])->render()."
        </div>

        <div class='template btn-file'>
          ".$this->adios->ui->Button([
            "uid" => "{{ btnUid }}",
            "fa_icon" => "{{ icon }}",
            "class" => "btn btn-file btn-sm btn-light btn-icon-split mb-1",
            "textRaw" => "{{ text }} <div class='filesize'>{{ filesize }} MB</div>",
            "title" => "{{ file }}",
            "onclick" => $btnFileOnclick,
          ])->render()."
          
        </div>
      </div>

      <script>
        function {$this->uid}_getDirId(dir) {
          return dir.replaceAll('/', '-').replaceAll(' ', '-');
        }

        function {$this->uid}_showDir(dir, title, level) {
          let dirId = {$this->uid}_getDirId(dir);

          let folderTemplate = $('#{$this->uid}_filebrowser_wrapper .adios.ui.folder.template');
          let folderHtml =
            folderTemplate.get(0).outerHTML
            .replaceAll('{{ subDir }}', dir)
            .replaceAll('{{ dirId }}', dirId)
            .replaceAll('{{ title }}', title)
            .replaceAll('{{ level }}', level)
          ;
          let tree = {$this->uid}_dirTree[dir];

          let folders = $('#{$this->uid}_filebrowser_wrapper .folders');

          folders
            .find('.folder')
            .filter(function() {
              return $(this).data('level') >= level;
            })
            .remove()
          ;

          let folder = $(folderHtml)
            .removeClass('template')
            .show()
          ;

          if (level == 0) {
            folder.find('.btn-more-operations').hide();
          }

          folders.append(folder);

          let btnCnt = (tree ? tree.length : 0);
          let btnHtml = (level == 0 ? '' : $('#{$this->uid}_filebrowser_wrapper .btn-folder-up.template').get(0).innerHTML);
          let btnFolderTemplate = $('#{$this->uid}_filebrowser_wrapper .btn-folder.template').get(0).innerHTML;
          let btnFileTemplate = $('#{$this->uid}_filebrowser_wrapper .btn-file.template').get(0).innerHTML;

          if (typeof tree != 'undefined') {
            for (var i in tree) {
              if (tree[i][0] == 'D') {
                btnHtml += {$this->uid}_addButton(folder, btnFolderTemplate, dir, tree[i][1], level, 'folder', tree[i][1], 0);
              }
            }
            for (var i in tree) {
              if (tree[i][0] == 'F') {
                let text = tree[i][1];
                let icon = (tree[i][2] ? 'file-' + tree[i][2] : 'file');
                let filesize = Math.round(tree[i][3]/1024/1024 * 100)/100;

                if (icon == 'file-image' && btnCnt < 100) {
                  text =
                    '<img ' +
                      'src=\"{$rootUrl}/' + dir + '/' + tree[i][1] + '\" ' +
                      'onclick=\"window.open($(this).attr(\\'src\\')); event.cancelBubble = true;\" ' +
                    '/> '
                  ;
                }
                btnHtml += {$this->uid}_addButton(folder, btnFileTemplate, dir, tree[i][1], level, icon, text, filesize);
              }
            }
          }
          
          if (btnCnt < 100) {
            $(folder).find('.card-body .file-list').html(btnHtml);
          } else {
            setTimeout(function() {
              $(folder).find('.card-body .file-list').html(btnHtml);
            }, 300);
          }

          setTimeout(function() {
            $(folder).find('.btn-file .icon')
              .mouseover(function() {
                let el = $(this).find('i');
                el.data('classOrig', el.attr('class'));
                el.attr('class', 'fas fa-check');
              })
              .mouseout(function() {
                let el = $(this).find('i');
                el.attr('class', el.data('classOrig'));
              })
            ;
          }, 301);

          $('#{$this->uid}_filebrowser_wrapper .folder').filter(function() {
            return $(this).data('level') >= level;
          }).hide();
          $('#{$this->uid}_dir_' + dir.replaceAll('/', '-')).show();

        }

        function {$this->uid}_addButton(folder, btnTemplateHtml, subDir, file, level, icon, text, filesize) {
          let btnHtml =
            btnTemplateHtml
            .replaceAll('{{ btnUid }}', subDir.replaceAll('/', '-') + '-' + file.replaceAll('/', '-'))
            .replaceAll('{{ subDir }}', subDir)
            .replaceAll('{{ file }}', file)
            .replaceAll('{{ text }}', text)
            .replaceAll('{{ filesize }}', filesize)
            .replaceAll('{{ level }}', level)
            .replaceAll('{{ icon }}', 'fas fa-' + icon)
          ;

          return btnHtml;
        }

        function {$this->uid}_getCurrentFolderPath() {
          let wrapper = $('#{$this->uid}_filebrowser_wrapper');
          let folderPath = '';
          wrapper.find('.dir-name:visible').slice(1).each(function() {
            folderPath += (folderPath == '' ? '' : '/') + $(this).text();
          });
          return  '".(empty($this->params['subdir']) ? "" : "{$this->params['subdir']}/")."' + folderPath;
        }

        {$this->uid}_showDir('', '".$this->translate('Files And Media')."".(empty($this->params['subdir']) ? "" : "/".ads($this->params['subdir']))."', 0);
      </script>
    ";

    return $html;
  }
}
