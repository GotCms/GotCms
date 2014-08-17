"use strict";

var route = angular.module("GotCms.GcBackend");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                controller: "GcBackendCtrl",
                controllerAs: "GcBackend",
                templateUrl: "components/gcbackend/partials/index.html"
            })
            .when("/login", {
                controller: "GcBackendCtrl",
                controllerAs: "Login",
                templateUrl: "components/foo/partials/login.html"
            })
            .otherwise({redirectTo: "/"});
    }
]);
