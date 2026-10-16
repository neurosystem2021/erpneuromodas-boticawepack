from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8')
a=s.index('          <br />');b=s.index('          <b-popover',a)
s=s[:a]+'''          <header class="projects-hero">
            <div><span class="projects-eyebrow">GESTIÓN DE SERVICIOS</span><h1>{{ pi === 1 ? 'Servicios Huánuco' : 'Servicios Pucallpa' }}</h1><p>Organiza tus proyectos, clientes y equipo en un solo lugar.</p></div>
            <div class="projects-hero-actions"><span class="projects-count">{{ getHabitacionesPiso(pi).length }} proyectos</span><b-button variant="success" size="sm" id="nuevoProyecto"><i class="fas fa-plus" aria-hidden="true"></i> Agregar proyecto</b-button></div>
          </header>
'''+s[b:]
s=s.replace('<b-row class="mt-3">','<b-row class="projects-grid">',1).replace('class="habitacion"','class="habitacion project-cell"',1)
a=s.index('              <h5>{{ hab.Producto }}</h5>');b=s.index('              <div slot="footer">',a)
s=s[:a]+'''              <div class="project-card-heading"><span class="project-icon"><i class="fas fa-briefcase" aria-hidden="true"></i></span><span class="project-status">{{ getVariant(hab) === 'danger' ? 'Plazo vencido' : hab.EstadoHabitacion == 3 ? 'En curso' : hab.EstadoHabitacion == 2 ? 'Reservado' : 'Disponible' }}</span></div>
              <h2 class="project-name">{{ hab.Producto }}</h2>
              <p class="project-client">{{ hab.Cliente || 'Sin cliente asignado' }}</p>
              <div class="project-amount"><span>Importe del proyecto</span><strong>S/ {{ hab.granTotalCalculado | moneda }}</strong><small v-if="hab.IdProforma">Proforma: S/ {{ hab.TotalProf | moneda }}</small></div>
              <div class="project-meta" v-if="hab.EstadoHabitacion == 3"><div><i class="far fa-calendar-alt" aria-hidden="true"></i><span><small>Periodo del servicio</small>{{ hab.FechaAlquiler || 'Sin fecha' }} <span aria-hidden="true">→</span> {{ hab.FechaAlquilerFin || 'Sin fecha' }}</span></div><div><i class="fas fa-user-tie" aria-hidden="true"></i><span><small>Personal de servicio</small>{{ hab.Empleado || 'Sin personal asignado' }}</span></div></div>
              <div class="project-meta" v-if="hab.EstadoHabitacion == 2"><div><i class="far fa-calendar-alt" aria-hidden="true"></i><span><small>Reserva</small>{{ formatFecha(hab.FechaReserva) }}</span></div><div v-if="hab.Vuelo">Vuelo: {{ hab.Vuelo }}</div></div>
'''+s[b:]
s=s.replace('>+</b-button','><i class="fas fa-plus" aria-hidden="true"></i> Detalle</b-button',1)
s=s.replace('<b-popover\n','<b-popover custom-class="project-popover"\n')
s=s.replace('.popover {','.project-popover {',1).replace('  width: 500px !important;','  width: min(500px, calc(100vw - 24px)) !important;',1).replace('.popover-body {','.project-popover .popover-body {',1)
p.write_text(s,encoding='utf-8')
