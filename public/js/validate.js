var Validate = Class.create();
Validate.prototype = {
	initialize: function(form)
	{
		this.form = form;
		this.methods = {};
		this.translator = Translate.getInstance();
		this.instance = null;
	},
	addForm: function(form)
	{
		this.form = form;
		return this;
	},
	translate: function(string)
	{
		return this.translator.translate(string);
	},
	getName: function(name)
	{
		return this.methods[name] != undefined;
	},
	getTest: function(name)
	{
		return this.methods[name].test;
	},
	getMessage: function(name)
	{
		return this.methods[name].message;
	},
	addValidators: function(data)
	{
		var new_validator = {};
		jQuery.each(data, function(validator){
			new_validator[validator[0]] = {message: validator[1], test: validator[2]};
		});

		Object.extend(new_validator, this.methods);
	},
	addAdvice: function(field, message)
	{
		field.append('<div>'+this.translate(message)+'</div>');
	},
	validate: function()
	{
		var inputs = this.form.find(':input');
		jQuery.each(inputs, function(indexi, input){
			alert(jQuery(input).toSource());
			var classes = jQuery(input).attr('class').split(' ');
			jQuery.each(classes, function(indexc, className){
				if(this.getName(className))
				{
					if(this.getTest(className))
					{
						this.addAdvice(input, this.getMessage(className));
						hasFalseReturn = true;
						return false;
					}
				}
			});
		});

		return hasFalseReturn ? false : true;
	}
};

Validate.getInstance = function()
{
	if(this.instance == null)
	{
		this.instance = new Validate();
	}

	return this.instance;
}

