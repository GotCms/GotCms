/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */
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
            var $regexp,
                $exp = '#\\{' + index + '\\}';

            $regexp = new RegExp($exp, "gi");
            $content = $content.replace($regexp, value);
        });

        return $content;
    };
}

$.widget("ui.dialog", $.extend({}, $.ui.dialog.prototype, {
    _title: function(title) {
        if (!this.options.title ) {
            title.html("&#160;");
        } else {
            title.html(this.options.title);
        }
    }
}));
