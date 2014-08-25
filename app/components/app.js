"use strict";

var gotcms = angular.module("GotCms", [
    "ngResource",
    "ngRoute",
    "ng-breadcrumbs",
    "http-auth-interceptor",
    "pascalprecht.translate",
    "GcBackend",
    "GcContent",
    "GcConfig",
    "GcDevelopment",
    "GcModules",
    "GcStats"
]);

gotcms.config(["$provide", "$httpProvider", function ($provide, $httpProvider) {
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

gotcms.config(['$translateProvider', function ($translateProvider) {
    $translateProvider.useStaticFilesLoader({
        prefix: 'languages/',
        suffix: '.json'
    });

    $translateProvider.preferredLanguage('en_GB');
}]);

gotcms.directive("gcBreadcrumbs", ["$rootScope", "breadcrumbs", function($rootScope, breadcrumbs) {
    $rootScope.breadcrumbs = breadcrumbs;
    return {
        restrict: "AE",
        templateUrl: "components/gcbackend/partials/breadcrumbs.html"
    };
}]);
