const gulp = require('gulp');
const sass = require('gulp-sass');
const postcss = require('gulp-postcss');
const sourcemaps = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const babel = require('gulp-babel');
const {series, parallel} = require('gulp');

function style() {
    return gulp.src('./assets/scss/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('./public/dist'));
}

exports.style = style;

function js() {
    return gulp.src('./assets/js/**/*.js')
        .pipe(gulp.src('./node_modules/selectize/dist/js/standalone/selectize.js'))
        .pipe(sourcemaps.init())
        .pipe(concat('main.js'))
        .pipe(gulp.dest('./public/dist'))
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(sourcemaps.write('/map'))
        .pipe(gulp.dest('./public/dist'));
}

function watch() {
    gulp.watch('./assets/scss/**/*scss', style);
    gulp.watch('./assets/js/**/*js').on('change', js);
}

function images() {
    return gulp.src('./assets/img/*')
        .pipe(gulp.dest('./public/dist/img'));
}

function fonts() {
    return gulp.src('./node_modules/tabler-ui/src/assets/fonts/**/*')
        .pipe(gulp.dest('./public/fonts'));
}

exports.js = js;
exports.style = style;
exports.watch = watch;
exports.images = images;
exports.fonts = fonts;

exports.default = series(js, style, images, fonts);
exports.dev = series(js, style, images, fonts, watch);
