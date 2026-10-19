from pathlib import Path
p=Path('src/components/ProformaLista.vue');s=p.read_text(encoding='utf-8');s=s.replace('<b-modal v-model="phoneOpen" title=', '<b-modal v-model="phoneOpen" @hidden="show=true" title=',1);s=s.replace('<b-modal v-model="editOpen" title=', '<b-modal v-model="editOpen" @hidden="show=true" title=',1);p.write_text(s,encoding='utf-8')
