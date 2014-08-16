"use strict";

describe("angular-skeleton homepage", function() {
    beforeEach(function() {
        browser.get("http://localhost:8001");
    });

    it("should redirect to '/'", function() {
        expect(browser.getCurrentUrl()).toEqual("http://localhost:8001/#/");

        browser.get("http://localhost:8001/#/nonexistantroute");
        expect(browser.getCurrentUrl()).toEqual("http://localhost:8001/#/");
    });

    it("should have a title", function() {
        expect(browser.getTitle()).toEqual("Angular-Skeleton");
    });

    it("should go to foo page", function() {
        var link = element(by.id("foo-link"));

        link.click();
        expect(browser.getCurrentUrl()).toEqual("http://localhost:8001/#/foo");
    });

    it("should go to homepage", function() {
        var link = element(by.id("home-link"));

        link.click();
        expect(browser.getCurrentUrl()).toEqual("http://localhost:8001/#/");
    });
});