let gulp = require('gulp');
let config = require('../config.json');
let sass = require('gulp-sass');
let cleancss = require('gulp-clean-css');
let autoprefixer = require('gulp-autoprefixer');

gulp.task('sass', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    if (global.env === 'prod') {
        gulp.src(cfg.base.src + 'sass/style.scss')
            .pipe(sass(cfg.sass))
            .pipe(autoprefixer(cfg.autoprefixer))
            .pipe(cleancss())
            .pipe(gulp.dest(cfg.base.dest + 'css/'))
        ;
    } else {
        gulp.src(cfg.base.src + 'sass/style.scss')
            .pipe(sass(cfg.sass))
            .pipe(autoprefixer(cfg.autoprefixer))
            .pipe(gulp.dest(cfg.base.dest + 'css/'))
        ;
    }

});
