<?php

/**
  * @package Basic Helper Functions
*/

/**
 * Shorthand for htmlspecialchars with ENT_QUOTES.
 */
function hsc($string) {
  return htmlspecialchars($string, ENT_QUOTES);
}

/**
 * Shorthand for addslashes
 */
function ads($string) {
  return addslashes($string);
}

/**
 * Switch debugging outputs on.
 *
 * @deprecated
 * @param  boolean $debugCorrectQueries If set to TRUE, also valid DB queries will be debugged.
 * @return void
 */
function _d($debugCorrectQueries = false) {
  global $___ADIOSObject;
  $___ADIOSObject->config['debug_enabled'] = TRUE;
  $___ADIOSObject->db->debugCorrectQueries = $debugCorrectQueries;
}

/**
 * Switch debugging outputs off.
 *
 * @deprecated
 * @return void
 */
function _nd() {
  global $___ADIOSObject;
  $___ADIOSObject->config['debug_enabled'] = FALSE;
}

/**
 * Echo debugging output.
 *
 * @deprecated
 * @return void
 */
function _d_echo($logger_class, $logger_name, $msg) {
  global $___ADIOSObject;

  if ($___ADIOSObject->config['debug_enabled']) {
    // if ($___ADIOSObject->config['disable_adios_console']) {
    //   echo "
    //     <div style='background:#CCFFFF'>
    //       <span style='color:red;font-size:10px;'>[$d_echo_id] $logger_name:</span>
    //       <xmp style='font-size:10px'>$msg</xmp>
    //     </div>
    //   ";
    // } else {
      $___ADIOSObject->console->info($msg);
    // }
  }
}

/**
 * Translate string
 *
 * @deprecated
 * @return void
 */
function l($string, $data = [], $params = []) {
  return $string;
}


/**
 * Format print_r() result into *xmp* element.
 *
 * @param  mixed $var Variable to print_r()
 * @param  mixed $only_return If set to TRUE, the result will be returned, not echoed.
 * @return void|string Formatted result.
 */
function _print_r($var, $only_return = false) {
  $str = "<xmp style='font-size:11px'>".print_r($var, true).'</xmp>';
  if ($only_return) {
    return $str;
  } else {
    echo $str;
  }
}

function _getmicrotime() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $usec + (float) $sec;
}

function _put_default_params_values($params, $default_values) {
  foreach ($default_values as $key => $value) {
    if (!isset($params[$key])) {
      $params[$key] = $value;
    } else {
      if (is_array($value)) {
        $params[$key] = _put_default_params_values($params[$key], $value);
      }
    }
  }

  return $params;
}

function _count($data) {
  return is_array($data) && count($data) > 0 ? 1 : 0;
}

