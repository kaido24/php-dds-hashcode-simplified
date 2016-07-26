"use strict";

var gulp = require('gulp'),
    gulpFilter = require('gulp-filter'),
    mainBowerFiles = require('main-bower-files'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat');

var bowerHome = './bower_components/',
    targetBase = './example/web/assets/';

gulp.task('bower-main-files', function () {
    var fontFilter = gulpFilter('*glyph*'),
        cssFilter = gulpFilter('**/*.css'),
        jsFilter = gulpFilter('**/*.js');

    gulp.src(mainBowerFiles())
        .pipe(jsFilter)
        .pipe(gulp.dest(targetBase + 'js'))
        .pipe(jsFilter.restore())
        .pipe(cssFilter)
        .pipe(gulp.dest(targetBase + 'css'))
        .pipe(cssFilter.restore())
        .pipe(fontFilter)
        .pipe(gulp.dest(targetBase + 'fonts'))
});

gulp.task('none-bower-libs', function () {
    var libName = 'native-promise-only';
    gulp.src([bowerHome + libName + '/build.js'])
        .pipe(rename({
            basename: libName
        }))
        .pipe(gulp.dest(targetBase + 'js'))
});

gulp.task('default', ['bower-main-files', 'none-bower-libs']);
