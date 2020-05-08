var gulp = require('gulp');
var $ = require('gulp-load-plugins')();
var settings = require('./config-gulp.js');

// ローカルテスト
gulp.task('test', function(done) {
    gulp.src('')
        .pipe($.phpunit('phpunit --bootstrap ./server/test/bootstrap-mac.php ./server/test'));
    done();
});

// 本番フォルダーにデプロイ
gulp.task('deploy-rel', function(done) {
    var dirs = [
        'v1',
        'v1/lib',
        'src/am1',
        'src/am1/utils',
        'src/config',
        'templates',
    ];
    var files = [
        'src/dependencies.php',
        'src/middleware.php',
        'src/routes.php',
        'src/settings-app.php',
        'src/settings-rel.php',
    ];
    // フォルダーをアップロード
    for (var dir in dirs)
    {
        gulp.src('server/'+dirs[dir]+'/*')
            .pipe($.ftp({
                host: settings.FTP_URL,
                user: settings.FTP_USER,
                pass: settings.FTP_PASS,
                remotePath: settings.FTP_REMOTE_PATH+'/'+dirs[dir]
            }));
    }
    // ファイルをアップロード
    for (var file in files)
    {
        gulp.src('server/'+files[file])
            .pipe($.ftp({
                host: settings.FTP_URL,
                user: settings.FTP_USER,
                pass: settings.FTP_PASS,
                remotePath: settings.FTP_REMOTE_PATH+'/src'
            }));
    }
    // 必要なファイルをアップロード
    gulp.src('server/src/config/config-rel.php')
        .pipe($.rename('config.php'))
        .pipe($.ftp({
            host: settings.FTP_URL,
            user: settings.FTP_USER,
            pass: settings.FTP_PASS,
            remotePath: settings.FTP_REMOTE_PATH+'/src/config'
        }));
    done();
});

// 開発フォルダーにデプロイ
gulp.task('deploy-dev', function(done) {
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
    // 必要なファイルをアップロード
    gulp.src('server/src/config/config-dev.php')
        .pipe($.rename('config.php'))
        .pipe($.ftp({
            host: settings.FTP_URL,
            user: settings.FTP_USER,
            pass: settings.FTP_PASS,
            remotePath: settings.FTP_REMOTE_PATH+'/src/config'
        }));
    done();
});

// 開発フォルダーにv1フォルダーのみデプロイ
gulp.task('deploy-dev-index', function(done) {
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
    done();
});

// 開発フォルダーにsrcフォルダーのみデプロイ
gulp.task('deploy-dev-src', function(done) {
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
    done();
});
