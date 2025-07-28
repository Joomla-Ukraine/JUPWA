const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import("tailwindcss").Config} */
module.exports = {
    experimental: {
        optimizeUniversalDefaults: true,
    },
    content: [
        "./src/js/html/**/*.html",
    ],
    safelist: [],
    module: {
        rules: [
            {
                test: /\.scss$/,
                use: [
                    "style-loader",
                    "css-loader",
                    "postcss-loader",
                    "sass-loader",
                ],
            },
        ],
    }
}
