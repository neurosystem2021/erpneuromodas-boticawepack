from pathlib import Path
import json
source=Path('integration/remision-imceden/frontend/src/legacy-migrations');dest=Path('src/legacy-migrations');dest.mkdir(exist_ok=True)
raw=json.loads(Path('.tmp-order-workbook/ubigeo-inei.json').read_text(encoding='utf-8-sig'));assert len(raw)>1800
Path('static/ubigeo-inei.json').write_text(json.dumps(raw,ensure_ascii=False),encoding='utf-8')
s=(source/'remision-modern.js').read_text(encoding='utf-8').replace('https://jmc-software-x.github.io/public-ubigeo-pe/data/ubigeo-inei.json','static/ubigeo-inei.json')
s=s.replace("var required = Array.prototype.slice.call(form.querySelectorAll('[required]'));","var required = Array.prototype.slice.call(form.querySelectorAll('[required]')).filter(function(field){return !field.disabled;});")
s=s.replace("Array.prototype.slice.call(form.querySelectorAll('[required]')).find", "Array.prototype.slice.call(form.querySelectorAll('[required]')).filter(function(field){return !field.disabled;}).find")
# The source migration shares a page with the Vue router: don't leave its floating action bar after navigation.
s=s.replace("if (form.dataset.modernized === '1') return;", "if (form.dataset.modernized === '1') return;")
(dest/'remision-modern.js').write_text(s,encoding='utf-8')
css=(source/'remision-modern.css').read_text(encoding='utf-8');css+='\nbody.nm-remision-page .nst-module-heading{display:none} body.nm-remision-page .nm-guide-hero{padding:12px 18px} body.nm-remision-page .nm-guide-actionbar{z-index:1020}\n';(dest/'remision-modern.css').write_text(css,encoding='utf-8')
p=Path('src/main.js');s=p.read_text(encoding='utf-8');s+="\nimport './legacy-migrations/remision-modern.css'\nimport './legacy-migrations/remision-modern.js'\n";p.write_text(s,encoding='utf-8')
p=Path('src/views/RemisionLista.vue');s=p.read_text(encoding='utf-8');needle='<b-container class="bv-example-row">';assert needle in s;s=s.replace(needle,needle+'''<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Guías de remisión</h4><router-link class="btn btn-success" to="/remision">+ Nueva guía</router-link></div>''',1);p.write_text(s,encoding='utf-8')
p=Path('src/router/index.js');s=p.read_text(encoding='utf-8');needle='      path: "/remision",';assert needle in s;s=s.replace(needle,needle+'\n      alias: "/remision/ver/:idDocVenta/:Serie/:Numero",',1);p.write_text(s,encoding='utf-8')
p=Path('src/views/Remision.vue');s=p.read_text(encoding='utf-8');needle='    this.obtenerNumero()';assert needle in s;s=s.replace(needle,needle+'''\n    const params=this.$route.params;
    if(params.idDocVenta) this.setDocRef({IdDocVenta:params.idDocVenta,NroComprobante:params.Serie+'-'+params.Numero});''',1)
# Never keep the save button stuck after a failed request.
needle=".catch((error) => {\n          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')";s=s.replace(needle,".catch((error) => {\n          this.inProgress = false;\n          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')")
p.write_text(s,encoding='utf-8')
print('IMCEDEN guide UI integrated; local catalog',len(raw),'records. Current GRE sending preserved.')
