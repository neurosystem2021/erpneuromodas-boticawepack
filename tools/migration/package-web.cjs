const fs=require('fs'),path=require('path'),compiler=require('vue-template-compiler');
const dir=path.resolve('output/migrador-web');fs.mkdirSync(dir,{recursive:true});
const copy=(from,to)=>{const dest=path.join(dir,to);fs.mkdirSync(path.dirname(dest),{recursive:true});fs.copyFileSync(from,dest);};
const sfc=compiler.parseComponent(fs.readFileSync('src/views/MigradorHistorico.vue','utf8'));
const script=sfc.script.content.replace(/^import .*;\s*$/gm,'').replace('export default','return');
fs.mkdirSync(path.join(dir,'migration-ui'),{recursive:true});
fs.writeFileSync(path.join(dir,'migration-ui/app.js'),"const config={API:'../api/migracion.php/'};const options=(()=>{"+script+"})();options.template="+JSON.stringify(sfc.template.content)+";new Vue(options).$mount('#app');");
fs.writeFileSync(path.join(dir,'migration-ui/app.css'),'body{margin:0;background:#f3f6fc;font-family:Arial,sans-serif}'+sfc.styles.map(s=>s.content).join('\n'));
fs.writeFileSync(path.join(dir,'migration-ui/index.html'),'<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Migrador Neurosof</title><link rel="stylesheet" href="app.css"></head><body><div id="app"></div><script src="vue.min.js"></script><script src="app.js"></script></body></html>');
copy('node_modules/vue/dist/vue.min.js','migration-ui/vue.min.js');
copy('integration/migration/migrador-historico.php','views/migrador-historico.php');
for(const file of ['Engine.php','runtime.php','schema.sql'])copy('integration/migration/'+file,'api/migration/'+file);
let api=fs.readFileSync('integration/migration/test-api.php','utf8');
api=api.replace("require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';","require __DIR__.'/vendor/autoload.php';").replace("require 'C:/xampp/htdocs/boticav1/config/database.php';","require dirname(__DIR__).'/config/database.php';").replace("require 'C:/xampp/htdocs/boticav1/api/ali-pos-security.php';","require __DIR__.'/ali-pos-security.php';");
api=api.replace('// Standalone test application: reuse authentication libraries, never the production router.','// Hosted ERP module: dedicated router, authenticated superadmin only.');
fs.mkdirSync(path.join(dir,'api'),{recursive:true});fs.writeFileSync(path.join(dir,'api/migracion.php'),api);
copy('integration/ali-pos-security.php','api/migration/pos-security.php');
// Include the authentication helper in the module to avoid assuming it was installed in older ERPs.
api=api.replace("require __DIR__.'/ali-pos-security.php';","require_once __DIR__.'/migration/pos-security.php';");fs.writeFileSync(path.join(dir,'api/migracion.php'),api);
let wrapper=fs.readFileSync(path.join(dir,'views/migrador-historico.php'),'utf8').replace("require_once __DIR__.'/../api/ali-pos-security.php';","require_once __DIR__.'/../api/migration/pos-security.php';");fs.writeFileSync(path.join(dir,'views/migrador-historico.php'),wrapper);
fs.writeFileSync(path.join(dir,'menu-superadmin.snippet.php'),`<?php if (strcasecmp(trim((string)($_SESSION['user']??'')),'superadmin')===0): ?>\n<li><a href="/views/migrador-historico.php">Migrador de compras y ventas</a></li>\n<?php endif; ?>\n`);
copy('docs/migrador-publicacion-web.md','LEEME-PUBLICACION.md');
copy('tools/migration/install-copy.php','tools/migration/install-copy.php');
copy('tools/migration/run-copy.php','tools/migration/run-copy.php');
for(const file of ['install-copy.php','run-copy.php']){const p=path.join(dir,'tools/migration',file);fs.writeFileSync(p,fs.readFileSync(p,'utf8').replace("/../../integration/migration/runtime.php","/../../api/migration/runtime.php"));}
copy('integration/migration/profile.web.example.json','private-templates/profile.example.json');
copy('integration/migration/backup-manifest.web.example.json','private-templates/backup-manifest.example.json');
const files=[];function walk(root){for(const entry of fs.readdirSync(root,{withFileTypes:true})){const p=path.join(root,entry.name);if(entry.isDirectory())walk(p);else files.push(path.relative(dir,p).replaceAll('\\','/'));}}walk(dir);
for(const file of files){if(/\.(php|js|html|json|md)$/.test(file)&&/C:\/?(?:\\|\/)|localhost:8099|C:\/xampp/i.test(fs.readFileSync(path.join(dir,file),'utf8')))throw Error('Local dependency in web package: '+file);}
console.log('Portable hosted module packaged: '+files.length+' files; no credentials or historical records.');process.exit(0);
