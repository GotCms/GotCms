"use strict";

var gcbackend = angular.module("GcBackend");

gcbackend.controller("GcBackendCtrl", ["$scope", "$http", "$rootScope", "breadcrumbs", function($scope, $http, $rootScope, breadcrumbs) {
    $rootScope.moduleName = 'gcbackend';
    $rootScope.breadcrumbs = breadcrumbs;
    console.log(breadcrumbs.get());


    $http.get('/backend/dashboard').success(function(data) {
        angular.forEach(data, function(value, key) {
            $scope[key] = value;
        });
    });
}]);
