from pathlib import Path
p=Path('src/components/ProductoAsesorIA.vue');s=p.read_text(encoding='utf-8')
s=s.replace(r"join('\\n')",r"join('\n')").replace(r'/\\r?\\n/',r'/\r?\n/')
for field in ['characteristics','pending','sourceText','reference']:
 s=s.replace('v-model="draft.'+field+'"','v-model="draft.'+field+'" :disabled="busy"')
s=s.replace('v-model="reviewed"','v-model="reviewed" :disabled="busy"')
s=s.replace("title:(this.sources.find(s=>s.url===url)||{}).title||url", "title:((this.sources.find(s=>s.url===url)||{}).title||url).slice(0,300)")
p.write_text(s,encoding='utf-8')
