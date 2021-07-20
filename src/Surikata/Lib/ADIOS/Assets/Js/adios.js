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
  var translate_re = /[¹²³áàâãäåaaaÀÁÂÃÄÅAAAÆccç©CCÇÐÐèéê?ëeeeeeÈÊË?EEEEE€gGiìíîïìiiiÌÍÎÏ?ÌIIIlLnnñNNÑòóôõöoooøÒÓÔÕÖOOOØŒr®Ršs?ßŠS?ùúûüuuuuÙÚÛÜUUUUýÿÝŸžzzŽZZ]/g;
  var translate = {
"¹":"1","²":"2","³":"3","á":"a","à":"a","â":"a","ã":"a","ä":"a","å":"a","a":"a","a":"a","a":"a","À":"a","Á":"a","Â":"a","Ã":"a","Ä":"a","Å":"a","A":"a","A":"a",
"A":"a","Æ":"a","c":"c","c":"c","ç":"c","©":"c","C":"c","C":"c","Ç":"c","Ð":"d","Ð":"d","è":"e","é":"e","ê":"e","?":"e","ë":"e","e":"e","e":"e","e":"e","e":"e",
"e":"e","È":"e","Ê":"e","Ë":"e","?":"e","E":"e","E":"e","E":"e","E":"e","E":"e","€":"e","g":"g","G":"g","i":"i","ì":"i","í":"i","î":"i","ï":"i","ì":"i","i":"i",
"i":"i","i":"i","Ì":"i","Í":"i","Î":"i","Ï":"i","?":"i","Ì":"i","I":"i","I":"i","I":"i","l":"l","L":"l","n":"n","n":"n","ñ":"n","N":"n","N":"n","Ñ":"n","ò":"o",
"ó":"o","ô":"o","õ":"o","ö":"o","o":"o","o":"o","o":"o","ø":"o","Ò":"o","Ó":"o","Ô":"o","Õ":"o","Ö":"o","O":"o","O":"o","O":"o","Ø":"o","Œ":"o","r":"r","®":"r",
"R":"r","š":"s","s":"s","?":"s","ß":"s","Š":"s","S":"s","?":"s","ù":"u","ú":"u","û":"u","ü":"u","u":"u","u":"u","u":"u","u":"u","Ù":"u","Ú":"u","Û":"u","Ü":"u",
"U":"u","U":"u","U":"u","U":"u","ý":"y","ÿ":"y","Ý":"y","Ÿ":"y","ž":"z","z":"z","z":"z","Ž":"z","Z":"z","Z":"z"
  };
  return(s.replace(translate_re, function(match){return translate[match];}) );
};

