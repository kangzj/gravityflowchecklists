var path = require('path');
module.exports = {
	entry: './js/checklist-settings-source.js',
	output: {
		path: path.resolve(__dirname, 'js'),
		filename: 'checklist-settings-build.js',
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: "babel-loader"
				}
			}
		]
	}
};
