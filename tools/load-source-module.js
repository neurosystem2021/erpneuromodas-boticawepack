const fs = require('fs');
const path = require('path');
const Module = require('module');
const babel = require('babel-core');
module.exports = file => {
  const filename = path.resolve(__dirname, '..', file);
  const code = babel.transform(fs.readFileSync(filename, 'utf8'), { filename, babelrc: false, presets: [['env', { modules: 'commonjs', targets: { node: 'current' } }]] }).code;
  const loaded = new Module(filename, module);
  loaded.filename = filename;
  loaded.paths = Module._nodeModulePaths(path.dirname(filename));
  loaded._compile(code, filename);
  return loaded.exports;
};
