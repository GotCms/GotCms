"use strict";

var route = angular.module("GotCms.Route");

route.config([
    "$routeProvider",
    function($routeProvider) {
        $routeProvider
            .when("/", {
                controller: "HomeCtrl",
                controllerAs: "Home",
                templateUrl: "components/home/partials/index.html"
            })
            .when("/foo", {
                controller: "FooCtrl",
                controllerAs: "Foo",
                templateUrl: "components/foo/partials/index.html"
            })
            .otherwise({redirectTo: "/"});
    }
]);
