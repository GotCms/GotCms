"use strict";

var route = angular.module("GotCms.GcBackend");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                controller: "GcBackendCtrl",
                templateUrl: "components/gcbackend/partials/index.html"
            })
            .when("/login", {
                controller: "GcBackendLoginCtrl",
                templateUrl: "components/foo/partials/login.html"
            })
            .otherwise({redirectTo: "/"});
    }
]);
