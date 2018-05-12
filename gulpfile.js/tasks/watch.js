let gulp = require('gulp');
let config = require('../config.json');

gulp.task('watch', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    gulp.watch(cfg.base.src + 'sass/**/*.scss', ['sass']);
    gulp.watch(cfg.base.src + 'js/**/*.js', ['javascripts']);

});
