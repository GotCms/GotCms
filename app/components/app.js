"use strict";

angular.module("GotCms", [
    "ngResource",
    "ngRoute",
    "http-auth-interceptor",
    "GotCms.GcBackend"
]);

var httpInterceptor = function ($provide, $httpProvider) {
    $provide.factory('httpInterceptor', function ($q) {
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
    });
    $httpProvider.interceptors.push('httpInterceptor');
};

angular.module("GotCms").config(httpInterceptor);
