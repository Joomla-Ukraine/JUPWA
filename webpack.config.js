"use strict";

const webpack = require('webpack'),
    path = require('path'),
    yargs = require('yargs/yargs');

const {hideBin} = require('yargs/helpers'),
    argv = yargs(hideBin(process.argv)).argv;

const MiniCssExtractPlugin = require('mini-css-extract-plugin'),
    CssMinimizerPlugin = require('css-minimizer-webpack-plugin'),
    ImageMinimizerPlugin = require("image-minimizer-webpack-plugin"),
    TerserPlugin = require('terser-webpack-plugin'),
    CopyWebpackPlugin = require('copy-webpack-plugin'),
    ReplaceInFileWebpackPlugin = require('replace-in-file-webpack-plugin'),
    {CleanWebpackPlugin} = require('clean-webpack-plugin');

const {version} = require('./package.json'),
    isDevelopment = argv.mode === 'development',
    isProduction = !isDevelopment,
    distPath = path.join(__dirname, './packages/plg_system_jupwa/media/jupwa');

const entry = {
        'jupwa': path.resolve(__dirname, './src/js/index.js'),
        'push': path.resolve(__dirname, './src/js/push.js')
    },
    output = {
        filename: `./js/[name].${version}.js`,
        path: distPath,
        chunkFilename: `./js/[name].${version}.js`,
        pathinfo: false
    };

const cleanDirs = [
        '**/packages/plg_system_jupwa/media/jupwa/js/*',
        '**/packages/plg_system_jupwa/media/jupwa/css/*'
    ],
    copyFiles = {
        patterns: [
            {
                from: './src/image',
                to: './image'
            }
        ]
    };

const rulesStyle = {
        test: /\.(sa|sc|c)ss$/,
        use: [
            MiniCssExtractPlugin.loader,
            {
                loader: 'css-loader',
                options: {sourceMap: false}
            },
            {
                loader: 'postcss-loader',
                options: {sourceMap: false}
            },
            {
                loader: 'sass-loader',
                options: {
                    sourceMap: false,
                    sassOptions: {
                        quietDeps: true
                    }
                }
            }
        ]
    },
    rulesImg = {
        test: /\.(jpe?g|png|gif|svg|webp|avif)$/,
        type: 'asset/resource',
        generator: {
            filename: 'img/[name][ext][query]'
        }
    },
    rulesStyleDev = {
        test: /\.(sa|sc|c)ss$/,
        use: [
            'style-loader',
            {
                loader: 'css-loader'
            },
            {
                loader: 'sass-loader',
                options: {
                    sassOptions: {
                        quietDeps: true
                    }
                }
            }
        ],
    };

const pluginClean = new CleanWebpackPlugin({
        default: cleanDirs
    }),
    pluginMiniCss = new MiniCssExtractPlugin({
        filename: `./css/app.[name].${version}.css`,
        chunkFilename: `./css/app.[name].${version}.css`
    }),
    pluginMCP = new webpack.optimize.ModuleConcatenationPlugin(),
    pluginCopy = new CopyWebpackPlugin(copyFiles),
    pluginReplace = new ReplaceInFileWebpackPlugin([
        {
            dir: path.join(__dirname, '/packages/plg_system_jupwa'),
            files: ['jupwa.php'],
            rules: [
                {
                    search: /\$jupwa_js_version = '(.*?)';/ig,
                    replace: '$jupwa_js_version = \'' + version + '\';'
                }
            ]
        }
    ]),
    pluginImageMin = new ImageMinimizerPlugin({
        minimizer: [
            {
                implementation: ImageMinimizerPlugin.imageminMinify,
                options: {
                    plugins: [
                        ["gifsicle", {interlaced: true}],
                        ["jpegtran", {progressive: true}],
                        ["optipng", {optimizationLevel: 5}],
                    ],
                },
            },
            {
                implementation: ImageMinimizerPlugin.svgoMinify,
                options: {
                    encodeOptions: {
                        multipass: true,
                        plugins: [
                            {
                                name: 'preset-default',
                                params: {
                                    overrides: {
                                        removeViewBox: false
                                    },
                                },
                            }
                        ],
                    },
                },
            },
        ],
    }),
    pluginTerser = new TerserPlugin({
        terserOptions: {
            compress: true,
            mangle: true,
            format: {
                comments: false
            }
        },
        parallel: true,
        extractComments: false,
    });

const watchOptions = {
    aggregateTimeout: 200,
    poll: 1000,
    stdin: true,
    ignored: [
        'node_modules'
    ]
};

const configProd = {
    mode: 'production',
    entry: entry,
    target: ['web', 'es2022'],
    output: output,
    cache: {
        type: "filesystem"
    },
    devtool: false,
    performance: {
        hints: false,
        maxEntrypointSize: 512000,
        maxAssetSize: 512000,
    },
    module: {
        rules: [
            rulesStyle,
            rulesImg
        ]
    },
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('production')
            }
        }),
        pluginClean,
        pluginMiniCss,
        pluginMCP,
        pluginCopy,
        pluginReplace
    ],
    optimization: {
        moduleIds: "deterministic",
        nodeEnv: 'production',
        removeAvailableModules: true,
        usedExports: true,
        minimize: true,
        minimizer: [
            pluginTerser,
            pluginImageMin,
            new CssMinimizerPlugin()
        ]
    },
    stats: 'errors-only'
};

const configDev = {
    mode: 'development',
    entry: entry,
    target: ['web', 'es2020'],
    output: output,
    cache: {
        type: "filesystem"
    },
    devtool: 'eval-cheap-module-source-map',
    watchOptions: watchOptions,
    module: {
        rules: [
            rulesStyleDev
        ]
    },
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('development')
            }
        }),
        pluginMiniCss,
        pluginReplace,
        pluginMCP
    ],
    optimization: {
        moduleIds: "deterministic",
        nodeEnv: 'development',
        removeAvailableModules: true,
        usedExports: false,
        minimize: false
    }
};

if (isProduction) {
    module.exports = configProd;
} else {
    module.exports = configDev;
}