(function($) {
    'use strict';
    var $document      = $(document);

    var AddthisWidget = function AddthisWidget(element, options) {
        if (element === undefined) {
            return;
        }

        var settings      = {},
        $this = this;
        settings = $.extend({
            'shareNamespace': element.prop('id'),
            'servicesEndpoint': 'https://cache.addthiscdn.com/services/v1/sharing.en.ssl.jsonp?jsonpcallback=loadServices',
            // defaults button list
            'defaults': [
                'facebook_like',
                'tweet',
                /*'email',*/
                'pinterest_pinit',
                'google_plusone',
                /*'linkedin_counter',*/
                'compact'
            ]
        }, options);

        /**
         * Initialize dropdown
         */
        var intialize = function initialize() {
            element.find('.addthis-widget-smart-sharing-container .restore-default-options').hide();
            element.find('.sortable .disabled').tooltip({ position: { my: 'left+15 center', at: 'right center' } });
            element.find('.sortable .close').tooltip({ position: { my: 'left+10 center', at: 'right center' } });
            $this.events().fetchServices();
        };

        /**
         * Public methods
         */
        this.loadJS = function loadJS(file) {
            var self = this,
                script = document.createElement('script');
            script.src = file;
            document.body.appendChild(script);
            return Services.settings.loadDeferred;
        };

        this.fetchServices = function fetchServices() {
            var self = this,
                def = $.Deferred();

            if(Services.settings.scriptIncluded) {
                def.resolve(Services.settings.addthisButtons.services);
                return def;
            }
            else {
                Services.settings.scriptIncluded = true;
                self.loadJS(settings.servicesEndpoint).done(function(services) {
                    def.resolve(services);
                });
            }

            return def;
        };

        this.populateSharingServices = function populateSharingServices(restoreDefaults, pageload) {
            var self = this,
                def = $.Deferred(),
                defaults,
                services,
                style,
                currentType,
                addthisMappedDefaults,
                thirdPartyMappedDefaults,
                addthisServices,
                thirdPartyServices,
                sharingSortable = element.find('.addthis-widget-smart-sharing-container .sharing-buttons .sortable'),
                selectedSortable = element.find('.addthis-widget-smart-sharing-container .selected-services .sortable');
            self.fetchServices().done(function(services) {
                if(!services.length) {
                    def.resolve();
                } else {
                    self.getSavedOrder(function(obj) {
                        defaults = restoreDefaults ? settings.defaults : obj.rememberedDefaults;
                        currentType = element.find('input[name$="[settings]"]:checked');
                        if(!currentType.length) {
                            currentType = element.find('input[name$="[settings]"]:visible').first();
                        }

                        if(currentType.length) {
                            if(currentType.val() === 'large_toolbox') {
                                style = 'horizontal';
                                currentType = 'addthisButtons';
                            } else if(currentType.val() === 'fb_tw_p1_sc') {
                                style = 'horizontal';
                                currentType = 'thirdPartyButtons';
                            } else if(currentType.val() === 'small_toolbox') {
                                style = 'horizontal';
                                currentType = 'addthisButtons';
                            } else if(currentType.val() === 'button') {
                                style = '';
                                currentType = 'image';
                            }

                            addthisMappedDefaults = _.map(defaults, function(value) {
                                var service = _.where(Services.settings.thirdPartyButtons.services(), { 'service': value });
                                if(service.length) {
                                    return service[0].linkedService;
                                } else {
                                    return value;
                                }
                            });
                            thirdPartyMappedDefaults = _.map(defaults, function(value) {
                                var service = _.where(Services.settings.thirdPartyButtons.services(), { 'linkedService': value });
                                if(service.length) {
                                    return service[0].service;
                                } else {
                                    return value;
                                }
                            });

                            defaults = currentType === 'addthisButtons' ?
                                addthisMappedDefaults :
                                thirdPartyMappedDefaults;
                            addthisServices = self.sort(
                                {defaults: addthisMappedDefaults, services: Services.settings.totalServices}
                            );
                            thirdPartyServices = self.sort(
                                {defaults: thirdPartyMappedDefaults, services: Services.settings.totalServices}
                            );

                            if(currentType === 'addthisButtons') {
                                self.populateList(
                                    {
                                        elem: sharingSortable,
                                        services: addthisServices,
                                        exclude: Services.settings.addthisButtons.exclude,
                                        defaults: addthisMappedDefaults,
                                        type: 'sharing-buttons',
                                        buttonType: 'addthisButtons'
                                    }
                                );
                                self.populateList(
                                    {
                                        elem: selectedSortable,
                                        services: addthisServices,
                                        exclude: Services.settings.addthisButtons.exclude,
                                        defaults: addthisMappedDefaults,
                                        type: 'selected-services',
                                        buttonType: 'addthisButtons'
                                    }
                                );
                            }

                            if(currentType === 'thirdPartyButtons' && style === 'horizontal') {
                                self.populateList(
                                    {
                                        elem: sharingSortable,
                                        services: thirdPartyServices,
                                        exclude: Services.settings.thirdPartyButtons.exclude.horizontal,
                                        defaults: thirdPartyMappedDefaults,
                                        type: 'sharing-buttons',
                                        style: 'horizontal',
                                        buttonType: 'thirdPartyButtons'
                                    }
                                );
                                self.populateList(
                                    {
                                        elem: selectedSortable,
                                        services: thirdPartyServices,
                                        exclude: Services.settings.thirdPartyButtons.exclude.horizontal,
                                        defaults: thirdPartyMappedDefaults,
                                        type: 'selected-services',
                                        style: 'horizontal',
                                        buttonType: 'thirdPartyButtons'
                                    }
                                );
                            }

                            $('body').trigger('populatedList');
                            def.resolve();
                        } else {
                            $('body').trigger('populatedList');
                            def.resolve();
                        }
                    });
                }
            });

            return def;
        };

        this.sort = function sort(obj) {
            var self = this,
                copiedItem,
                whereInArray,
                currentService,
                defaults = obj.defaults,
                services = $.merge([], obj.services);

            // Sorts the addthis button list in the correct order
            $.each(services, function(iterator, value) {
                currentService = value.service;
                whereInArray = $.inArray(currentService, defaults);
                if(whereInArray !== -1) {
                    copiedItem = services[whereInArray];
                    services[whereInArray] = value;
                    services[iterator] = copiedItem;
                }
            });

            return services;
        };

        this.populateList = function populateList(obj) {
            var self = this,
                list = obj.elem,
                listHtml = '',
                service,
                type = obj.type,
                services = obj.services,
                iconService = '',
                defaults = obj.defaults,
                attrs,
                name,
                style = obj.style,
                duplicates = [],
                buttonType = obj.buttonType,
                buttonServices = buttonType === 'addthisButtons' ?
                    Services.settings.addthisButtons.services :
                    Services.settings.thirdPartyButtons.services(),
                excludeList = obj.exclude,
                thirdPartyDisabled = (function() {
                    var arr, disabledArr = [], serviceObj;
                    if(buttonType === 'addthisButtons' || type === 'selected-services') {
                        return disabledArr;
                    } else {
                        arr = _.filter(Services.settings.thirdPartyButtons.exclude[style], function(value) {
                            return $.inArray(value, defaults) === -1;
                        });
                        disabledArr = [];
                        serviceObj = {};
                        _.each(arr, function(value) {
                            serviceObj = _.where(Services.settings.thirdPartyButtons.services(), { service: value });
                            if(serviceObj.length) {
                                disabledArr.push(serviceObj[0]);
                            }
                        });
                    }
                    return disabledArr;
                }()),
                disabledServices = (buttonType === 'addthisButtons' || type === 'selected-services') ?
                    [] :
                    $.merge($.merge([], Services.settings.disabledServices), thirdPartyDisabled),
                selectedDefaults = [],
                isDuplicate = false,
                isDefault = false,
                isExcluded = false,
                containsService = false;

            for(var key in services) {
                if(services.hasOwnProperty(key)) {
                    var value = services[key],
                    service = (value.service) || key,
                    name = value.name,
                    iconService = value.icon,
                    isDuplicate = $.inArray(service, duplicates) !== -1,
                    isDefault = $.inArray(service, defaults) !== -1,
                    isExcluded = $.inArray(service, excludeList) !== -1,
                    containsService = _.where(buttonServices , { 'service': service }).length;
                    if(!isDuplicate) {
                        if(type === 'selected-services') {
                            if(defaults && isDefault) {
                                selectedDefaults.push(service);
                                if(!containsService || isExcluded) {
                                    listHtml += '<li class="disabled service" data-service="' + service + ' title=" \
                                        The ' + name + ' button is not supported in this style"><span class=" \
                                        at300bs at15nc at15t_' + iconService + ' at16t_' + iconService + '" \
                                        ></span><span class="service-name">' + name + '</span> \
                                        <button type="button" title="Remove" class="close">x</button></li>';
                                } else {
                                    listHtml += '<li class="enabled service" data-service="' + service + '"> \
                                        <span class="at300bs at15nc at15t_' + iconService + ' \
                                        at16t_' + iconService + '"></span><span class="service-name">\
                                        ' + name + '</span><button type="button" title="Remove" \
                                        class="close">x</button></li>';
                                }
                            }
                        } else {
                            if(defaults && !isDefault && !isExcluded) {
                                if(containsService) {
                                    listHtml += '<li class="enabled service" data-service="' + service + '">\
                                        <span class="at300bs at15nc at15t_' + iconService + ' \
                                        at16t_' + iconService + '"></span><span class="service-name">\
                                        ' + name + '</span><button type="button" title="Remove" \
                                        class="close">x</button></li>';
                                }
                            }
                        }

                        duplicates.push(service);
                    }
                }
            }

            if(disabledServices.length) {
                $.each(disabledServices, function(iterator, disabledService) {
                    var service = disabledService.service,
                        iconService = disabledService.icon,
                        name = disabledService.name;
                    listHtml += "<li class='disabled service' data-service='" + service + "' title='The " + name + " button is not supported in this style'><span class='at300bs at15nc at15t_" + iconService + " at16t_" + iconService + "' style='display:inline-block;padding-right:10px;vertical-align:middle;margin-left:10px;'></span><span class='service-name'>" + name + "</span><button type='button' title='Remove' class='close'>Ã—</button></li>";
                });
            }

            if(!defaults.length && type === 'selected-services') {
                listHtml = '<p class="add-buttons-msg">Add buttons by dragging them in this box.</p>';
                list.css('border-style', 'dashed');
            } else if(defaults.length && type === 'selected-services') {
                list.css('border-style', 'solid');
            }

            list.html(listHtml).data('selectedDefaults', selectedDefaults);

            return self;
        };

        this.getSavedOrder = function getSavedOrder(callback) {
            var self = this;
            if(window.commonMethods && window.commonMethods.localStorageSettings) {
                window.commonMethods.localStorageSettings({ namespace: settings.shareNamespace, method: 'get' }, function(obj) {
                    callback.call(self, obj || { rememberedDefaults: settings.defaults });
                });
            } else {
                callback.call(self, {});
            }

            return self;
        };

        this.saveOrder = function saveOrder(obj) {
            var self = this,
                defaults = [],
                dynamicObj = {},
                size = obj['size'],
                type = obj['type'],
                style = obj['style'],
                updatedItem = obj['item'],
                currentService,
                elem = obj.elem,
                serviceItems = elem.find('li'),
                enabled = elem.find('li.enabled'),
                disabled = elem.find('li.disabled'),
                enabledDefaults = [],
                removed = true;

            serviceItems.each(function(iterator) {
                currentService = $(this).attr('data-service');
                defaults.push(currentService);
                if(currentService === updatedItem) {
                    removed = false;
                }

                if($(this).hasClass('enabled')) {
                    enabledDefaults.push(currentService);
                }
            });

            if(window.commonMethods && window.commonMethods.localStorageSettings) {
                dynamicObj['rememberedDefaults'] = defaults;
                window.commonMethods.localStorageSettings({ namespace: settings.shareNamespace, method: 'set', data: dynamicObj });
            }

            return self;
        };

        this.events = function events() {
            var self = this,
                abvimg,
                enableSmartSharing = element.find('.addthis-widget-enable-smart-sharing'),
                disableSmartSharing = element.find('.addthis-widget-disable-smart-sharing'),

                sortableContainer,
                radioInputs = element.find('input[name$="[settings]"]'),
                currentRadioInput,
                currentType,
                currentStyle,
                excludeList,
                whereInputs = element.find('input[name=where]'),
                smartSharingContainer = element.find('.addthis-widget-smart-sharing-container'),
                smartSharingInnerContainer = element.find('.addthis-widget-smart-sharing-container .smart-sharing-inner-container'),
                customizeButtons = element.find('.addthis-widget-smart-sharing-container .customize-buttons'),
                Buttons = element.find('.addthis-widget-smart-sharing-container .customize-buttons'),
                defaults,

                buttontype,
                buttonsize,
                buttonstyle,
                RestoreDefaultOptions = element.find('.addthis-widget-smart-sharing-container .restore-default-options'),
                restoreToDefault = _.debounce(function() {
                    // Updates the personalization UI
                    self.populateSharingServices(true);
                    element.find('.addthis-widget-smart-sharing-container .selected-services .sortable:visible').trigger('sortupdate');
                    window.commonMethods.localStorageSettings({ method: "remove", namespace: settings.shareNamespace });
                }, 1000, true);

            //to show options upon save
            if(element.find('input[name$="[chosen_list]"]').val() != "") {
                element.find('.addthis-widget-smart-sharing-container .customizedMessage').show();
                element.find('.addthis-widget-smart-sharing-container .ownButtonsMessage').hide();
            }

            element.closest('form').on('submit', function() {
                var list = [];
                element.find('.selected-services .ui-sortable').each(function() {
                    var service = '';
                    $(this).find('li').each(function(){
                        if($(this).hasClass('enabled')) {
                            list.push($(this).attr('data-service'));
                            if($(this).attr('data-service') == 'compact') {
                                list.push('counter');
                            }
                        }
                    });
                });
                var aboveservices = list.join(', ');
                element.find('input[name$="[chosen_list]"]').val(aboveservices);
            });

            disableSmartSharing.add(radioInputs).not('input[value="button"]').on('click', function() {
                currentRadioInput = element.find('input[name$="[settings]"]:checked');
                if(!currentRadioInput.length) {
                    currentRadioInput = element.find('input[name$="[settings]"]').first();
                }

                if(currentRadioInput.val() == 'large_toolbox') {
                    currentStyle = "horizontal";
                    currentType = "addthisButtons";
                } else if(currentRadioInput.val() == 'fb_tw_p1_sc') {
                    currentStyle = "horizontal";
                    currentType = "thirdPartyButtons";
                } else if(currentRadioInput.val() == 'small_toolbox') {
                    currentStyle = "horizontal";
                    currentType = "addthisButtons";
                } else if(currentRadioInput.val() == 'button') {
                    currentStyle = "";
                    currentType = "image";
                }

                if(disableSmartSharing.is(':checked')) {
                    if(currentType === 'addthisButtons' || currentType === 'thirdPartyButtons') {
                        Buttons.show();
                    } else {
                        Buttons.hide();
                    }

                    radioInputs.addClass('disabled-smart-sharing');
                    setTimeout(function() {
                        // Updates the personalization UI
                        self.populateSharingServices();
                        element.find('.addthis-widget-smart-sharing-container .selected-services .sortable:visible').trigger('sortupdate');
                    }, 0);

                    RestoreDefaultOptions.show();
                    element.find('.sharing-buttons-search').val('');
                }

                smartSharingInnerContainer.show();
                element.find('.addthis-widget-customize-sharing-link, .customize-sharing-checkbox').show();
            });

            element.find('input[value="button"]').on('click',function() {
                var self = $(this);
                smartSharingInnerContainer.hide();
                element.find('.addthis-widget-customize-sharing-link, .customize-sharing-checkbox').hide();
            });


            enableSmartSharing.on('click', function() {
                if(element.find('input[name$="[chosen_list]"]').val() != "") {
                    element.find('.addthis-widget-smart-sharing-container .customizedMessage').hide();
                    element.find('input[name$="[chosen_list]"]').val('');
                } else {
                    element.find('.addthis-widget-smart-sharing-container .customizedMessage').hide();
                }

                currentRadioInput = element.find('input[name$="[settings]"]:checked');
                customizeButtons.hide();
                RestoreDefaultOptions.hide();
                radioInputs.removeClass('disabled-smart-sharing');
                currentRadioInput.click();
            });

            disableSmartSharing.on('click', function() {
                customizeButtons.show();
            });

            disableSmartSharing.one('click', function() {
                setTimeout(function() {
                    // Makes the new list sortable
                    element.find('.addthis-widget-smart-sharing-container .sortable').sortable({
                        placeholder: "sortable-placeholder",
                        revert: true,
                        scroll: false,
                        cancel: '.add-buttons-msg, .disabled',
                        start: function(ev, obj) {
                            if(obj && obj.item) {
                                if(obj.item.parent().parent().hasClass('sharing-buttons')) {
                                    obj.item.data('cancel', true);
                                } else {
                                    obj.item.removeData('cancel');
                                }
                            }
                        },

                        stop: function(ev, obj) {
                            if(obj && obj.item) {
                                if(obj.item.data('cancel') && obj.item.parent().parent().hasClass('sharing-buttons')) {
                                    return false;
                                } else {
                                    obj.item.removeData('cancel');
                                }
                            }
                        }
                    }).disableSelection().sortable('option', 'connectWith', '.sortable');
                }, 0);
            });

            element.find('.addthis_widget_button_set .selected-services .sortable').on('sortupdate', function(ev, item) {
                if($.isPlainObject(item)) {
                    item = item.item.attr('data-service');
                }

                if(!$(this).find('li').length) {
                    $(this).html('<p class="add-buttons-msg">Add buttons by dragging them in this box.</p>');
                    $(this).css('border-style', 'dashed');
                    element.find('.add-buttons-msg').show();
                } else {
                    $(this).css('border-style', 'solid');
                }

                var sortableList = element.find('.addthis-widget-smart-sharing-container .selected-services .sortable:visible');
                if(currentRadioInput.val() == 'large_toolbox') {
                    buttonstyle = "horizontal";
                    buttontype = "addthisButtons";
                    buttonsize = "large";
                } else if(currentRadioInput.val() == 'fb_tw_p1_sc') {
                    buttonstyle = "horizontal";
                    buttontype = "thirdPartyButtons";
                    buttonsize = "";
                } else if(currentRadioInput.val() == 'small_toolbox') {
                    buttonstyle = "horizontal";
                    buttontype = "addthisButtons";
                    buttonsize = "small";
                } else if(currentRadioInput.val() == 'button') {
                    buttonstyle = "";
                    buttontype = "image";
                    buttonsize = "";
                }

                self.saveOrder({ tool: 'above', type: buttontype, elem: sortableList, size: buttonsize, style: buttonstyle, item: item || "" });
            });

            RestoreDefaultOptions.on('click', function(ev) {
                ev.preventDefault();
                setTimeout(function() {
                    element.find('.addthis-widget-smart-sharing-container .sharing-buttons-search').val('');
                    restoreToDefault();
                }, 1);
            });

            $(document).on({
                'mouseenter': function() {
                    $(this).find('.close').css('display', 'inline-block');
                },
                'mouseleave': function() {
                    $(this).find('.close').hide();
                },
                'mouseup': function() {
                    $(this).find('.close').hide();
                }
            }, '.selected-services li');

            $(document).on({
                'mouseup': function() {
                    if(!element.find('.selected-services li:visible').length) {
                        element.find('.add-buttons-msg').show();
                    }
                },
                'mousedown': function() {
                    element.find('.add-buttons-msg').hide();
                    element.find('.addthis-widget-smart-sharing-container .horizontal-drag').hide();
                }
            }, '.sortable li');


            $(document).on('click', '.selected-services li .close', function() {
                var parent = $(this).closest('li'),
                    isDisabled = parent.hasClass('disabled');
                parent.fadeOut().promise().done(function() {
                    element.find('.sharing-buttons .sortable:visible').prepend(parent);
                    parent.find('.close').hide().tooltip().tooltip('close');
                    parent.fadeIn();
                    element.find('.selected-services .sortable:visible').trigger('sortupdate', parent.attr('data-service'));
                });
            });

            element.find('.addthis-widget-smart-sharing-container .sharing-buttons-search').on('keyup', function(e) {
                var currentVal = $(this).val();
                element.find('.addthis-widget-smart-sharing-container .sharing-buttons .sortable').find('li').each(function() {
                    if($(this).text().toLowerCase().search(currentVal.toLowerCase()) === -1) {
                        $(this).hide().attr('data-hidden', 'true');
                    } else {
                        $(this).show().removeAttr('data-hidden');
                    }
                });
            });

            element.find('.addthis-widget-smart-sharing-container .sortable').on('mousedown', function() {
                if(element.find('.addthis-widget-smart-sharing-container .sharing-buttons-search').is(':focus')) {
                    element.find('.addthis-widget-smart-sharing-container .sharing-buttons-search').blur();
                }
            });

            element.find('.addthis-widget-smart-sharing-container .selected-services .sortable').on({
                'mouseover': function() {
                    if($(this).find('li.enabled:visible').length > 1) {
                        element.find('.addthis-widget-smart-sharing-container .horizontal-drag').hide();
                        element.find('.addthis-widget-smart-sharing-container .vertical-drag').show();
                    }
                },
                'mouseout': function() {
                    element.find('.addthis-widget-smart-sharing-container .vertical-drag').hide();
                }
            });

            element.find('.addthis-widget-smart-sharing-container .sharing-buttons .sortable').on({
                'mouseover': function() {
                    if($(this).find('li.enabled:visible').length) {
                        element.find('.addthis-widget-smart-sharing-container .vertical-drag').hide();
                        element.find('.addthis-widget-smart-sharing-container .horizontal-drag').show();
                    }
                },
                'mouseout': function() {
                    element.find('.addthis-widget-smart-sharing-container .horizontal-drag').hide();
                }
            });

            element.find('.addthis-widget-customize-sharing-link').on('click', function(ev) {
                var SmartSharingLink = element.find('.addthis-widget-smart-sharing-container .smart-sharing-link'),
                    customizeButtonLink = element.find('.addthis-widget-smart-sharing-container .customize-your-buttons');
                ev.preventDefault();

                if($(this).is(customizeButtonLink)) {
                    customizeButtonLink.hide();
                    SmartSharingLink.show();
                    if(!disableSmartSharing.is(':checked')) {
                        disableSmartSharing.prop('checked', true).trigger('click');
                    }
                } else if($(this).is(SmartSharingLink)) {
                    SmartSharingLink.hide();
                    customizeButtonLink.show();
                    if(!enableSmartSharing.is(':checked')) {
                        enableSmartSharing.prop('checked', true).trigger('click');
                    }
                }
            });

            if(element.find('input[value="large_toolbox"]').is(':checked')) {
                if(element.find('input[name$="[chosen_list]"]').val() == "") {
                    abvimg = '<img alt="large_toolbox" src="'+addthis_params.img_base+'toolbox-large.png">';
                } else {
                    abvimg += '<div class="addthis_toolbox addthis_default_style addthis_32x32_style">';
                    var serv = element.find('input[name$="[chosen_list]"]').val();
                    var aservice = serv.split(', ');
                    var i;
                    for (i = 0; i < (aservice.length); ++i) {
                        if(aservice[i] == 'counter') {
                            abvimg += '<a class="addthis_counter addthis_bubble_style" style="display: inline-block; float: left;" href="#" tabindex="0"></a>';
                        } else {
                             abvimg += '<span class="at300bs at15nc at15t_'+aservice[i]+' at16t_'+aservice[i]+'" style="display:inline-block;padding-right:4px;vertical-align:middle;"></span>';
                        }
                    }
                    abvimg += '</div>';
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            } else if(element.find('input[value="fb_tw_p1_sc"]').is(':checked')) {
                 if(element.find('input[name$="[chosen_list]"]').val() == "") {
                     abvimg = '<img alt="large_toolbox" src="'+addthis_params.img_base+'horizontal_share_rect.png">';
                 } else {
                    var serv = element.find('input[name$="[chosen_list]"]').val();
                    var aservice = serv.split(', ');
                    var i;
                    for (i = 0; i < (aservice.length); ++i) {
                       if(aservice[i] == 'compact') {
                          abvimg += '<img src="'+addthis_params.img_base+'addthis_pill_style.png">';
                       } else if(aservice[i] != 'counter') {
                           abvimg += '<img src="'+addthis_params.img_base+aservice[i]+'.png">';
                       }
                    }
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            } else if(element.find('input[value="small_toolbox"]').is(':checked')) {
                if(element.find('input[name$="[chosen_list]"]').val() == "") {
                     abvimg = '<img alt="large_toolbox" src="'+addthis_params.img_base+'toolbox-small.png">';
                } else {
                    var serv = element.find('input[name$="[chosen_list]"]').val();
                    var aservice = serv.split(', ');
                    var i;
                    for (i = 0; i < (aservice.length); ++i) {
                        if(aservice[i] == 'counter') {
                           abvimg += '<a class="addthis_counter addthis_bubble_style" style="display: inline-block; float:left;" href="#" tabindex="0"></a>';
                        } else {
                             abvimg += '<span class="at300bs at15nc at15t_'+aservice[i]+' at16t_'+aservice[i]+'" style="display:inline-block;padding-right:4px;vertical-align:middle;"></span>';
                        }
                    }
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            } else if(element.find('input[value="button"]').is(':checked')) {
                abvimg = '<img alt="large_toolbox" src="'+addthis_params.img_base+'horizontal_share.png">';
                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 0);
                element.find('.addthis-widget-smart-sharing-container').hide();
            }

            element.find('input[value="large_toolbox"]').on('click', function() {
                if(element.find('input[name$="[chosen_list]"]').val() != '') {
                    var newserv = Services.revertServices(element);
                    element.find('input[name$="[chosen_list]"]').val(newserv);
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            });

            element.find('input[value="fb_tw_p1_sc"]').on('click', function() {
                if(element.find('input[name$="[chosen_list]"]').val() != '') {
                    var newserv = Services.rewriteServices(element);
                    element.find('input[name$="[chosen_list]"]').val(newserv);
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            });

            element.find('input[value="small_toolbox"]').on('click', function() {
                if(element.find('input[name$="[chosen_list]"]').val() != '') {
                    var newserv = Services.revertServices(element);
                    element.find('input[name$="[chosen_list]"]').val(newserv);
                }

                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            });

            element.find('input[value="button"]').on('click', function() {
                element.find('.addthis_widget_button_set').css('opacity', 1);
                element.find('.addthis-widget-customize-sharing-link').css('opacity', 1);
                element.find('.addthis-widget-smart-sharing-container').show();
            });


            var customString = element.find('input[value="custom_string"]');
            var customStringShow = function(){
                if (customString.is(':checked')) {
                    element.find('.addthis_widget_custom_string_input').removeClass('hidden');
                    element.find('.personalizedMessage').addClass('hidden');
                } else {
                    element.find('.addthis_widget_custom_string_input').addClass('hidden');
                    element.find('.personalizedMessage').removeClass('hidden');
                }
            };

            customStringShow();

            element.find('input[name$="[settings]"]').change(function(){
                customStringShow();
            });

            $('body').on({
                'populatedList': function() {
                    setTimeout(function() {
                        element.find('.sortable .disabled, .sortable .close').tooltip({
                            position: {
                            my: 'left+15 top',
                                at: 'right top',
                                collision: 'none',
                                tooltipClass: 'custom-tooltip-styling'
                            }
                        });

                        element.find('.addthis-widget-smart-sharing-container .sortable .disabled').on('mouseover', function() {
                            element.find('.addthis-widget-smart-sharing-container .horizontal-drag, .addthis-widget-smart-sharing-container .vertical-drag').hide();
                        });

                        element.find('.addthis-widget-smart-sharing-container .sharing-buttons .enabled').on('mouseenter', function() {
                            if($(this).parent().parent().hasClass('sharing-buttons')) {
                                element.find('.addthis-widget-smart-sharing-container .horizontal-drag').show();
                            }
                        });

                        element.find('.addthis-widget-smart-sharing-container .selected-services .enabled').on('mouseenter', function() {
                            element.find('.addthis-widget-smart-sharing-container .vertical-drag').show();
                        });
                    },0);
                }
            });

            return self;
        };

        intialize();
    };

    var Services = {
        'settings': {
            'loadDeferred': $.Deferred(),
            'scriptIncluded': false,
            'thirdPartyButtons': {
                // Exclude list that will exclude certain services from showing up
                'exclude': {
                    'horizontal': [
                        'stumbleupon_badge'
                    ],
                    'vertical': [
                        'pinterest_pinit',
                        'hyves_respect',
                        'stumbleupon_badge'
                    ]
                },
                'services': function() {
                    return [
                        {
                            'service': 'facebook_like',
                            'name': 'Facebook',
                            'linkedService': 'facebook',
                            'icon': 'facebook',
                            'attrs': {
                                'horizontal': 'fb:like:layout="button_count"',
                                'vertical': 'fb:like:layout="box_count"'
                            }
                        },
                        {
                            'service': 'tweet',
                            'name': 'Twitter',
                            'linkedService': 'twitter',
                            'icon': 'twitter',
                            'attrs': {
                                'horizontal': '',
                                'vertical': 'tw:count="vertical"'
                            }
                        },
                        {
                            'service': 'pinterest_pinit',
                            'name': 'Pinterest',
                            'linkedService': 'pinterest_share',
                            'icon': 'pinterest_share',
                            'attrs': {
                                'horizontal': '',
                                'vertical': ''
                            }
                        },
                        {
                            'service': 'google_plusone',
                            'name': 'Google +1',
                            'linkedService': 'google_plusone_share',
                            'icon': 'google_plusone',
                            'attrs': {
                                'horizontal': 'g:plusone:size="medium"',
                                'vertical': 'g:plusone:size="tall"'
                            }
                        },
                        {
                            'service': 'hyves_respect',
                            'name': 'Hyves',
                            'linkedService': 'hyves',
                            'icon': 'hyves',
                            'attrs': {
                                'horizontal': '',
                                'vertical': ''
                            }
                        },
                        {
                            'service': 'linkedin_counter',
                            'name': 'LinkedIn',
                            'linkedService': 'linkedin',
                            'icon': 'linkedin',
                            'attrs': {
                                'horizontal': '',
                                'vertical': 'li:counter="top"'
                            }
                        },
                        {
                            'service': 'stumbleupon_badge',
                            'name': 'Stumbleupon',
                            'linkedService': 'stumbleupon',
                            'icon': 'stumbleupon',
                            'attrs': {
                                'horizontal': '',
                                'vertical': ''
                            }
                        },
                        {
                            'service': 'compact',
                            'name': 'More',
                            'linkedService': 'compact',
                            'icon': 'compact',
                            'attrs': {
                                'horizontal': '',
                                'vertical': ''
                            }
                        }
                    ];
                }
            },
            'totalServices': [],
            'addthisButtons': {
                // Exclude list that will exclude certain services from showing up
                'exclude': [
                    'facebook_like',
                    'pinterest'
                ],
                // All AddThis supported services get pulled in dynamically from a jsonp endpoint
                'services': []
            }
        },

        // Helps Fetch all of the service names
        'loadServices': function loadServices(response) {
            var serviceList = [],
                currentService = '',
                itemCopy,
                duplicateServices = Services.settings.duplicateServices = {},
                checkDuplicateName = {},
                checkDuplicateService = {},
                service,
                thirdPartyButtons = Services.settings.thirdPartyButtons.services();

            $(function() {
                Services.settings.addthisButtons.services = serviceList;
                if((response || {}).data) {
                    for (var i = 0; i < response.data.length; i += 1) {
                        currentService = response.data[i].code;
                        if(currentService === 'pinterest') {
                            service = { service: 'pinterest_share', name: 'Pinterest', icon: 'pinterest_share' };
                        } else {
                            service = { service: currentService, name: response.data[i].name, icon: currentService };
                        }

                        checkDuplicateName['name'] = response.data[i].name;
                        checkDuplicateService['service'] = currentService;
                        if(_.where(thirdPartyButtons, checkDuplicateName).length) {
                            duplicateServices[currentService] = service;
                        }

                        if(!_.where(thirdPartyButtons, checkDuplicateService).length) {
                            serviceList.push(service);
                        }
                    }
                }

                try {
                    if(!_.where(serviceList, { 'service': 'compact' } ).length) {
                        serviceList.push({ service: "compact", name: "More", icon: 'compact' });
                    }
                } catch(e) {
                    console.log(e);
                }

                Services.settings.totalServices = $.merge($.merge([],serviceList), Services.settings.thirdPartyButtons.services());
                Services.settings.disabledServices = _.filter(serviceList, function(service) {
                    return !_.where(Services.settings.thirdPartyButtons.services(), { 'linkedService': service.service }).length;
                });

                Services.settings.loadDeferred.resolve(serviceList);
            });
        },
        'rewriteServices': function rewriteServices(element) {
            var services = element.find('input[name$="[chosen_list]"]').val(),
                service = services.split(', '),
                i,
                newservice = '';
            for (i = 0; i < (service.length); ++i) {
                if(service[i] == 'linkedin') {
                    newservice += 'linkedin_counter, ';
                } else if(service[i] == 'facebook') {
                    newservice += 'facebook_like, ';
                } else if(service[i] == 'twitter') {
                    newservice += 'tweet, ';
                } else if(service[i] == 'pinterest_share') {
                    newservice += 'pinterest_pinit, ';
                } else if(service[i] == 'hyves') {
                    newservice += 'hyves_respect, ';
                } else if(service[i] == 'google_plusone_share') {
                    newservice += 'google_plusone, ';
                } else if(service[i] == 'counter' || service[i] == 'compact') {
                    newservice += service[i]+', ';
                }
            }
            var newservices = newservice.slice(0,-2);
            return newservices;
        },
        'revertServices': function revertServices(element) {
            var services = element.find('input[name$="[chosen_list]"]').val(),
                service = services.split(', '),
                i,
                newservice = '';
            for (i = 0; i < (service.length); ++i) {
                if(service[i] == 'facebook_like') {
                    newservice += 'facebook, ';
                } else if(service[i] == 'linkedin_counter') {
                    newservice += 'linkedin, ';
                } else if(service[i] == 'hyves_respect') {
                    newservice += 'hyves, ';
                } else if(service[i] == 'google_plusone') {
                    newservice += 'google_plusone_share, ';
                } else if(service[i] == 'tweet') {
                    newservice += 'twitter, ';
                } else if(service[i] == 'pinterest_pinit') {
                    newservice += 'pinterest_share, ';
                } else {
                    newservice += service[i]+', ';
                }
            }
            var newservices = newservice.slice(0,-2);
            return newservices;
        }
    };

window.loadServices = function(services) {
    Services.loadServices(services);
};


    $.fn.addthisWidget = function(method) {
        var argv = arguments;
        return this.each(function() {
            /**
             * Method calling logic
             */
            if ($.data(this, 'addthisWidget') && $.data(this, 'addthisWidget')[method] ) {
                return $.data(this, 'addthisWidget')[method].apply(this, Array.prototype.slice.call(argv, 1));
            } else if (typeof method === 'object' || ! method) {
                if (!$.data(this, 'addthisWidget')) {
                    var addthisWidget = new AddthisWidget($(this), method);
                    $(this).data('addthisWidget', addthisWidget);
                }
            } else {
                $.error('Method ' +  method + ' does not exist on jQuery.addthisWidget');
            }
        });
    };
})(jQuery);