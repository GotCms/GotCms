"use strict";

var route = angular.module("GotCms.GcBackend");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                controller: "GcBackendCtrl",
                action: "admin",
                templateUrl: "components/gcbackend/partials/index.html"
            })
            .when("/login", {
                controller: "GcBackendLoginCtrl",
                action: "login",
                templateUrl: "components/gcbackend/partials/login.html"
            })
            .when("/404", {
                controller: "GcBackend404Ctrl",
                controllerAs: "404",
                templateUrl: "components/gcbackend/partials/404.html"
            })
            .otherwise({redirectTo: "/404"});
    }
]);
