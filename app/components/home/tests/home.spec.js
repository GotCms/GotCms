"use strict";

describe("Home module", function () {

    beforeEach(module("GotCms.Home"));

    describe("HomeCtrl", function() {
        var scope, ctrl;

        beforeEach(inject(function($rootScope, $controller) {
            scope = $rootScope.$new();
            ctrl  = $controller("HomeCtrl", {$scope: scope});
        }));

        it('ensure the title is set', function() {
            expect(ctrl.title).toBe("This is the home view!");
        });
    });
});
