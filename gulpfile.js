"use strict";

var gulp       = require("gulp"),
    gutil      = require("gulp-util"),
    rev        = require("gulp-rev"),
    _          = require("lodash"),
    del        = require("del"),
    connect    = require("gulp-connect"),
    jshint     = require("gulp-jshint"),
    stylish    = require("jshint-stylish"),
    uglify     = require("gulp-uglify"),
    sass       = require("gulp-sass"),
    minifyCss  = require("gulp-minify-css"),
    rename     = require("gulp-rename"),
    usemin     = require("gulp-usemin"),
    karma      = require("karma").server,
    spawn      = require("child_process").spawn,
    readline   = require("readline");

var appConf = {
    root: {
        dev: ".tmp/assets",
        integration: "public/backend"
    },
    servers: {
        dev: {
            root: ["app", ".tmp"],
            port: 8000,
            livereload: true
        },
        integration: {
            root: ["public/backend"],
            port: 8001
        }
    },
    files: {
        all: [
            ".tmp/assets/**/*.*",
            "!.tmp/assets/**/*.{js,css}"
        ],
        html: ["app/**/*.html"],
        sass: [
            "app/assets/**/*.scss",
            "app/bower_components/**/*.scss"
        ],
        fonts: [
            "app/**/*.eot",
            "app/**/*.ttf",
            "app/**/*.svg",
            "app/**/*.woff",
        ],
        img: ["app/assets/**/*.{png,jpg,jpeg}"],
        js: {
            all: [
                "gulpfile.js",
                "app/components/**/*.js",
                "!{node_modules,test}/**/*.js",
                "!app/bower_components/**"
            ],
            src: ["app/{app,**/src/*}.js"],
            test: ["app/**/tests/*.js"]
        }
    }
};

var karmaConf = {
    browsers: ["PhantomJS"],
    frameworks: ["jasmine"],
    files: [
        "app/bower_components/angular/angular.js",
        "app/bower_components/angular-route/angular-route.js",
        "app/bower_components/angular-mocks/angular-mocks.js",
        "app/bower_components/angular-http-auth/src/http-auth-interceptor.js",
        "app/components/app.js",
        "app/components/**/src/*.js",
        "app/components/**/tests/*.spec.js"
    ]
};

gulp.task("karma", function(done) {
    karma.start(_.assign({}, karmaConf, {singleRun: true}), done);
});

gulp.task("devServer", function() {
    connect.server(appConf.servers.dev);
});

gulp.task("integrationServer", function() {
    connect.server(appConf.servers.integration);
});

gulp.task("cleanTmp", function(callback) {
    del([".tmp"], {"sync": true}, callback);
});

gulp.task("cleanBackend", function(callback) {
    del([appConf.root.integration], callback);
});

gulp.task("html", function() {
    return gulp.src(appConf.files.html)
        .pipe(connect.reload());
});

gulp.task("images", ["cleanTmp"], function() {
    return gulp.src(appConf.files.img)
        .pipe(gulp.dest(appConf.root.dev))
        .pipe(connect.reload());
});

gulp.task("sass", ["cleanTmp"], function() {
    return gulp.src(appConf.files.sass)
        .pipe(sass())
        .pipe(gulp.dest(appConf.root.dev))
        .pipe(connect.reload());
});

gulp.task("fonts", ["cleanTmp"], function() {
    return gulp.src(appConf.files.fonts)
        .pipe(rename(function(path) {
            path.dirname = path.dirname.replace('bower_components', '');
        }))
        .pipe(gulp.dest(appConf.root.dev))
        .pipe(connect.reload());
});

gulp.task("jshint", function() {
    gulp.src(appConf.files.js.all)
        .pipe(jshint())
        .pipe(jshint.reporter(stylish));
});

gulp.task("js", ["karma"], function() {
    return gulp.src(appConf.files.js.src)
        .pipe(connect.reload());
});

gulp.task("watch", function() {
    gulp.watch(appConf.files.html, ["html"]);
    gulp.watch(appConf.files.js.all, ["jshint"]);
    gulp.watch(appConf.files.js.test, ["karma"]);
    gulp.watch(appConf.files.js.src, ["js"]);
    gulp.watch(appConf.files.fonts, ["fonts"]);
    gulp.watch(appConf.files.img, ["images"]);
    gulp.watch(appConf.files.sass, ["sass"]);
    gulp.watch(appConf.files.fonts, ["fonts"]);
});

gulp.task("webdriver-start", function() {
    var webdriver = spawn(
        "node_modules/protractor/bin/webdriver-manager",
        ["start"]
    );

    readline.createInterface({
        input   : webdriver.stdout,
        terminal: false
    }).on("line", function(line) {
        gutil.log(line);
    });
});

gulp.task("e2e", ["build", "integrationServer"], function() {
    var protractor = spawn(
        "node_modules/protractor/bin/protractor",
        ["app/test/protractor.conf.js"]
    );

    readline.createInterface({
        input : protractor.stdout,
        terminal: false
    }).on("line", function(line) {
        gutil.log(line);
    });

    return protractor.on("exit", function(exitCode) {
        connect.serverClose();
        return exitCode;
    });
});

gulp.task("build", ["cleanBackend", "sass", "fonts", "images"], function() {
    gulp.src(appConf.files.all)
        .pipe(rename(function(path) {
            path.dirname = path.dirname.replace(/^([^\/]*)(.*)$/, '$2');
        }))
        .pipe(gulp.dest(appConf.root.integration));

    gulp.src(appConf.files.img)
        .pipe(gulp.dest(appConf.root.integration + '/assets'));

    return gulp.src(appConf.files.html)
        .pipe(usemin({
            css: [minifyCss(), rev()],
            js: [uglify(), rev()]
        }))
        .pipe(gulp.dest(appConf.root.integration));
});

gulp.task("default", ["devServer", "watch", "images", "sass", "fonts", "js", "html"]);
