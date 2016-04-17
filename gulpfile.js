var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var settings = require('./config-gulp.js');

// ローカルテスト
gulp.task('test', function() {
    gulp.src('')
        .pipe($.phpunit('phpunit --bootstrap ./server/test/bootstrap-mac.php ./server/test'));
});

// 開発フォルダーにデプロイ
gulp.task('deploy-dev', function() {
    var dirs = [
        'v1',
        'v1/lib',
        'src',
        'src/am1',
        'src/am1/utils',
        'src/config',
        'templates',
    ];
    for (var dir in dirs)
    {
        gulp.src('server/'+dirs[dir]+'/*')
            .pipe($.ftp({
                host: settings.FTP_URL,
                user: settings.FTP_USER,
                pass: settings.FTP_PASS,
                remotePath: settings.FTP_REMOTE_DEV_PATH+'/'+dirs[dir]
            }));
    }
});

// 開発フォルダーにデプロイ
gulp.task('deploy-dev-index', function() {
    var dirs = [
        'v1',
    ];
    for (var dir in dirs)
    {
        gulp.src('server/'+dirs[dir]+'/*')
            .pipe($.ftp({
                host: settings.FTP_URL,
                user: settings.FTP_USER,
                pass: settings.FTP_PASS,
                remotePath: settings.FTP_REMOTE_DEV_PATH+'/'+dirs[dir]
            }));
    }
});

// 開発フォルダーにデプロイ
gulp.task('deploy-dev-src', function() {
    var dirs = [
        'src',
    ];
    for (var dir in dirs)
    {
        gulp.src('server/'+dirs[dir]+'/*')
            .pipe($.ftp({
                host: settings.FTP_URL,
                user: settings.FTP_USER,
                pass: settings.FTP_PASS,
                remotePath: settings.FTP_REMOTE_DEV_PATH+'/'+dirs[dir]
            }));
    }
});
