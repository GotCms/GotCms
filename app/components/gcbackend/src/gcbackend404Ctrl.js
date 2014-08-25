"use strict";

var gcbackend = angular.module("GcBackend");

gcbackend.controller("GcBackend404Ctrl", ["$rootScope", function($rootScope) {
    $rootScope.pageType = "onePage";
}]);
