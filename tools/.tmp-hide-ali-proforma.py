from pathlib import Path
p=Path('src/App.vue');s=p.read_text(encoding='utf8').replace('v-if="$route.name !== \'Ventas\'" class="pos-inline-voice"','v-if="$route.name !== \'Ventas\' && $route.name !== \'Proforma\'" class="pos-inline-voice"');p.write_text(s,encoding='utf8')
p=Path('src/components/DiarioRecordatorios.vue');s=p.read_text(encoding='utf8');s+='\n<style>\n@media print{.diario-reminders{display:none!important}}\n</style>\n';p.write_text(s,encoding='utf8')
