/* eslint-disable indent */
const path = require('path');

module.exports = {
    mode: 'development',
    entry: { app: './assets/javascript/App.jsx' }, // Non-HMR
    // entry: { app: ['./src/App.jsx'] }, // Hot Module Replacement
    output: {
        filename: '[name]_bundle.js',
        path: path.resolve(__dirname, 'public/javascript'),
        publicPath: '/', // This param added for Browser History Router, not needed for Hash Raouter
    },
    module: {
        rules: [
            {
                test: /\.jsx?$/,
                exclude: /node_modules/,
                use: 'babel-loader',
            },
            {
                test: /\.(jpg|gif|png)$/,
                use: {
                    loader: 'url-loader',
                    options: {
                        limit: 25000,
                    },
                },
            },
            {
                test: /\.css$/,
                // exclude: /node_modules/,
                use: [
                    'style-loader',
                    'css-loader',
                ],
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    // Creates `style` nodes from JS strings
                    'style-loader',
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
    // Debug tool -- see source code instead of compiled code
    // Dev console > sources > webpack > . > [source files]
    devtool: 'source-map',
};
