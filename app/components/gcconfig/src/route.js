"use strict";

var route = angular.module("GcConfig");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/config", {
                controller: "GcConfigCtrl",
                controllerAs: "Config",
                templateUrl: "components/gcdevelopment/partials/index.html",
                label: "CONFIG"
            });
    }
]);
