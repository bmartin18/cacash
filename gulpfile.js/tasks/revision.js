let gulp = require('gulp');
let config = require('../config.json');
let rev = require('gulp-rev');
let buffer = require('vinyl-buffer');

gulp.task('revision', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    return gulp.src([cfg.base.dest + 'css/*.css', cfg.base.dest + 'js/*.js'], { base: cfg.base.dest })
        .pipe(buffer())
        .pipe(rev())
        .pipe(gulp.dest(cfg.base.dest))
        .pipe(rev.manifest({
            base: cfg.base.dest,
            path: cfg.base.dest + '/rev-manifest.json',
            merge: true
        }))
        .pipe(gulp.dest(cfg.base.dest))
    ;

});
