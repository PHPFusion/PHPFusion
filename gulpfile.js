const gulp = require('gulp');
const browserSync = require('browser-sync').create();
const gulpless = require('gulp-less');
const gulpautoprefixer = require('gulp-autoprefixer');
const gulpsourcemaps= require('gulp-sourcemaps');
const gulpyui = require('gulp-yuicompressor');
const proxyUrl = 'https://babylon.test';
const themeName = 'FusionTheme';
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

function yuicompress() {
    let srcpath = dir.jscripts_dir;
    return gulp.src(srcpath)
        .pipe(gulpyui({
        type: 'js'
    }))
        .pipe(gulp.dest('dest'));
}

exports.yuicompress = yuicompress;

function lessc() {
    let srcpath = dir.theme_dir+themeName+'/styles.less';
    let dest = dir.theme_dir+themeName+'/';
    return gulp
        .src(srcpath)
        .pipe(gulpless())
        .pipe(gulpautoprefixer())
        .pipe(gulpsourcemaps.write(dest)) // i did not get this to run
        .pipe(gulp.dest(dest))
        .pipe(browserSync.reload({stream:true}));
}
exports.lessc = lessc;

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
    gulp.watch(dir.jscripts_dir +'**/*.js', yuicompress());
    gulp.watch(dir.admin_theme_dir +'**/templates/*.twig').on('change', browserSync.reload);
    gulp.watch(dir.admin_theme_dir +'**/templates/*.twig').on('change', browserSync.reload);
    //gulp.watch(dir.admin_theme_dir +'**/templates/*.twig', lessc);//.on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/templates/**/*.twig').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'**/templates/*.twig').on('change', browserSync.reload);
    gulp.watch(dir.infusion_dir +'**/templates/*.twig').on('change', browserSync.reload);

    // fusion infusions theme dir
    gulp.watch(dir.theme_dir +'**/fusion/templates/*.twig').on('change', browserSync.reload);
    // fusion theme less file
    gulp.watch(dir.theme_dir +'/*.less').on('change', browserSync.reload);
    gulp.watch(dir.theme_dir +'/**/*.less').on('change', browserSync.reload);

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
