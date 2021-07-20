<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

function hsc($string) {
  return htmlspecialchars($string, ENT_QUOTES);
}

function ads($string) {
  return addslashes($string);
}

function _d($debug_correct_queries = false) {
  global $___ADIOSObject;
  $___ADIOSObject->config['debug_enabled'] = TRUE;
  $___ADIOSObject->db->debug_correct_queries = $debug_correct_queries;
}

function _nd() {
  global $___ADIOSObject;
  $___ADIOSObject->config['debug_enabled'] = FALSE;
}

function _d_echo($logger_class, $logger_name, $msg) {
  global $___ADIOSObject;

  if ($___ADIOSObject->config['debug_enabled']) {
    if ($___ADIOSObject->config['disable_adios_console']) {
      echo "
        <div style='background:#CCFFFF'>
          <span style='color:red;font-size:10px;'>[$d_echo_id] $logger_name:</span>
          <xmp style='font-size:10px'>$msg</xmp>
        </div>
      ";
    } else {
      $___ADIOSObject->console->log("$logger_name [$d_echo_id]", $msg);
    }
  }
}

function l($string, $data = [], $params = []) {
    return $string;
    

}










function _print_r($var, $only_return = false) {
  $str = "<xmp style='font-size:11px'>".print_r($var, true).'</xmp>';
  if ($only_return) {
    return $str;
  } else {
    echo $str;
  }
}

// function _underscorize($string) {
//   return _rmdiacritic(strtr(_rmspecialchars($string), " \t", '__'));
// }

function _getmicrotime() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $usec + (float) $sec;
}

// global $_load_url_error;
// $_load_url_error = '';

// function _load_url($url, $post = [], $auth = []) {
//   global $_load_url_error;
//   $_load_url_error = '';
//   if (is_callable('curl_init')) {
//     $ch = curl_init();

//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POST, count($post));
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 5);
//     curl_setopt($ch, CURLOPT_BINARYTRANSFER, $binary);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//     curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024 * 1024 * 10);
//     curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
//     //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

//     if (isset($auth['user']) && isset($auth['password'])) {
//         curl_setopt($ch, CURLOPT_HTTPAUTH, ALL);
//         curl_setopt($ch, CURLOPT_USERPWD, "{$auth['user']}:{$auth['password']}");
//     }

//     $html = curl_exec($ch);
//     $error = curl_error($ch);
//     $_load_url_error = $error;
//     curl_close($ch);
//   } else {
//     $error = 'CURL is not available';
//   }

//   return '' == $error ? $html : false;
// }

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

// function _real_escape_string($str) {
//   global $___ADIOSObject;
//   return $___ADIOSObject->db->escape($str);
// }

function _count($data) {
  return is_array($data) && count($data) > 0 ? 1 : 0;
}

// function get_table_filter_where($params)
// {
//     $type = $params['col_type'];
//     $s = explode(',', $params['filter']);
//     if (('int' == $type && _count($params['col_def']['enum_values'])) || 'varchar' == $type || 'text' == $type || 'color' == $type || 'file' == $type || 'image' == $type || 'enum' == $type || 'password' == $type || 'lookup' == $type) {
//         $w = explode(' ', $params['filter']);
//     } else {
//         $w = $params['filter'];
//     }

//     if ('' != trim($params['col_def']['sql']) && 'lookup' != $type) {
//         if (!('int' == $type && _count($params['col_def']['enum_values']))) {
//             $params['col_name'] = '('.$params['col_def']['sql'].')';
//         }
//     }

//     $return = 'false';

//     // trochu komplikovanejsia kontrola, ale znamena, ze vyhladavany retazec sa pouzije len ak uz nie je delitelny podla ciarok, alebo medzier
//     // pripadne tato kontrola eplati ak je na zaciatku =

//     if (
//         '=' == $params['filter'][0]
//       || (is_array($s) && 1 == count($s) && is_array($w) && 1 == count($w))
//       || (is_array($s) && 1 == count($s) && !is_array($w) && '' != $w)
//     ) {
//         $s = reset($s);

