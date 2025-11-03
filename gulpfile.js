const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const babel = require('gulp-babel');
const del = require('del');

// Paths
const paths = {
  sass: {
    src: 'sass/**/*.scss',
    dest: 'css/',
    main: 'sass/style.scss'
  },
  js: {
    src: 'js/**/*.js',
    dest: 'js/',
    vendors: 'js/vendors/**/*.js'
  },
  clean: ['css/*.css', 'css/*.css.map', '!css/vendors.css']
};

// Clean CSS files
function clean() {
  return del(paths.clean);
}

// Compile SCSS to CSS
function compileSass() {
  return gulp.src(paths.sass.main)
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['sass']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest))
    .pipe(cleanCSS({
      compatibility: 'ie8'
    }))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest));
}

// Compile responsive SCSS
function compileResponsive() {
  return gulp.src('sass/responsive.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['sass']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest))
    .pipe(cleanCSS({
      compatibility: 'ie8'
    }))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest));
}

// Compile icon SCSS
function compileIcon() {
  return gulp.src('sass/icon.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['sass']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest))
    .pipe(cleanCSS({
      compatibility: 'ie8'
    }))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest));
}

// Compile vendors SCSS
function compileVendors() {
  return gulp.src('sass/vendors.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['sass']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest))
    .pipe(cleanCSS({
      compatibility: 'ie8'
    }))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.sass.dest));
}

// Compile RTL CSS
function compileRTL() {
  return gulp.src('sass/style-rtl.scss')
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      precision: 10,
      includePaths: ['sass']
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('rtl-css/'))
    .pipe(cleanCSS({
      compatibility: 'ie8'
    }))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('rtl-css/'));
}

// Process JavaScript files
function processJS() {
  return gulp.src(['js/main.js'])
    .pipe(babel({
      presets: ['@babel/preset-env']
    }))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(paths.js.dest));
}

// Process vendor JavaScript files
function processVendorJS() {
  return gulp.src(paths.js.vendors)
    .pipe(concat('vendors.js'))
    .pipe(gulp.dest(paths.js.dest))
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(paths.js.dest));
}

// Watch files for changes
function watchFiles() {
  gulp.watch(paths.sass.src, gulp.series(compileSass, compileResponsive, compileIcon, compileVendors, compileRTL));
  gulp.watch(['js/main.js'], processJS);
  gulp.watch(paths.js.vendors, processVendorJS);
}

// Build all files
const build = gulp.series(
  clean,
  gulp.parallel(
    compileSass,
    compileResponsive,
    compileIcon,
    compileVendors,
    compileRTL,
    processJS,
    processVendorJS
  )
);

// Watch task
const watch = gulp.series(
  build,
  watchFiles
);

// Export tasks
exports.clean = clean;
exports.sass = gulp.parallel(compileSass, compileResponsive, compileIcon, compileVendors, compileRTL);
exports.js = gulp.parallel(processJS, processVendorJS);
exports.watch = watch;
exports.build = build;
exports.default = build;
