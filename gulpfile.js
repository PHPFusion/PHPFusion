const gulp = require('gulp');
const browserSync = require('browser-sync').create();
const proxyUrl = 'https://babylon.test';
//let rootDir = './';

const
    // source folders for development
    dir = {
        admin_theme_dir: 'themes/admin_themes/',
        theme_dir: 'themes/site_themes/',
        infusion_dir : 'infusions/',
        jquery_dir: 'includes/jquery',
        jscripts_dir: 'includes/jscripts',
    };


// Start BrowserSync
function watchBrowser() {
    browserSync.init({
        proxy: proxyUrl,
        notify: false,
        https: {
            key: 'cert/cert.key',
            cert: 'cert/cert.crt'
        }
    });
    // watch twig files
    gulp.watch(dir.admin_theme_dir +'**/templates/*.twig').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/templates/*.twig').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/templates/*.twig').on('change', browserSync.reload);
    // watch php files
    gulp.watch(dir.admin_theme_dir +'**/*.php').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/*.php').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/*.php').on('change', browserSync.reload);
    // watch less files
    gulp.watch(dir.admin_theme_dir +'**/less/*.less').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/less/*.less').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/less/*.less').on('change', browserSync.reload);
    // watch html files
    gulp.watch(dir.admin_theme_dir +'**/templates/*.html').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/templates/*.html').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/templates/*.html').on('change', browserSync.reload);
    // watch js files
    gulp.watch(dir.admin_theme_dir +'**/js/*.js').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/js/*.js').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/js/*.js').on('change', browserSync.reload);
    // watch project jquery files
    gulp.watch(dir.jquery_dir +'**/*.js').on('change', browserSync.reload);
}

exports.watch = watchBrowser;
