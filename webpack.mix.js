const mix = require('laravel-mix');

mix.disableSuccessNotifications();
mix.options({
    terser: {
        extractComments: false
    }
});
mix.setPublicPath('dist');
mix.version();

mix.postCss('resources/css/stock.css', 'dist/css', [
    require('postcss-import'),
    require('autoprefixer')
]);
