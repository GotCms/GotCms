"use strict";

var gcbackend = angular.module("GcBackend");

gcbackend.controller("GcBackendCtrl", ["$scope", "$http", "$rootScope", function($scope, $http, $rootScope) {
    $rootScope.moduleName = 'gcbackend';

    $http.get('/backend/dashboard').success(function(data) {
        angular.forEach(data, function(value, key) {
            $scope[key] = value;
        });
    });
}]);
