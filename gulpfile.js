const gulp = require('gulp');
const sass = require('gulp-sass');
const browserSync = require('browser-sync').create();
const proxyUrl = 'https://babylon.test';
let rootDir = './';
// compile scss into css
function style() {
    // 1. where is the scss file
    return gulp.src(rootDir + 'themes/site_themes/**/scss/*.scss')
    // 2. pass that file through the sass compiler
        .pipe(sass())
        // 3. save the compiled css to css compile.
        .pipe(rename, function(path){
            path.dirname += "/../css";
        })
        .pipe(gulp.dest(rootDir)) // @todo later fix back to same theme folder
        .pipe(browserSync.reload({stream:true}));
}

// trigger the
exports.style = style;

// Start BrowserSync
function watch() {
    browserSync.init({
        proxy: proxyUrl
    });
    gulp.watch('./themes/site_themes/**/scss/*.scss', style);
    gulp.watch('./*').on('change', browserSync.reload);
}

exports.watch = watch;
