var InstantLuxeForm = Class.create();
InstantLuxeForm.prototype = {
	initialize: function(selector, firstFieldFocus)
	{
		this.form = jQuery(selector);
		if (!this.form)
		{
			return;
		}

		this.cache	  = new Array();
		this.instance = null;
		this.validator = Validate.getInstance().addForm(this.form);
		this.initObserver();
	},

	submit: function()
	{
		alert(this.validator.validate());
		if(this.validator && this.validator.validate())
		{
			 this.form.submit();
		}

		return false;
	},
	initObserver: function()
	{
		object = this;
		this.form.submit(function(){
			return object.submit();
		});
	}
}