//         if ('=' == $params['filter'][0]) {
//             $s = substr($params['filter'], 1);
//         }

//         if ('!=' == substr($s, 0, 2)) {
//             $not = true;
//             $s = substr($s, 2);
//         }

//         // queryies pre typy

//         if ('bool' == $type) {
//             if ('Y' == $s) {
//                 $return = "{$params['col_name']} = '"._real_escape_string(trim($s))."' ";
//             } else {
//                 $return = "({$params['col_name']} != 'Y' OR {$params['col_name']} is null) ";
//             }
//         }

//         if ('boolean' == $type) {
//             if ('0' == $s) {
//                 $return = "({$params['col_name']} = '"._real_escape_string(trim($s))."'  or {$params['col_name']} is null) ";
//             } else {
//                 $return = "{$params['col_name']} != '0'";
//             }
//         }

//         if (('int' == $type && _count($params['col_def']['enum_values'])) || 'varchar' == $type || 'text' == $type || 'color' == $type || 'file' == $type || 'image' == $type || 'enum' == $type || 'password' == $type) {
//             $return = " {$params['col_name']} like '%"._real_escape_string(trim($s))."%'";
//         }

//         if ('lookup' == $type) {
//             $return = " {$params['col_name']}_lookup_sql_value like '%"._real_escape_string(trim($s))."%'";
//         }

//         if ('float' == $type || ('int' == $type && !_count($params['col_def']['enum_values']))) {
//             $s = trim(str_replace(',', '.', $s));
//             $s = str_replace(' ', '', $s);

//             if (is_numeric($s)) {
//                 $return = "({$params['col_name']}=$s)";
//             } elseif ('-' != $s[0] && strpos($s, '-')) {
//                 list($from, $to) = explode('-', $s);
//                 $return = "({$params['col_name']}>=".(trim($from) + 0)." and {$params['col_name']}<=".(trim($to) + 0).')';
//             } elseif (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? trim($m[1]) : '=');
//                 $operand = trim($m[2]) + 0;
//                 $return = "{$params['col_name']} {$operator} {$operand}";
//             } else {
//                 $return = 'FALSE';
//             }
//         }

//         if ('date' == $type) {
//             $s = str_replace(' ', '', $s);
//             $s = str_replace(',', '.', $s);

//             $return = 'false';

//             // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
//             if ($s === '-') {
//                 $return = "({$params['col_name']} IS NULL OR {$params['col_name']} = '0000-00-00' OR {$params['col_name']} = '')";
//             }

//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 if (strtotime($m[2]) > 0) {
//                     $to = date('Y-m-d', strtotime($m[2]));
//                     $return = "{$params['col_name']} {$operator} '{$to}'";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([\>\<=\!]{1,2})([0-9\.\-]+)([\>\<=\!]{1,2})([0-9\.\-]+)$/', $s, $m)) {
//                 $operator_1 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 $date_1 = date('Y-m-d', strtotime($m[2]));
//                 $operator_2 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[3] : '=');
//                 $date_2 = date('Y-m-d', strtotime($m[4]));
//                 if (strtotime($m[2]) > 0 && strtotime($m[4]) > 0) {
//                     $return = "({$params['col_name']} {$operator_1} '{$date_1}') and ({$params['col_name']} {$operator_2} '{$date_2}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9\.\-]+)-([0-9\.\-]+)$/', $s, $m)) {
//                 $date_1 = date('Y-m-d', strtotime($m[1]));
//                 $date_2 = date('Y-m-d', strtotime($m[2]));
//                 if (strtotime($m[1]) > 0 && strtotime($m[2]) > 0) {
//                     $return = "({$params['col_name']} >= '{$date_1}') and ({$params['col_name']} <= '{$date_2}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9]+)\.([0-9]+)$/', $s, $m)) {
//                 $month = $m[1];
//                 $year = $m[2];
//                 $return = "(month({$params['col_name']}) = '{$month}') and (year({$params['col_name']}) = '{$year}')";
//             }
//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 $year = $m[2];
//                 $return = "(year({$params['col_name']}) {$operator} '{$year}')";
//             }
//         }

