from pathlib import Path
p=Path('src/views/reportes/ReporteProyectos.vue');s=p.read_text(encoding='utf-8')
a=s.index('      <b-row>');b=s.index('      <section',a)
s=s[:a]+'''      <header class="project-register-hero"><div><span>CONTROL Y SEGUIMIENTO</span><h1>Registro de proyectos</h1><p>Consulta tus servicios, responsables y movimientos.</p></div><div class="project-register-count"><i class="fas fa-briefcase" aria-hidden="true"></i><strong>{{ totalRows }}</strong><small>proyectos registrados</small></div></header>
'''+s[b:]
s=s.replace('placeholder="Buscar"','placeholder="Proyecto, cliente o documento"',1).replace('}} Ped</p>','}} proyectos</p>',1)
s=s.replace('{ key: "FechaReg", label: "Fecha Registro", sortable: true },','{ key: "UsuarioReg", label: "Creado por", sortable: true },\n        { key: "FechaReg", label: "Fecha Registro", sortable: true },',1)
s=s.replace('        { key: "Cliente", sortable: true },\n        { key: "Adelantos"','        { key: "Adelantos"',1)
s=s.replace('            <template slot="Estado"','''            <template slot="UsuarioReg" slot-scope="data"><span class="project-creator"><i class="far fa-user" aria-hidden="true"></i> {{ data.item.UsuarioReg || 'Sin registro' }}</span></template>
            <template slot="TotalDetalle" slot-scope="data"><strong class="project-money">S/ {{ data.item.TotalDetalle | moneda }}</strong></template>
            <template slot="Adelantos" slot-scope="data"><span class="project-advance">S/ {{ data.item.Adelantos | moneda }}</span></template>
            <template slot="Estado"''',1)
s=s.replace('<b-button variant="primary" @click="verDetalle','<b-button variant="primary" title="Ver detalle del proyecto" aria-label="Ver detalle del proyecto" @click="verDetalle',1)
s=s.replace('modal-class="nst-dialog"','modal-class="nst-dialog project-detail-dialog"',1)
s=s.replace(': {{ productoSelect.UsuarioReg }}',': {{ productoSelect.UsuarioReg || "Sin registro" }}',1)
p.write_text(s,encoding='utf-8')
