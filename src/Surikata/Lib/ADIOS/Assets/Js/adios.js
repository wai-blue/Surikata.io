var Adios = {
  render: function(action, params) {
    desktop_update(action, params);
  }
}

function ltrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}

function trim(str, chars) {
	return ltrim(rtrim(str, chars), chars);
}

function rtrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

function parseStr(s) {
  var rv = {}, decode = window.decodeURIComponent || window.unescape;
  (s == null ? location.search : s).replace(
    /([^=&]*?)((?:\[\])?)(?:=([^&]*))?(?=&|$)/g,
    function ($, n, arr, v) {
      if (n == "")
        return;
      n = decode(n);
      v = decode(v);
      if (arr) {
        if (typeof rv[n] == "object")
          rv[n].push(v);
        else
          rv[n] = [v];
      } else {
        rv[n] = v;
      }
    });
  return rv;
}

function go(id) {
  if (document.getElementById)
    return document.getElementById(id);
  else
    return document.all[id]
};

function goval(id) {
  return go(id).value;
};

function draggable_int_input(id, params){

  if (!(params.sensitivity > 0)) params.sensitivity = 4;

  $('#'+id).on('mousedown', function(eventstart) {
    drag_int_input_start_position = eventstart.pageX
    drat_int_input_val = $(this).val();
    $(document).on('mousemove', function(event) {
      draggable_int_input_new_val = Math.floor(drat_int_input_val)-Math.floor((drag_int_input_start_position-event.pageX)/params.sensitivity);
      if (params.max_val.toString() != '') if (draggable_int_input_new_val > params.max_val) draggable_int_input_new_val = params.max_val;
      if (params.min_val.toString() != '') if (draggable_int_input_new_val < params.min_val) draggable_int_input_new_val = params.min_val;
      $('#'+id).val(draggable_int_input_new_val);
    });

      $('#'+id).on('mouseup', function() {
        $(document).off('mousemove');
        $('#'+id).off('mouseup');
      });

      $(document).on('mouseup', function() {
        $(document).off('mousemove');
        $(document).off('mouseup');
        $('#'+id).off('mouseup');
        if (typeof params.callback == 'function') params.callback(id);
      });

  });




};

function rmdiacritic(s){
  var translate_re = /[??????????????????aaa????????????AAA??cc????CC???????????????eeeee???????EEEEE???gGi??????????iii???????????IIIlLnn??NN????????????ooo????????????OOO????r??R??s?????S?????????uuuu????????UUUU??????????zz??ZZ]/g;
  var translate = {
"??":"1","??":"2","??":"3","??":"a","??":"a","??":"a","??":"a","??":"a","??":"a","a":"a","a":"a","a":"a","??":"a","??":"a","??":"a","??":"a","??":"a","??":"a","A":"a","A":"a",
"A":"a","??":"a","c":"c","c":"c","??":"c","??":"c","C":"c","C":"c","??":"c","??":"d","??":"d","??":"e","??":"e","??":"e","?":"e","??":"e","e":"e","e":"e","e":"e","e":"e",
"e":"e","??":"e","??":"e","??":"e","?":"e","E":"e","E":"e","E":"e","E":"e","E":"e","???":"e","g":"g","G":"g","i":"i","??":"i","??":"i","??":"i","??":"i","??":"i","i":"i",
"i":"i","i":"i","??":"i","??":"i","??":"i","??":"i","?":"i","??":"i","I":"i","I":"i","I":"i","l":"l","L":"l","n":"n","n":"n","??":"n","N":"n","N":"n","??":"n","??":"o",
"??":"o","??":"o","??":"o","??":"o","o":"o","o":"o","o":"o","??":"o","??":"o","??":"o","??":"o","??":"o","??":"o","O":"o","O":"o","O":"o","??":"o","??":"o","r":"r","??":"r",
"R":"r","??":"s","s":"s","?":"s","??":"s","??":"s","S":"s","?":"s","??":"u","??":"u","??":"u","??":"u","u":"u","u":"u","u":"u","u":"u","??":"u","??":"u","??":"u","??":"u",
"U":"u","U":"u","U":"u","U":"u","??":"y","??":"y","??":"y","??":"y","??":"z","z":"z","z":"z","??":"z","Z":"z","Z":"z"
  };
  return(s.replace(translate_re, function(match){return translate[match];}) );
};

