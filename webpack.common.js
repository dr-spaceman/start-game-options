/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable indent */
const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: {
        index: './browser/src/index.js', // non-HMR
        // app: ['./src/App.jsx'] // Hot Module Replacement
        test: './browser/src/test.js',
    },
    plugins: [
        // Clean dist dir before every build
        new CleanWebpackPlugin(),
        // Extracts CSS into separate files, a CSS file per JS file which contains CSS
        new MiniCssExtractPlugin({
            filename: '[name].css',
        }),
    ],
    output: {
        chunkFilename: '[name].bundle.js',
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'public/dist'),
        publicPath: '/dist/',
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                include: path.resolve(__dirname, 'browser/src'),
                use: 'babel-loader',
            },
            {
                test: /\.(jpg|gif|png)$/,
                include: path.resolve(__dirname, 'browser/images'),
                use: {
                    loader: 'url-loader',
                    options: {
                        limit: 25000,
                    },
                },
            },
            // {
            //     test: /\.css$/,
            //     use: [
            //         // Extracts CSS into separate files, a CSS file per JS file which contains CSS
            //         MiniCssExtractPlugin.loader,
            //         // Creates `style` nodes from JS strings; Inject CSS into the DOM
            //         // 'style-loader',
            //         'css-loader',
            //     ],
            // },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    // Extracts CSS into separate files, a CSS file per JS file which contains CSS
                    MiniCssExtractPlugin.loader,
                    // Creates `style` nodes from JS strings; Inject CSS into the DOM
                    // 'style-loader',
                    // Translates CSS into CommonJS
                    'css-loader',
                    'resolve-url-loader',
                    // Compiles Sass to CSS
                    'sass-loader',
                ],
            },
            {
                // Match woff2 in addition to patterns like .woff?v=1.1.1.
                test: /\.woff2?(\?v=\d+\.\d+\.\d+)?$/,
                include: path.resolve(__dirname, 'browser/fonts'),
                use: {
                    loader: 'url-loader',
                    options: {
                        // Limit at 50k. Above that it emits separate files
                        limit: 50000,

                        // url-loader sets mimetype if it's passed.
                        // Without this it derives it from the file extension
                        mimetype: 'application/font-woff',

                        // Output below fonts directory
                        name: './fonts/[name].[ext]',
                    },
                },
            },
        ],
    },
    optimization: {
        splitChunks: {
            name: 'vendor',
            chunks: 'all',
        },
    },
};
