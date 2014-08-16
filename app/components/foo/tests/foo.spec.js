"use strict";

describe("Foo module", function () {

    beforeEach(module("GotCms.Foo"));

    describe("FooCtrl", function() {
        var scope, ctrl;

        beforeEach(inject(function($rootScope, $controller) {
            scope = $rootScope.$new();
            ctrl  = $controller("FooCtrl", {$scope: scope});
        }));

        it("ensure the title is set", function() {
            expect(ctrl.title).toBeDefined();
            expect(ctrl.title).toBe("This is another view!");
        });
    });
});
