'use strict';

var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var sass = require('gulp-sass');

// URL used for serving content
var serveUrl = "localhost:8000/wp-admin/options-general.php?page=external-login";

sass.compiler = require('node-sass');

gulp.task('sass', function () {
  return gulp.src('./plugin-files/styles/style.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./plugin-files/styles'))
    .pipe(browserSync.stream());
});

gulp.task('serve', ['sass'], function() {
  browserSync.init({
    proxy: serveUrl
  });

  gulp.watch('./plugin-files/styles/**/*.scss', ['sass']);
  gulp.watch("./plugin-files/*.php").on('change', browserSync.reload);
});

gulp.task('watch', function () {
  gulp.watch('./plugin-files/styles/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'serve']);