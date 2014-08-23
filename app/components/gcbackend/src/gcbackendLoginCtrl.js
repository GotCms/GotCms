"use strict";

var gcbackend = angular.module("GcBackend");

gcbackend.controller("GcBackendLoginCtrl", ["$rootScope", "$http", function($rootScope, $http) {
    $rootScope.pageType = "onePage";
}]);
