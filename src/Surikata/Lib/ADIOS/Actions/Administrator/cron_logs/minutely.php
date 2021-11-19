<?php
  $log_dir = "{$___ADIOSObject->config['log_dir']}/cron/minutely";
  
  echo $___ADIOSObject->ui->Title(array("center" => l("Systémové záznamy")))->render();
  
  echo "
    <div style='padding:5px'>
  ";
  
  if (is_dir($log_dir)) {
    $log_files = scandir($log_dir);
    $last_log_file = "";
    $last_log_file_ts = 0;
    
    foreach ($log_files as $file) {
      if ($file != "." && $file != "..") {
        $ts = filemtime("{$log_dir}/{$file}");
        if ($ts > $last_log_file_ts) {
          $last_log_file = $file;
          $last_log_file_ts = $ts;
        };
      };
    };
    
    $log = file("{$log_dir}/{$file}");
    
    echo "<xmp>";
    foreach ($log as $line) {
      echo $line;
    };
    echo "</xmp>";
    
  } else {
    echo "
      <br/>
      ".l("Žiadne systémové záznamy neboli nájdené.")."
    ";
  };
  
  echo "
    </div>
  ";
?>