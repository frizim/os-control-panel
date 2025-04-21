import path from "node:path";
import MiniCssExtractPlugin from "mini-css-extract-plugin";
import CssMinimizerPlugin from "css-minimizer-webpack-plugin";

const config = {
    entry: {
        presession: "./js/presession.js",
        dashboard: "./js/dashboard.js",
        onlinedisplay: "./js/online-display.js",
        identities: "./js/identities.js"
    },
    mode: "production",
    output: {
        filename: 'js/[name].js',
        clean: false,
        path: path.resolve("./public")
    },
    module: {
        rules: [
            {
                test: /\.(woff|woff2|ttf|eot)$/i,
                type: "asset",
                generator: {
                    filename: "fonts/[name][ext]"
                }
            },
            {
                test: /\.scss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    {
                        loader: "sass-loader",
                        options: {
                            sassOptions: {
                                verbose: true
                            }
                        }
                    },
                    {
                        loader: "postcss-loader",
                        options: {
                            postcssOptions: {
                                plugins: [
                                    "autoprefixer"
                                ]
                            }
                        }
                    }
                ],
                include: [
                    path.resolve("./scss/dashboard.scss"),
                    path.resolve("./scss/login.scss"),
                    path.resolve("./scss/online-display.scss")
                ],
                sideEffects: true
            },
            {
                test: /\.css$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    "css-loader"
                ]
            },
            {
                test: /\.(svg|png|jpg)$/,
                type: "asset/resource",
                generator: {
                    filename: "img/[name][ext]"
                }
            }
        ]
    },
    resolve: {
        extensions: [".js"]
    },
    optimization: {
        minimizer: [
            new CssMinimizerPlugin()
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: "css/[name].css"
        })
    ],
    performance: {
        maxAssetSize: 2000000
    }
};

export default config;