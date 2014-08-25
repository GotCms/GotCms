"use strict";

var route = angular.module("GcContent");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/content", {
                controller: "GcContentCtrl",
                controllerAs: "Content",
                templateUrl: "components/gccontent/partials/index.html",
                label: "CONTENT"
            });
    }
]);
