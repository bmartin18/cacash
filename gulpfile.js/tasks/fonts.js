let gulp = require('gulp');
let config = require('../config.json');

gulp.task('move-fonts', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    gulp.src(cfg.base.src + 'sass/fonts/**/*')
        .pipe(gulp.dest(cfg.base.dest + 'fonts'));

});
