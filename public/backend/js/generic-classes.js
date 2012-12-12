var Translator = (function($)
{
    "use strict";
    return {
        data: new Hash(),

        initialize: function($data)
        {
            this.data = new Hash($data);
            return this;
        },

        translate: function(text)
        {
            if(this.data.get(text))
            {
                return this.data.get(text);
            }

            return text;
        },

        add: function(key, value)
        {
            this.data.set(key, value);

            return this;
        }
    };
})(jQuery);

function Hash($data)
{
    "use strict";
    this.nh = ($data === undefined) ? {} : $data;

    this.each = function(f)
    {
        for(var i in this.nh)
        {
            if(typeof(this.nh[i]) !== "function")
            {
                f({key: i, value: this.nh[i]});
            }
        }
    };

    this.get = function(k)
    {
        for(var i in this.nh)
        {
            if(i === k)
            {
                return this.nh[k];
            }
        }
    };

    this.set = function(k, v)
    {
        this.nh[k] = v;
        return this.nh;
    };
}

function Template($body)
{
    "use strict";
    this.body = $body;
    this.evaluate = function($data)
    {
        var $content = this.body;
        $.each($data, function(index, value)
        {
            var $exp = new RegExp(/#\{'+index+'\}/, "gi");
            $content = $content.replace($exp, value);
        });

        return $content;
    };
}
