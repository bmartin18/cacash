let gulp = require('gulp');
let config = require('../config.json');
let browserify = require('browserify');
let babelify = require('babelify');
let source = require('vinyl-source-stream');
let buffer = require('vinyl-buffer');
let uglify = require('gulp-uglify');

gulp.task('javascripts', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    if (global.env === 'prod') {
        return browserify(cfg.base.src + 'js/script.js', cfg.browserify)
            .transform(babelify, cfg.babelify)
            .bundle()
            .pipe(source('script.js'))
            .pipe(buffer())
            .pipe(uglify())
            .pipe(gulp.dest(cfg.base.dest + 'js'))
        ;
    }

    return browserify(cfg.base.src + 'js/script.js', cfg.browserify)
        .transform(babelify, cfg.babelify)
        .bundle()
        .pipe(source('script.js'))
        .pipe(buffer())
        .pipe(gulp.dest(cfg.base.dest + 'js'))
    ;

});
