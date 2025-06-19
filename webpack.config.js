const path = require('path');
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')

    .enableStimulusBridge('./assets/controllers.json')
    .enableSassLoader()
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .addAliases({'tabulator-tables': path.resolve(__dirname, 'node_modules/tabulator-tables')});

module.exports = Encore.getWebpackConfig();