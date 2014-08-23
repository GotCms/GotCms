"use strict";

var route = angular.module("GcBackend");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                controller: "GcBackendCtrl",
                controllerAs: "Dashboard",
                templateUrl: "components/gcbackend/partials/index.html"
            })
            .when("/login", {
                controller: "GcBackendLoginCtrl",
                controllerAs: "Login",
                templateUrl: "components/gcbackend/partials/login.html"
            })
            .when("/404", {
                controller: "GcBackend404Ctrl",
                controllerAs: "NotFound",
                templateUrl: "components/gcbackend/partials/404.html"
            })
            .otherwise({redirectTo: "/404"});
    }
]);
