var Translate = Class.create();
Translate.prototype = {
	initialize: function(data)
	{
		this.data = $H(data);
	},
	add: function()
	{
		if(arguments.length > 1)
		{
			this.data.set(arguments[0], arguments[1]);
		}
		else if(typeof arguments[0] == 'object')
		{
			$H(arguments[0]).each(function(pair) {
				this.data.set(pair.key, pair.value);
			});
		}

		return this;
	},
	translate: function(string)
	{
		if(this.data.get(string))
		{
			return this.data.get(string);
		}

		return string;
	}
};

Translate.getInstance = function()
{
	if(this.instance == null)
	{
		this.instance = new Translate();
	}

	return this.instance;
}
