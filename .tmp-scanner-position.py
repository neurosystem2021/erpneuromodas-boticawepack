from pathlib import Path
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8');line='    <inventory-camera :embedded="true" :continuous="true" v-if="showProjectScanner" @close="showProjectScanner=false" @code="readProjectBarcode"/>'
assert line in s
s=s.replace(line+'\n','',1);s=s.replace('    <section class="project-barcode-search">',line+'\n    <section class="project-barcode-search">',1);p.write_text(s,encoding='utf-8')
