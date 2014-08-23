"use strict";

angular.module("GotCms", [
    "ngResource",
    "ngRoute",
    "http-auth-interceptor",
    "pascalprecht.translate",
    "GcBackend"
]);

angular.module("GotCms").config(["$provide", "$httpProvider", function ($provide, $httpProvider) {
    $provide.factory('httpInterceptor', ["$q", function ($q) {
        return {
            request: function (config) {
                /** @TODO get backend url **/
                if (!/^\/?(components|languages)/.test(config.url)) {
                    config.url = 'http://got-cms.dev/' + config.url.replace(/^\//g, '');
                }

                return config;
            },
            response: function (response) {
                return response || $q.when(response);
            },
            responseError: function (response) {
                // if(response.status === 401) {
                //     response.status === 401;
                // }

                return $q.reject(response);
            }
        };
    }]);
    $httpProvider.interceptors.push('httpInterceptor');
}]);

angular.module("GotCms").config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        prefix: 'languages/',
        suffix: '.json'
    });

    $translateProvider.preferredLanguage('en_GB');
}]);
