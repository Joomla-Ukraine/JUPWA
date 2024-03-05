"use strict";

const webpack = require('webpack'),
    path = require('path'),
    argv = require('yargs').argv;

const chalk = require("chalk"),
    ProgressBarPlugin = require("progress-bar-webpack-plugin"),
    MiniCssExtractPlugin = require('mini-css-extract-plugin'),
    TerserPlugin = require('terser-webpack-plugin'),
    CopyWebpackPlugin = require('copy-webpack-plugin'),
    ReplaceInFileWebpackPlugin = require('replace-in-file-webpack-plugin'),
    {CleanWebpackPlugin} = require('clean-webpack-plugin');

const {version} = require('./package.json'),
    isDevelopment = argv.mode === 'development',
    isProduction = !isDevelopment,
    distPath = path.join(__dirname, './packages/plg_system_jupwa/media/jupwa');

const entry = {
        jupwa: path.resolve(__dirname, './src/js/index.js')
    },
    output = {
        filename: `./js/[name].${version}.js`,
        path: distPath,
        publicPath: './packages/plg_system_jupwa/media/jupwa',
        chunkFilename: `./js/[name].${version}.js`,
        pathinfo: false
    };

const cleanDirs = [
        '**/packages/plg_system_jupwa/media/jupwa/js/*'
    ],
    copyFiles = {
        patterns: [
            {
                from: './src/image',
                to: './image'
            }
        ]
    };

const rulesJS = {
        test: /\.js$/,
        exclude: /(node_modules)/,
        include: path.resolve(__dirname, 'src/js'),
        use: [
            'thread-loader',
            'babel-loader'
        ]
    },
    rulesStyle = {
        test: /\.(sa|sc|c)ss$/,
        use: [
            'style-loader',
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

const pluginProgressBar = new ProgressBarPlugin({
        format: `  :msg [:bar] ${chalk.green.bold(":percent")} (:elapsed s)`,
    }),
    pluginClean = new CleanWebpackPlugin({
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
                    search: /\$jupwa_install_version = '(.*?)';/ig,
                    replace: '$jupwa_install_version = \'' + version + '\';'
                }
            ]
        }
    ]),
    pluginTerser = new TerserPlugin({
        terserOptions: {
            parse: {
                ecma: 8
            },
            compress: {
                ecma: 5,
                warnings: false,
                comparisons: false,
                inline: 2,
                drop_console: true,
                module: false,
                ie8: false,
                keep_classnames: undefined,
                keep_fnames: true,
                arrows: false,
                collapse_vars: false,
                computed_props: false,
                hoist_funs: false,
                hoist_props: false,
                hoist_vars: false,
                loops: false,
                negate_iife: false,
                properties: false,
                reduce_funcs: false,
                reduce_vars: false,
                switches: false,
                toplevel: false,
                typeofs: false,
                booleans: true,
                if_return: true,
                sequences: true,
                unused: true,
                conditionals: true,
                dead_code: true,
                evaluate: true
            },
            mangle: {
                safari10: true
            },
            output: {
                ecma: 5,
                comments: false
            }
        },
        parallel: 4,
        extractComments: false
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
            rulesJS,
            rulesStyle
        ]
    },
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('production')
            }
        }),
        pluginProgressBar,
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
            pluginTerser
        ]
    },
    stats: 'errors-only'
};

const configDev = {
    mode: 'development',
    entry: entry,
    output: output,
    cache: {
        type: "filesystem"
    },
    devtool: 'eval-cheap-module-source-map',
    watchOptions: watchOptions,
    module: {
        rules: [
            rulesJS,
            rulesStyleDev
        ]
    },
    plugins: [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('development')
            }
        }),
        pluginProgressBar,
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