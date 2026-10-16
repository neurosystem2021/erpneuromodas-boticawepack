from pathlib import Path
p=Path('src/components/DiarioArea.vue');s=p.read_text(encoding='utf-8')
s=s.replace('{{support.urgentes.total}} tickets urgentes sin aceptar', "{{support.urgentes.total}} {{support.urgentes.total===1?'ticket urgente sin aceptar':'tickets urgentes sin aceptar'}}",1)
s=s.replace('{{ratingSummary.cantidad || 0}} opiniones en el período', "{{ratingSummary.cantidad || 0}} {{Number(ratingSummary.cantidad)===1?'opinión':'opiniones'}} en el período",1)
s=s.replace('.then(r=>{this.internos=r.data;})', ".then(r=>{if(!r.data||!Array.isArray(r.data.diario)||!Array.isArray(r.data.avances))throw new Error('invalid progress response');this.internos=r.data;})",1)
s=s.replace('this.support=r.data;}catch(e)', "if(!r.data||!Array.isArray(r.data.diario)||!Array.isArray(r.data.tickets))throw new Error('invalid support response');this.support=r.data;}catch(e)",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/SupportLauncher.vue');s=p.read_text(encoding='utf-8').replace('Crítica · No puedo facturar','Urgente · No puedo facturar');p.write_text(s,encoding='utf-8')
p=Path('src/views/CentroSoporte.vue');s=p.read_text(encoding='utf-8').replace("critical:'Crítica'", "critical:'Urgente'",1);p.write_text(s,encoding='utf-8')
