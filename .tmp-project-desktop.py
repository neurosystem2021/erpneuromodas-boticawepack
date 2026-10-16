from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');s=s.replace('size="sm" id="nuevoProyecto"','size="sm" :id="\'nuevoProyecto_\' + i" v-b-toggle="\'crearProyecto_\' + i"',1)
a=s.index('          <b-popover custom-class="project-popover"');b=s.index('            <label>Seleccione un cliente:',a)
s=s[:a]+'''          <b-collapse :id="'crearProyecto_' + i" class="project-create-panel">
            <h2>Nuevo proyecto</h2><p class="project-create-hint">Selecciona el cliente y responsable, y confirma el periodo del servicio.</p>
'''+s[b:]
a=s.index('class="project-create-panel"');b=s.index('          </b-popover>',a);s=s[:b]+s[b:].replace('          </b-popover>','          </b-collapse>',1)
p.write_text(s,encoding='utf-8')