//         if ('datetime' == $type || 'timestamp' == $type) {
//             $s = str_replace(' ', '', $s);
//             $s = str_replace(',', '.', $s);

//             $return = 'false';

//             // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
//             if ($s === '-') {
//                 $return = "({$params['col_name']} IS NULL OR {$params['col_name']} = '0000-00-00 00:00:00' OR {$params['col_name']} = '')";
//             }

//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\-]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 if (strtotime($m[2]) > 0) {
//                     $to = date('Y-m-d', strtotime($m[2]));
//                     $return = "date({$params['col_name']}) {$operator} '{$to}'";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([\>\<=\!]{1,2})([0-9\.\-]+)([\>\<=\!]{1,2})([0-9\.\-]+)$/', $s, $m)) {
//                 $operator_1 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 $date_1 = date('Y-m-d', strtotime($m[2]));
//                 $operator_2 = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[3] : '=');
//                 $date_2 = date('Y-m-d', strtotime($m[4]));
//                 if (strtotime($m[2]) > 0 && strtotime($m[4]) > 0) {
//                     $return = "(date({$params['col_name']}) {$operator_1} '{$date_1}') and (date({$params['col_name']}) {$operator_2} '{$date_2}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9\.\-]+)-([0-9\.\-]+)$/', $s, $m)) {
//                 $date_1 = date('Y-m-d', strtotime($m[1]));
//                 $date_2 = date('Y-m-d', strtotime($m[2]));
//                 if (strtotime($m[1]) > 0 && strtotime($m[2]) > 0) {
//                     $return = "(date({$params['col_name']}) >= '{$date_1}') and (date({$params['col_name']}) <= '{$date_2}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9]+)\.([0-9]+)$/', $s, $m)) {
//                 $month = $m[1];
//                 $year = $m[2];
//                 $return = "(month({$params['col_name']}) = '{$month}') and (year({$params['col_name']}) = '{$year}')";
//             }
//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 $year = $m[2];
//                 $return = "(year({$params['col_name']}) {$operator} '{$year}')";
//             }
//         }

//         if ('time' == $type) {
//             $return = 'false';
//             $s = str_replace(' ', '', $s);

//             // ak je do filtru zadany znak '-', vyfiltruje nezadane datumy
//             if ($s === '-') {
//                 $return = "({$params['col_name']} IS NULL OR {$params['col_name']} = '00:00:00' OR {$params['col_name']} = '')";
//             }

//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9\.\:]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 if (strtotime('01.01.2000 '.$m[2]) > 0) {
//                     $to = date('H:i:s', strtotime('01.01.2000 '.$m[2]));
//                     $return = "{$params['col_name']} {$operator} '{$to}'";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9\:]+)-([0-9\:]+)$/', $s, $m)) {
//                 $date_1 = date('H:i:s', strtotime('01.01.2000 '.$m[1]));
//                 $date_2 = date('H:i:s', strtotime('01.01.2000 '.$m[2]));
//                 if (strtotime('01.01.2000 '.$m[1]) > 0 && strtotime('01.01.2000 '.$m[2]) > 0) {
//                     $return = "({$params['col_name']} >= '{$date_1}') and ({$params['col_name']} <= '{$date_2}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9]+)$/', $s, $m)) {
//                 $hour = $m[1];
//                 $return = "(hour({$params['col_name']}) = '{$hour}')";
//             }
//         }

//         if ('year' == $type) {
//             $return = 'false';

