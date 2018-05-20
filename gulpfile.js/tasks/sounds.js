let gulp = require('gulp');
let config = require('../config.json');

gulp.task('move-sounds', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    gulp.src(cfg.base.src + 'sounds/**/*')
        .pipe(gulp.dest(cfg.base.dest + 'sounds'));

});