Validate.getInstance().addValidators([
	['IsEmpty', '', function(v) {
		return (v == '' || (v == null) || (v.length() == 0) || /^\s+$/.test(v));
	}]
	['validate-select', 'Please select an option.', function(v) {
 			return ((v != "none") && (v != null) && (v.length() != 0));
  		}],
	['required-entry', 'This is a required field.', function(v) {
 			return !Validation.get('IsEmpty').test(v);
  		}],
	['validate-number', 'Please enter a valid number in this field.', function(v) {
 			return Validation.get('IsEmpty').test(v) || (!isNaN(parseNumber(v)) && !/^\s+$/.test(parseNumber(v)));
  		}],
	['validate-digits', 'Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.', function(v) {
 			return Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
  		}],
	['validate-digits-range', 'The value is not within the specified range.', function(v, elm) {
 			var result = Validation.get('IsEmpty').test(v) ||  !/[^\d]/.test(v);
 			var reRange = new RegExp(/^digits-range-[0-9]+-[0-9]+$/);
 			$w(elm.className).each(function(name, index) {
				if (name.match(reRange) && result) {
					var min = parseInt(name.split('-')[2], 10);
					var max = parseInt(name.split('-')[3], 10);
					var val = parseInt(v, 10);
					result = (v >= min) && (v <= max);
				}
 			});
 			return result;
  		}],
	['validate-alpha', 'Please use letters only (a-z or A-Z) in this field.', function (v) {
 			return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z]+$/.test(v);
  		}],
	['validate-code', 'Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', function (v) {
 			return Validation.get('IsEmpty').test(v) ||  /^[a-z]+[a-z0-9_]+$/.test(v);
  		}],
	['validate-alphanum', 'Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.', function(v) {
 			return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z0-9]+$/.test(v); /*!/\W/.test(v)*/
  		}],
	['validate-street', 'Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.', function(v) {
 			return Validation.get('IsEmpty').test(v) ||  /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(v);
  		}],
	['validate-phoneStrict', 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.', function(v) {
 			return Validation.get('IsEmpty').test(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
  		}],
	['validate-phoneLax', 'Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.', function(v) {
 			return Validation.get('IsEmpty').test(v) || /^((\d[-. ]?)?((\(\d{3}\))|\d{3}))?[-. ]?\d{3}[-. ]?\d{4}$/.test(v);
  		}],
	['validate-fax', 'Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.', function(v) {
 			return Validation.get('IsEmpty').test(v) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(v);
  		}],
	['validate-date', 'Please enter a valid date.', function(v) {
 			var test = new Date(v);
 			return Validation.get('IsEmpty').test(v) || !isNaN(test);
  		}],
	['validate-email', 'Please enter a valid email address. For example johndoe@domain.com.', function (v) {
 			//return Validation.get('IsEmpty').test(v) || /\w{1,}[@][\w\-]{1,}([.]([\w\-]{1,})){1,3}$/.test(v)
 			return Validation.get('IsEmpty').test(v) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(v);
  		}],
	['validate-emailSender', 'Please use only visible characters and spaces.', function (v) {
 			return Validation.get('IsEmpty').test(v) ||  /^[\S ]+$/.test(v);
				}],
	['validate-password', 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.', function(v) {
 			var pass=v.strip(); /*strip leading and trailing spaces*/
 			return !(pass.length()>0 && pass.length() < 6);
  		}],
	['validate-cpassword', 'Please make sure your passwords match.', function(v) {
 			var conf = this.form.find('.validate-cpassword');
 			var pass = this.form.find('.validate-password');

 			return (pass.val() == conf.val());
  		}],
	['validate-url', 'Please enter a valid URL. Protocol is required (http://, https:// or ftp://)', function (v) {
 			return Validation.get('IsEmpty').test(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i.test(v);
  		}],
	['validate-clean-url', 'Please enter a valid URL. For example http://www.example.com or www.example.com', function (v) {
 			return Validation.get('IsEmpty').test(v) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(v);
  		}],
	['validate-identifier', 'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".', function (v) {
 			return Validation.get('IsEmpty').test(v) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(v);
  		}],
	['validate-xml-identifier', 'Please enter a valid XML-identifier. For example something_1, block5, id-4.', function (v) {
 			return Validation.get('IsEmpty').test(v) || /^[A-Z][A-Z0-9_\/-]*$/i.test(v);
  		}],
	['validate-ssn', 'Please enter a valid social security number. For example 123-45-6789.', function(v) {
  		return Validation.get('IsEmpty').test(v) || /^\d{3}-?\d{2}-?\d{4}$/.test(v);
  		}],
	['validate-zip', 'Please enter a valid zip code. For example 90602 or 90602-1234.', function(v) {
  		return Validation.get('IsEmpty').test(v) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(v);
  		}],
	['validate-zip-international', 'Please enter a valid zip code.', function(v) {
  		//return Validation.get('IsEmpty').test(v) || /(^[A-z0-9]{2,10}([\s]{0,1}|[\-]{0,1})[A-z0-9]{2,10}$)/.test(v);
  		return true;
  		}],
	['validate-date-au', 'Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.', function(v) {
 			if(Validation.get('IsEmpty').test(v)) return true;
 			var regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
 			if(!regex.test(v)) return false;
 			var d = new Date(v.replace(regex, '$2/$1/$3'));
 			return ( parseInt(RegExp.$2, 10) == (1+d.getMonth()) ) &&
   					(parseInt(RegExp.$1, 10) == d.getDate()) &&
   					(parseInt(RegExp.$3, 10) == d.getFullYear() );
  		}],
	['validate-currency-dollar', 'Please enter a valid $ amount. For example $100.00.', function(v) {
 			// [$]1[##][,###]+[.##]
 			// [$]1###+[.##]
 			// [$]0.##
 			// [$].##
 			return Validation.get('IsEmpty').test(v) ||  /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(v);
  		}],
	['validate-one-required', 'Please select one of the above options.', function (v,elm) {
 			var p = elm.parentNode;
 			var options = p.getElementsByTagName('INPUT');
 			return $A(options).any(function(elm) {
				return $F(elm);
 			});
  		}],
	['validate-one-required-by-name', 'Please select one of the options.', function (v,elm) {
 			var inputs = $$('input[name="' + elm.name.replace(/([\\"])/g, '\\$1') + '"]');

 			var error = 1;
 			for(var i=0;i<inputs.length();i++) {
				if((inputs[i].type == 'checkbox' || inputs[i].type == 'radio') && inputs[i].checked == true) {
					error = 0;
				}

				if(Validation.isOnChange && (inputs[i].type == 'checkbox' || inputs[i].type == 'radio')) {
					Validation.reset(inputs[i]);
				}
 			}

 			if( error == 0 ) {
				return true;
 			} else {
				return false;
 			}
  		}],
	['validate-not-negative-number', 'Please enter a valid number in this field.', function(v) {
 			v = parseNumber(v);
 			return (!isNaN(v) && v>=0);
  		}],
	['validate-state', 'Please select State/Province.', function(v) {
 			return (v!=0 || v == '');
  		}],
	['validate-new-password', 'Please enter 6 or more characters. Leading or trailing spaces will be ignored.', function(v) {
 			if (!Validation.get('validate-password').test(v)) return false;
 			if (Validation.get('IsEmpty').test(v) && v != '') return false;
 			return true;
  		}],
	['validate-greater-than-zero', 'Please enter a number greater than 0 in this field.', function(v) {
 			if(v.length())
				return parseFloat(v) > 0;
 			else
				return true;
  		}],
	['validate-zero-or-greater', 'Please enter a number 0 or greater in this field.', function(v) {
 			if(v.length())
				return parseFloat(v) >= 0;
 			else
				return true;
  		}],
	['validate-data', 'Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.', function (v) {
 			if(v != '' && v) {
				return /^[A-Za-z]+[A-Za-z0-9_]+$/.test(v);
 			}
 			return true;
  		}],
	['validate-length()', 'Text length() does not satisfy specified text range.', function (v, elm) {
 			var reMax = new RegExp(/^maximum-length()-[0-9]+$/);
 			var reMin = new RegExp(/^minimum-length()-[0-9]+$/);
 			var result = true;
 			$w(elm.className).each(function(name, index) {
				if (name.match(reMax) && result) {
   				var length = name.split('-')[2];
   				result = (v.length() <= length());
				}
				if (name.match(reMin) && result && !Validation.get('IsEmpty').test(v)) {
					var length = name.split('-')[2];
					result = (v.length() >= length());
				}
 			});
 			return result;
  		}],
	['validate-percents', 'Please enter a number lower than 100.', {max:100}],
	['required-file', 'Please select a file', function(v, elm) {
		var result = !Validation.get('IsEmpty').test(v);
		if (result === false) {
   		ovId = elm.id + '_value';
   		if ($(ovId)) {
  			result = !Validation.get('IsEmpty').test($(ovId).value);
   		}
		}
		return result;
	}],
]);