//             if (preg_match('/^([\>\<=\!]{1,2})?([0-9]+)$/', $s, $m)) {
//                 $operator = (in_array($m[1], ['=', '!=', '<>', '>', '<', '>=', '<=']) ? $m[1] : '=');
//                 if (is_numeric($m[2])) {
//                     $return = "{$params['col_name']} {$operator} '$m[2]'";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9\:]+)-([0-9\:]+)$/', $s, $m)) {
//                 if (is_numeric($m[1]) && is_numeric($m[2])) {
//                     $return = "({$params['col_name']} >= '{$m[1]}') and ({$params['col_name']} <= '{$m[2]}')";
//                 } else {
//                   //
//                 }
//             }
//             if (preg_match('/^([0-9]+)$/', $s, $m)) {
//                 $return = "({$params['col_name']} = '{$m[1]}')";
//             }
//         }

//         if ($not) {
//             $return = " not( {$return} ) ";
//         }
//     } elseif (is_array($s) && count($s) > 1) {
//         foreach ($s as $val) {
//             $tmp = $params;
//             $tmp['filter'] = trim($val);
//             $wheres[] = get_table_filter_where($tmp);
//         }
//         $return = implode(' or ', $wheres);
//     } elseif (is_array($w) && count($w) > 1) {
//         foreach ($w as $val) {
//             $tmp = $params;
//             $tmp['filter'] = trim($val);
//             $wheres[] = get_table_filter_where($tmp);
//         }
//         $return = implode(' and ', $wheres);
//     }

//     return $return;
// }

// function _readable_byte_size($size) {
//   if ($size < 1024) {
//     return round($size).' bytes';
//   } elseif ($size < (1024 * 1024)) {
//     $size = round($size / 1024, 2);

//     return number_format($size, 2, ',', ' ').' KB';
//   } elseif ($size < (1024 * 1024 * 1024)) {
//     $size = round($size / (1024 * 1024), 2);

//     return number_format($size, 2, ',', ' ').' MB';
//   } else {
//     $size = round($size / (1024 * 1024 * 1024), 2);

//     return number_format($size, 2, ',', ' ').' GB';
//   }
// }

// function _float2time($value) {
//   $hod = floor($value);
//   $min = round($value * 60 - $hod * 60);
//   if ($hod < 10) {
//     $hod = "0{$hod}";
//   }
//   if ($min < 10) {
//     $min = "0{$min}";
//   }

//   return [$hod, $min];
// }

// function _is_wai_devel_server()
// {
//     if ('devel.wai.sk' == substr($_SERVER['HTTP_HOST'], 0, 12)) {
//         return true;
//     } else {
//         return false;
//     }
// }

// function _is_localhost_server()
// {
//     if ('127.0.0.1' == $_SERVER['HTTP_HOST']) {
//         return true;
//     } else {
//         return false;
//     }
// }

// function _ajax_load_return($result, $return_json = false)
// {
//     $res = json_encode([
//   'result' => $result,
//   'error' => false,
//   'error_code' => 0,
//   'error_message' => '',
// ]);
//     if ($return_json) {
//         return $res;
//     } else {
//         echo $res;
//     }
// }

// function _ajax_load_error($err_string, $error_code = 0, $return_json = false)
// {
//     $res = json_encode([
//       'result' => null,
//       'error' => true,
//       'error_code' => $error_code,
//       'error_message' => $err_string,
//     ]);

//     if ($return_json) {
//         return $res;
//     } else {
//         echo $res;
//     }
// }

// function _is_alphanumeric($string)
// {
//     return !preg_match('/[^A-Za-z0-9_]/', $string);
// }


// function _minify_html($html) {
//   // minifikacia vystupneho HTML
//   $search = array(
//     '/\>[^\S ]+/s',   // strip whitespaces after tags, except space
//     '/[^\S ]+\</s',   // strip whitespaces before tags, except space
//     '/(\s)+/s',     // shorten multiple whitespace sequences
//     '/<!--(.|\s)*?-->/' // Remove HTML comments
//   );

//   $replace = array(
//     '>',
//     '<',
//     '\\1',
//     ''
//   );

//   return preg_replace($search, $replace, $html);
// }




