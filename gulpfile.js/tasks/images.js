let gulp = require('gulp');
let config = require('../config.json');

gulp.task('move-images', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    gulp.src(cfg.base.src + 'images/**/*')
        .pipe(gulp.dest(cfg.base.dest + 'images'));

});
