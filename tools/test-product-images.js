const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const compiler = require('vue-template-compiler');
const script = compiler.parseComponent(fs.readFileSync('src/components/ProductoImagenes.vue', 'utf8')).script.content
  .replace("import config from '@/store/config';", "const config = { API: '/api/' };").replace('export default', 'module.exports =');
const payloads = [];
const sandbox = { module: {}, URL: { createObjectURL: file => 'preview:' + file.name, revokeObjectURL() {} }, FormData: class { constructor() { this.fields = []; } append(key, value) { this.fields.push([key, value]); } } };
vm.runInNewContext(script, sandbox);
const component = sandbox.module.exports;
(async () => {
  const instance = Object.assign(component.data.call({ primary: 'existing.jpg' }), { primary: 'existing.jpg', productId: 1,
    $set: (array, index, value) => { array[index] = value; }, $toasted: { error() {} }, axios: {
      get: async () => ({ data: { imagenes: ['existing.jpg', 'second.jpg', 'third.jpg'] } }),
      post: async (url, form) => { payloads.push(form.fields); return { data: { insertId: 1, imagenes: ['new.jpg', 'new2.jpg', 'new3.jpg'] } }; }
    } });
  Object.entries(component.methods).forEach(([name, method]) => { instance[name] = method.bind(instance); });
  await instance.load();
  assert.strictEqual(instance.images[2], 'third.jpg');
  for (let index = 0; index < 3; index++) instance.select({ target: { files: [{ name: 'image' + index + '.png', type: 'image/png', size: 200 }], value: 'file' } }, index);
  await instance.upload(1);
  assert.deepStrictEqual(payloads[0].map(field => field[0]), ['imagen', 'imagen2', 'imagen3']);
  assert.strictEqual(instance.files.some(Boolean), false);
  instance.select({ target: { files: [{ name: 'bad.svg', type: 'image/svg+xml', size: 10 }], value: '' } }, 0);
  assert.strictEqual(instance.files[0], null);
  instance.select({ target: { files: [{ name: 'retry.png', type: 'image/png', size: 100 }], value: '' } }, 2);
  instance.axios.post = async () => { throw new Error('Upload failed'); };
  await assert.rejects(instance.upload(1), /Upload failed/);
  assert.strictEqual(instance.files[2].name, 'retry.png');
  assert.strictEqual(instance.busy, false);
  console.log('Three image slots, replacement, invalid type, and retry preservation passed.');
  process.exit(0);
})().catch(error => { console.error(error); process.exit(1); });
