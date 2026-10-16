from pathlib import Path
for f in ['integration/ali-technical-expert.php','integration/technical-expert.sql']:
 p=Path(f);p.write_text(p.read_text(encoding='utf-8-sig'),encoding='utf-8')
p=Path('integration/ali-api.php');s=p.read_text(encoding='utf-8').replace("require_once __DIR__.'/ali-product-images.php';", "require_once __DIR__.'/ali-product-images.php';\nrequire_once __DIR__.'/ali-technical-expert.php';");s=s.replace(' aliRegisterCheckout($app,$auth);',' aliRegisterTechnicalExpert($app,$auth);\n aliRegisterCheckout($app,$auth);');p.write_text(s,encoding='utf-8')
p=Path('src/router/index.js');s=p.read_text(encoding='utf-8').replace('import Router from',"import TecnicoExperto from '@/views/TecnicoExperto.vue';\nimport Router from",1).replace('routes: [',"routes: [\n    {path:'/tecnico-experto',name:'TecnicoExperto',component:TecnicoExperto},",1);p.write_text(s,encoding='utf-8')
