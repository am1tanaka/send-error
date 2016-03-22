var gulp = require('gulp');
var $ = require('gulp-load-plugins')();

gulp.task('test', function() {
    gulp.src('')
        .pipe($.phpunit('phpunit ./server/test'));
});
