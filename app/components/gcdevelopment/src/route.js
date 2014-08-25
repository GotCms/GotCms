"use strict";

var route = angular.module("GcDevelopment");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/development", {
                controller: "GcDevelopmentCtrl",
                controllerAs: "Development",
                templateUrl: "components/gcdevelopment/partials/index.html",
                label: "DEVELOPMENT"
            });
    }
]);
