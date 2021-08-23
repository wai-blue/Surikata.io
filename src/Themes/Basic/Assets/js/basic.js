(function() {
	var container = document.querySelector( 'div.container' ),
		triggerBttn = document.getElementById( 'trigger-overlay' ),
		overlay = document.querySelector( 'div.overlay' ),
		closeBttn = overlay.querySelector( 'button.overlay-close' );
		transEndEventNames = {
			'WebkitTransition': 'webkitTransitionEnd',
			'MozTransition': 'transitionend',
			'OTransition': 'oTransitionEnd',
			'msTransition': 'MSTransitionEnd',
			'transition': 'transitionend'
		},
		transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
		support = { transitions : Modernizr.csstransitions };

	function toggleOverlay() {
		if( classie.has( overlay, 'open' ) ) {
			classie.remove( overlay, 'open' );
			classie.remove( container, 'overlay-open' );
			classie.add( overlay, 'close' );
			var onEndTransitionFn = function( ev ) {
				if( support.transitions ) {
					if( ev.propertyName !== 'visibility' ) return;
					this.removeEventListener( transEndEventName, onEndTransitionFn );
				}
				classie.remove( overlay, 'close' );
			};
			if( support.transitions ) {
				overlay.addEventListener( transEndEventName, onEndTransitionFn );
			}
			else {
				onEndTransitionFn();
			}
		}
		else if( !classie.has( overlay, 'close' ) ) {
			classie.add( overlay, 'open' );
			classie.add( container, 'overlay-open' );
		}
	}

	triggerBttn.addEventListener( 'click', toggleOverlay );
	closeBttn.addEventListener( 'click', toggleOverlay );
})();

function searchKeyPressCallback(event, element) {
	var x = event.which || event.keyCode;
	if (x === 13) {
		element = $(element);
		sendSearchData(element.attr("id"));
		return false;
	}
	return false;
}

function sendSearchData(searchInput) {
	searchInput = $("#"+searchInput);
	searchValue = searchInput.val();
	var url = "{{ rootUrl }}";
	url += '/search?search='+searchValue;
	window.location.href = url;
	return false;
}

var customRenderMenu = function(ul, items){
	var self = this;
	var categoryArr = [];

	function contain(item, array) {
		var contains = false;
		$.each(array, function (index, value) {
			if (item == value) {
				contains = true;
				return false;
			}
		});
		return contains;
	}

	$.each(items, function (index, item) {
		if (! contain(item.category, categoryArr)) {
			categoryArr.push(item.category);
		}
	});

	$.each(categoryArr, function (index, category) {
		ul.append("<li class='ui-autocomplete-group'>" + category + "</li>");
		$.each(items, function (index, item) {
			if (item.category == category) {
				self._renderItemData(ul, item);
			}
		});
	});
};

$( "#headerSearch" ).autocomplete({
	source: function (request, response) {
		var requestData = {
			'action': 'website_find',
			'value': request.term,
			'__renderOnlyPlugin': 'WAI/Misc/WebsiteSearch',
			'__output': 'json',
		}
		$.getJSON('{{ rootUrl }}/WAI/Misc/WebsiteSearch',
			requestData
		).done(function (data) {
			if (typeof success == 'function') {
				response(data)
			}
			else {
				response(data);
			}
			console.log(data);
		});
	},
	create: function () {
		$(this).data('uiAutocomplete')._renderMenu = customRenderMenu;
		$(this).data('uiAutocomplete')._renderItem = function( ul, item ) {
			if (item.url != null) {
				return $("<li class='ui-menu-item'>")
					.attr("data-value", item.value)
					.append(
						$("<div id='ui-id-2' tabindex='-1' class='ui-menu-item-wrapper'>")
							.append(item.label)
							.attr("onclick", "window.location.href = '" + item.url + "'"))
					.appendTo(ul);
			}
			else {
				return $("<li class='ui-menu-item'>")
					.attr("data-value", item.value)
					.append(
						$("<div id='ui-id-2' tabindex='-1' class='ui-menu-item-wrapper'>")
							.append(item.label))
					.appendTo(ul);
			}
		};
	},
});