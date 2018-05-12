let gulp = require('gulp');
let config = require('../config.json');
let del = require('del');

gulp.task('clean', function() {

    let cfg = global.env === 'prod' ? config.prod : config.dev;

    return del(cfg.base.dest);

});
