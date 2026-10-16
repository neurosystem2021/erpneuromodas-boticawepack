from pathlib import Path
p=Path('src/components/DiarioArea.vue');s=p.read_text(encoding='utf-8')
needle='<p v-if="error" class="work-warning"'
form='''<form class="work-date-filters" @submit.prevent="consultPeriod"><label>Desde<input type="date" v-model="draft.desde" required></label><label>Hasta<input type="date" v-model="draft.hasta" required></label><label v-if="vista==='equipo' && context.admin">Responsable<select v-model="draft.responsable"><option value="">Todo el equipo autorizado</option><optgroup label="Usuarios"><option v-for="u in context.usuarios || []" :key="'u:'+u.Usuario" :value="'u:'+u.Usuario">{{u.NombreUsuario || u.Usuario}}</option></optgroup><optgroup label="Empleados"><option v-for="e in context.empleados || []" :key="'e:'+e.IdEmpleado" :value="'e:'+e.IdEmpleado">{{e.Empleado}}</option></optgroup></select></label><button type="submit" :disabled="loading">Consultar período</button></form>
 '''
s=s.replace(needle,form+needle,1)
s=s.replace("data(){return {loading:false", "data(){return {draft:{desde:this.periodo.desde,hasta:this.periodo.hasta,responsable:this.periodo.responsable||''},periodKey:JSON.stringify(this.periodo),loading:false",1)
s=s.replace("periodo:{deep:true,handler(){this.load();}}", "periodo:{deep:true,handler(){const key=JSON.stringify(this.periodo);if(key!==this.periodKey){this.periodKey=key;this.draft={desde:this.periodo.desde,hasta:this.periodo.hasta,responsable:this.periodo.responsable||''};}this.load();}}",1)
s=s.replace('  duration(minutes)', "  consultPeriod(){if(!this.draft.desde||!this.draft.hasta||this.draft.desde>this.draft.hasta){this.error='Revisa la fecha inicial y final.';return;}this.$emit('period',Object.assign({},this.draft));},\n  duration(minutes)",1)
s=s.replace('.work-area summary{', '.work-date-filters{display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin:12px 0 18px}.work-date-filters label{display:grid;gap:6px;font-weight:600}.work-date-filters input{padding:9px 12px;border:1px solid #bbd0df;border-radius:8px;background:white;color:#18384d}.work-date-filters button{background:#1c6884;color:white}.work-area summary{',1)
p.write_text(s,encoding='utf-8')
p=Path('tools/deploy-work-center.php');s=p.read_text(encoding='utf-8').replace('b731aebb24','b731aaebb24');p.write_text(s,encoding='utf-8')
p=Path('tools/test-work-center-browser.mjs');s=p.read_text(encoding='utf-8').replace("page.locator('.work-history').innerText()", "page.locator('.work-history').last().innerText()");p.write_text(s,encoding='utf-8')
