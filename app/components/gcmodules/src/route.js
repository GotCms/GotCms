"use strict";

var route = angular.module("GcModules");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/module", {
                controller: "GcModulesCtrl",
                controllerAs: "Modules",
                templateUrl: "components/gcmodules/partials/index.html",
                label: "MODULES"
            });
    }
]);
