window.commonMethods = {
    localStorageSettings: function(obj, callback) {
        'use strict';
        var tempObj = {};
        if(!obj.namespace || !obj.namespace.length || !obj.method || !obj.method.length) {
            return;
        }

        if(window.localStorage && window.JSON) {
            if(obj.method.toLowerCase() === 'get') {
                callback.call(this, JSON.parse(window.localStorage.getItem(obj.namespace)));
            }
            else if(obj.method.toLowerCase() === 'set' && obj.data != null && $.isPlainObject(obj.data)) {
                tempObj = $.extend({}, JSON.parse(window.localStorage.getItem(obj.namespace)), obj.data);
                return window.localStorage.setItem(obj.namespace, JSON.stringify(tempObj));
            }
            else if(obj.method.toLowerCase() === 'set' && obj.data != null && $.isArray(obj.data)) {
                return window.localStorage.setItem(obj.namespace, JSON.stringify(obj.data));
            }
            else if(obj.method.toLowerCase() === 'remove') {
                return window.localStorage.removeItem(obj.namespace);
            }
        }
    },

    resetOptions: function(namespace, obj, callback) {
        'use strict';
        if(obj) {
            for(var x in obj) {
                if($(x).is(':checkbox') || $(x).is(':radio')) {
                    $(x).prop('checked', obj[x]).change();
                } else {
                    $(x).val(obj[x]).change().keyup();
                }
            }
            if(window.localStorage && namespace) {
                window.commonMethods.localStorageSettings({ namespace: namespace, method: 'remove' });
            }
            if(callback) {
                callback.call(this);
            }
        }
    },

    loadCode: function (namespace, callback) {
        'use strict';
        window.commonMethods.localStorageSettings({ namespace: namespace, method: 'get' }, function(obj) {
            if(obj) {
                for(var x in obj) {
                    if($(x).is(':checkbox') || $(x).is(':radio')) {
                        $(x).prop('checked', obj[x]).val(obj[x]);
                        if($(x).is(':checked'))  {
                            $(x).trigger('auto-dismiss');
                        }
                    } else {
                        $(x).val(obj[x]).attr('data-updated', 'updated');
                    }
                }
            }
            if(callback) {
                callback.call(this, obj);
            }
        });
    }
};

window.addthisnamespaces = {
    share: 'addthis-share-addthis-widget'
};