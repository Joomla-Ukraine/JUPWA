"use strict";

const advancedPreset = require('cssnano-preset-advanced');

module.exports = {
    plugins: [
        require('tailwindcss'),
        require('postcss-merge-rules'),
        require('postcss-sort-media-queries'),
        require('cssnano')({
            preset: [
                advancedPreset,
                {
                    discardComments: {
                        removeAll: true
                    },
                    autoprefixer: false,
                    calc: false,
                    cssDeclarationSorter: true,
                    colormin: true,
                    convertValues: true,
                    discardDuplicates: true,
                    discardOverridden: true,
                    discardUnused: {
                        fontFace: false,
                        keyframes: false
                    },
                    discardEmpty: true,
                    mergeIdents: true,
                    mergeLonghand: true,
                    mergeRules: true,
                    minifyFontValues: true,
                    minifyGradients: true,
                    minifyParams: true,
                    minifySelectors: true,
                    normalizeCharset: true,
                    normalizeDisplayValues: true,
                    normalizePositions: true,
                    normalizeRepeatStyle: true,
                    normalizeString: true,
                    normalizeTimingFunctions: true,
                    normalizeUnicode: true,
                    normalizeUrl: true,
                    normalizeWhitespace: true,
                    orderedValues: true,
                    reduceIdents: true,
                    reduceInitial: true,
                    reduceTransforms: true,
                    svgo: true,
                    uniqueSelectors: true,
                    zindex: false
                }
            ]
        })
    ]
};