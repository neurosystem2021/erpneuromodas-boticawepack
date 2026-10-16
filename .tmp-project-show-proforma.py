from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');needle='<div class="project-balance-details"><div><span>Total del proyecto</span>'
assert needle in s
s=s.replace(needle,'<div class="project-balance-details"><div v-if="Number(hab.IdProforma) > 0 && Number(hab.TotalProf) > 0" class="project-proforma-total"><span>Monto de proforma</span><b>S/ {{ hab.TotalProf | moneda }}</b></div><div><span>Total del proyecto</span>',1)
p.write_text(s,encoding='utf-8')
