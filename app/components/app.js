"use strict";

angular.module("GotCms", [
    "ngResource",
    "ngRoute",
    "ng-breadcrumbs",
    "http-auth-interceptor",
    "pascalprecht.translate",
    "GcBackend"
]);

angular.module("GotCms").config(["$provide", "$httpProvider", function ($provide, $httpProvider) {
    $provide.factory('httpInterceptor', ["$q", function ($q) {
        return {
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
