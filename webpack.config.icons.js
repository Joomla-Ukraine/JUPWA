"use strict";

const path = require('path');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');

const distPath = path.join(__dirname, './packages/plg_system_jupwa/media/jupwa');

const config = {
    entry: {
        sprite: './src/js/sprite.js'
    },
    output: {
        filename: `./sprite/[name].js`,
        path: distPath,
    },
    module: {
        rules: [
            {
                test: /\.svg$/,
                type: 'asset',
                use: [
                    {
                        loader: 'svg-sprite-loader',
                        options: {
                            extract: true,
                            spriteFilename: "icons.svg",
                            outputPath: 'icons/',
                            publicPath: '/app/icons/'
                        }
                    },
                    'svg-transform-loader',
                    'svgo-loader'
                ]
            }
        ]
    },
    plugins: [
        new SpriteLoaderPlugin({plainSprite: true})
    ]
};

module.exports = config;