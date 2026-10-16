from pathlib import Path
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8')
panel='''<b-col cols="12" v-if="pagosPersonal.length">
          <h5>Adelantos y pagos al personal — cuenta de origen</h5>
          <p class="closing-bank-note">Este resumen muestra pagos ya incluidos en el detalle de su cuenta. No vuelve a sumar ni descontar los importes.</p>
          <div class="nst-table-scroll" tabindex="0" role="region" aria-label="Pagos al personal"><table class="table b-table table-sm">
            <thead><tr class="thDetalle"><th>Fecha</th><th>Personal / concepto</th><th>Cuenta de salida</th><th>Usuario</th><th>Monto</th><th>Tratamiento en Caja Principal</th></tr></thead>
            <tbody><tr v-for="item in pagosPersonal" :key="'personal-'+item.IdCajaBanco">
              <td>{{ item.FechaDoc }}</td><td>{{ item.Concepto }}</td><td><b>{{ item.Cuenta }}</b></td><td>{{ item.UsuarioReg }}</td>
              <td>S/. {{ item.Importe | redondear }}</td>
              <td>{{ Number(item.Anulado) ? 'Anulado: no afecta el saldo' : (Number(item.IdCuenta) === 1 ? 'Incluido una sola vez en salidas de caja' : 'Solo referencia: no suma ni resta a caja') }}</td>
            </tr></tbody>
          </table></div>
        </b-col>
        '''
for target in ['<b-col v-if="totalSalidas">','<b-col cols="12" v-if="totalSalidas">']:
 assert target in s;s=s.replace(target,panel+target)
s=s.replace('    totalSalidas() {','''    pagosPersonal() {
      return this.salidas.concat(this.salidasTarjeta).filter(item => /^PLANILLA\\s/i.test(item.Concepto || ""));
    },
    totalSalidas() {''')
p.write_text(s,encoding='utf-8')
