"use strict";

describe("GcBackend module", function () {

    beforeEach(module("GcBackend"));

    describe("GcBackendCtrl", function() {
        var scope, ctrl;

        beforeEach(inject(function($rootScope, $controller) {
            scope = $rootScope.$new();
            ctrl  = $controller("GcBackendCtrl", {$scope: scope});
        }));

        it("should say something", function() {

        });
    });
});
