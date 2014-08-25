"use strict";

var route = angular.module("GcStats");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/statistics", {
                controller: "GcStatsCtrl",
                controllerAs: "Stats",
                templateUrl: "components/gcstats/partials/index.html",
                label: "STATS"
            });
    }
]);
