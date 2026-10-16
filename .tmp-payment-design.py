from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');a=s.index('                                              <div class="table-responsive p-2">',s.index(':target="`popover-metodopago-'));b=s.index('                                            </b-popover>',a)
s=s[:a]+'''                                              <section class="pos-payment-picker" aria-label="Formas de pago">
                                                <header class="pos-payment-picker-head"><span><i class="fas fa-wallet" aria-hidden="true"></i></span><div><strong>Formas de pago</strong><small>Distribuye el importe de la venta</small></div></header>
                                                <div v-for="method in [{key:'efectivo',label:'Efectivo',icon:'fas fa-money-bill-alt'},{key:'yape',label:'Yape/Plin',icon:'fas fa-mobile-alt'},{key:'visa',label:'Tarjeta',icon:'fas fa-credit-card'},{key:'mastercard',label:'Transferencia',icon:'fas fa-university'}]" :key="method.key" :class="['pos-payment-option','payment-'+method.key]">
                                                  <span class="pos-payment-option-icon"><i :class="method.icon" aria-hidden="true"></i></span>
                                                  <strong>{{ method.label }}</strong>
                                                  <button type="button" class="pos-payment-assign" :disabled="bloquearInput" :aria-label="'Asignar total a '+method.label" :title="'Asignar total a '+method.label" @click="cargarMonto(method.key)"><i class="fas fa-check" aria-hidden="true"></i></button>
                                                  <input v-model="ventas[index].pago[method.key].descripcion" type="text" class="form-control pos-payment-reference" :disabled="bloquearInput" :placeholder="method.key==='efectivo'?'Referencia':'Operación'" :aria-label="'Referencia de '+method.label">
                                                  <input v-model="ventas[index].pago[method.key].monto" type="number" inputmode="decimal" min="0" step="0.01" class="form-control pos-payment-amount" :disabled="bloquearInput" :aria-label="'Monto de '+method.label" @input="distribuirPago(method.key,$event.target.value)">
                                                </div>
                                              </section>
'''+s[b:]
s=s.replace('custom-class="custom-popover"','custom-class="custom-popover pos-payment-popover"',1)
s=s.replace('triggers="click blur focus"','triggers="click"',1)
s=s.replace('    mostrarAccionesMovil() {','''    distribuirPago(tipo, monto) {
      if (tipo === 'efectivo') return;
      const venta = this.ventas[this.index];
      const otros = ['visa', 'mastercard', 'yape'].reduce((sum, key) => sum + (key === tipo ? Number(monto) || 0 : Number(venta.pago[key].monto) || 0), 0);
      venta.pago.efectivo.monto = this.$options.filters.redondear(Math.max(0, (Number(venta.totalCart) || 0) - otros));
    },
    mostrarAccionesMovil() {''',1)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
/* Formas de pago compactas, con referencias e importes independientes. */
.popover.pos-payment-popover{width:400px!important;max-width:calc(100vw - 12px)!important;border:0;border-radius:17px!important;box-shadow:0 12px 36px #173f5433;font-family:inherit}
.pos-payment-popover .popover-body{padding:0!important;overflow:hidden;border-radius:17px}
.pos-payment-picker{padding:0 2px 4px;background:#fff;color:#173453}
.pos-payment-picker-head{display:flex;align-items:center;gap:11px;padding:12px 16px;margin:0 -2px 7px;color:#fff;background:linear-gradient(110deg,#0c214a,#11629d 65%,#0ca0b5)}
.pos-payment-picker-head>span{display:grid;place-items:center;width:42px;height:42px;border:1px solid #ffffff66;border-radius:13px;background:#ffffff22}
.pos-payment-picker-head strong,.pos-payment-picker-head small{display:block}.pos-payment-picker-head strong{font-size:15px;font-weight:800}.pos-payment-picker-head small{margin-top:3px;font-size:11px;color:#dcefff;font-weight:600}
.pos-payment-option{--pay-color:#0cb985;display:grid;grid-template-columns:30px minmax(70px,1fr) 34px minmax(80px,1.3fr) 80px;align-items:center;gap:8px;min-height:52px;margin:0 0 7px;padding:6px;border:1px solid #d6e5f5;border-left:4px solid var(--pay-color);border-radius:12px;background:#fff}
.pos-payment-option:last-child{margin-bottom:0}.payment-yape{--pay-color:#8a54ff}.payment-visa{--pay-color:#2462f5}.payment-mastercard{--pay-color:#f5a000}
.pos-payment-option-icon,.pos-payment-assign{display:grid;place-items:center;border:0;background:var(--pay-color);color:white;border-radius:10px;box-shadow:0 4px 10px #233d5222}.pos-payment-option-icon{width:30px;height:30px;font-size:16px}.pos-payment-option>strong{font-size:11px;font-weight:800;line-height:1.2;overflow-wrap:anywhere}.pos-payment-assign{width:34px;height:34px;cursor:pointer;font-size:18px}.pos-payment-assign:focus-visible{outline:2px solid #183a56;outline-offset:2px}
.pos-payment-option .form-control{min-width:0;width:100%;height:39px!important;min-height:39px!important;padding:6px 9px!important;border:1px solid #c9ddf5;border-radius:10px;font-size:12px;box-shadow:none;background:#fff;color:#154970}.pos-payment-option .pos-payment-amount{font-weight:800;text-align:center;font-variant-numeric:tabular-nums;appearance:textfield}.pos-payment-amount::-webkit-inner-spin-button,.pos-payment-amount::-webkit-outer-spin-button{appearance:none;margin:0}
@media(max-width:380px){.pos-payment-option{grid-template-columns:26px minmax(58px,1fr) 30px minmax(62px,1fr) 60px;gap:5px;padding:5px}.pos-payment-option-icon{width:26px;height:29px}.pos-payment-assign{width:30px;height:32px}.pos-payment-option .form-control{padding:5px!important;font-size:11px}}
''';p.write_text(s,encoding='utf-8')
