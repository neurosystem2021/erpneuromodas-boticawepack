from pathlib import Path
p=Path('src/views/DiarioActividades.vue');s=p.read_text(encoding='utf-8-sig')
s=s.replace("import {ticketNumber,limaInput", "import DiarioArea from '@/components/DiarioArea';\nimport {ticketNumber,limaInput",1)
s=s.replace('components:{DiarioSoporte,','components:{DiarioArea,DiarioSoporte,',1)
s=s.replace(" <p v-if=\"error\" class=\"diary-error\"", " <diario-area :tasks=\"tasks\" :periodo=\"loadedFilters || filters\" :context=\"context\" :vista=\"workspace\" @period=\"applyAreaPeriod\" @history=\"showHistory\" />\n <p v-if=\"error\" class=\"diary-error\"",1)
s=s.replace(' methods:{ticketNumber,',' methods:{ticketNumber,\n  async applyAreaPeriod(period){this.filters=Object.assign({},this.filters,period);await this.load()},',1)
s=s.replace('Los indicadores corresponden al rango consultado. Tiempo trabajado = intervalos iniciados, excluyendo pausas.', 'El tablero conserva tareas activas de períodos anteriores. El seguimiento superior usa avances del rango consultado. El tiempo de cada tarea es su acumulado, excluyendo pausas.')
p.write_text(s,encoding='utf-8')
p=Path('integration/diario/diario-api.php');s=p.read_text(encoding='utf-8')
old="WHERE '.$scope.' AND t.Inicio<=? AND t.Fin>=?';$params=array_merge($params,[$to,$from]);"
new="WHERE '.$scope.' AND (t.Estado IN (\\'pendiente\\',\\'en_curso\\',\\'pausada\\') OR (t.Inicio<=? AND t.Fin>=?) OR (t.FinReal>=? AND t.FinReal<=?) OR EXISTS(SELECT 1 FROM Rhu_DiarioEvento ev WHERE ev.Tarea=t.Id AND ev.Fecha>=? AND ev.Fecha<=?))';$params=array_merge($params,[$to,$from,$from,$to,$from,$to]);"
if old not in s:raise RuntimeError('Diary task scope boundary missing')
s=s.replace(old,new,1);p.write_text(s,encoding='utf-8')
