const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('public').mergeManifest();

// mix.js(__dirname + '/Resources/assets/js/app.js', 'js/cyberfranco.js')

if (mix.inProduction()) {
    mix.version();
}
