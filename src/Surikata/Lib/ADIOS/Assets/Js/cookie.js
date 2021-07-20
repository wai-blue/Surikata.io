var Cookies = {
	aliases: {},

	alias: function(alias, name, defaultValue)
	{
		Cookies.aliases[alias] = name;

		Cookies[alias] = function(value, days)
		{
			if(value == null)
				return Cookies.get(name, defaultValue);
			else {
				Cookies.set(name, value, days);
        return null;
      };
		}
	},

	set: function(name, value, days)
	{
		name = Cookies.aliases[name] || name;

		var expires = '';

		if(!isNaN(days))
		{
			var date = new Date();
			date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
			expires = "; expires=" + date.toGMTString();
		}

		document.cookie = name + "=" + escape(value) + expires + "; path=/";
	},

	get: function(name, defaultValue)
	{
		name = Cookies.aliases[name] || name;

		var regex = new RegExp(name + "s*=s*(.*?)(;|$)");
		var cookies = document.cookie.toString();
		var match = cookies.match(regex);

		if(match)
			return unescape(match[1]);

		return defaultValue;
	},

	erase: function(name)
	{
		Cookies.set(name, '', -1);
	}
}
