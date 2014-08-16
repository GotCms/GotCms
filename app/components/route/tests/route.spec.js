"use strict";

describe("Route module", function () {

    beforeEach(module("GotCms.Route"));

    var route;

    beforeEach(inject(function($route) {
        route = $route;
    }));

    it("ensure home route settings", function() {
        expect(route.routes["/"].controller)
            .toBe("HomeCtrl");
        expect(route.routes["/"].controllerAs)
            .toBe("Home");
        expect(route.routes["/"].templateUrl)
            .toBe("components/home/partials/index.html");
    });

    it("ensure sample route settings", function() {
        expect(route.routes["/foo"].controller)
            .toBe("FooCtrl");
        expect(route.routes["/foo"].controllerAs)
            .toBe("Foo");
        expect(route.routes["/foo"].templateUrl)
            .toBe("components/foo/partials/index.html");
    });

    it("ensure otherwise redirect to '/'", function() {
        expect(route.routes[null].redirectTo)
            .toBe("/");
    });
});
