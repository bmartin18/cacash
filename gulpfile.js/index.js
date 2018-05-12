let gulp = require('gulp');
let requireDir = require('require-dir');
let runSequence = require('run-sequence');

let tasks = requireDir('./tasks', { recurse: true });

global.env = 'dev';

gulp.task('dev', ['dev-nowatch', 'watch']);

gulp.task('dev-nowatch', ['move-fonts', 'move-images', 'sass', 'javascripts']);

gulp.task('set-prod-env', function() {
    global.env = 'prod';
});

gulp.task('prod', function() {
    runSequence('set-prod-env', ['clean'], ['front-nowatch'], ['revision']);
});
