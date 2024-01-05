<template>
    <div>
      <b-container fluid style="width: calc(85%);" class="bv-example-row" id="conten">
        <b-row class="mt-2">
            <b-col md="12" class="mb-2" v-if="visual">
                <h4><b>ESTADO DE CAJA: </b><b-badge :variant="cajaEstado==1?'primary':'success'">{{ cajaEstado==1?'ABIERTO':'CERRADO' }}</b-badge></h4>
            </b-col>
            <b-col md="3">
                <b-input-group prepend="Usuario " class="inputLine">
                <input
                    v-model="vendedor.NombreUsuario"
                    type="text"
                    style="width: 80px"
                    :disabled="true"
                    class="form-control" />
                </b-input-group>
            </b-col>
            <b-col md="3" v-if="!visual">
                <b-row>
                    <b-button v-if="idCajaAbierta > 0" variant="warning" @click="cerrarCaja">Cerrar Caja</b-button>
                    <b-button v-else variant="success" @click="abrirCaja">Aperturar Caja</b-button>
                    <b-button class="ml-2" v-if="idCajaAbierta > 0" variant="info" @click="registrarMov">Registrar</b-button>
                    <b-button class="ml-2" variant="primary" @click="getVendedor(1)">Actualizar</b-button>
                </b-row>
            </b-col>
            <b-col md="1">
                <b-button-group size="sm" class="ml-2">
                        <agente-cierres></agente-cierres>
                    </b-button-group>
                </b-col>
            <b-col md="5" style="display: flex; justify-content: center; align-content: center;">
                <b-row v-if="idCajaAbierta > 0">
                    <b-col md="12">
                        <h5 class="text-primary" style="font-weight: bold;">FECHA Y HORA DE APERTURA: {{ cajaFechaApertura }}</h5>
                        <h5 class="text-success" style="font-weight: bold;">FECHA Y HORA DE CIERRE: {{ cajaFechaCierre }}</h5>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <hr>
        <b-row v-if="!(idCajaAbierta > 0)">
            <b-col class="text-center mt-5">
                <h2 class="text-danger" v-show="cargaCorrecta">APERTURE SU CAJA PARA PODER REALIZAR OPERACIONES</h2>
            </b-col>
        </b-row>
        <b-row v-else>
            <b-col>
                <b-row v-for="(entidad, index) in data" :key="index">
                    <h5>{{ entidad.Nombre }}</h5><br>
                        <table class="table b-table table-sm">
                        <thead>
                            <tr>
                                <th class="tdgris" width="7%">Recibo</th><th class="tdgris" width="15%">Fecha</th><th class="tdgris" width="15%">Empresa</th><th class="tdgris" width="15%">Operación</th><th class="tdgris" width="20%">Cliente</th><th class="table-primary text-center" width="10%">Monto</th><th class="table-info text-center" width="10%">Comision</th><!--<th class="table-success text-center" width="10%">Total</th>--><th class="text-center" width="5%">Estado</th>
                                <th class="text-center" width="5%">Info</th><th class="text-center" width="5%" v-if="!visual">Procesar</th><th class="text-center" width="5%" v-if="!visual">Anular</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in entidad.Comprobantes" :key="index" :class="{anulado: item.Anulado}">
                                <td>{{ item.IdComprobante}}</td>
                                <td>{{ item.FechaDoc }}</td>
                                <td>{{ item.Empresa }}</td>
                                <td>{{ item.CategoriaEmpresa }} <br>
                                    <b>{{ item.Servicio }}</b>
                                </td>
                                <td>{{ item.Cliente }}</td>
                                <td class="table-primary text-center">S/ {{ item.Monto | moneda }}</td>
                                <td class="table-info text-center">S/ {{ item.Comision | moneda }}</td>
                                <!--<td class="table-success text-center">S/ {{ item.Total | moneda }}</td>-->
                                <td class="text-center"><b-badge style="font-size:0.8rem" :variant="item.Estado==1?'primary':(item.Estado==2?'success':'danger')">{{ item.Estado==1?'PENDIENTE':(item.Estado==2?'PROCESADO':'CANCELADO') }}</b-badge></td>
                                <td class="text-center"><span class="btn text-info" size="sm" @click="infoComprobante(item)"><i class="fas fa-info fa-lg"></i></span></td>
                                <td class="text-center" v-if="!visual"><span v-if="item.Estado==1 && item.Anulado==0" class="btn text-success" variant="success" size="sm" @click="procesarComprobante(item)"><i class="fas fa-check fa-lg"></i></span></td>
                                <td class="text-center" v-if="!visual"><span v-if="!item.Anulado && item.Estado==1" class="btn text-danger" size="sm" @click="anularComprobante(item)"><i class="fas fa-ban fa-lg"></i></span><span v-else>-</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                            <td class="total text-center" colspan="5"><b>Totales {{ entidad.Nombre }}</b></td>
                            <td class="table-primary text-center"><b>S/ {{ entidad.TotalMonto | moneda }}</b></td>
                            <td class="table-info text-center"><b>S/ {{ entidad.TotalComision | moneda }}</b></td>
                            <!--<td class="table-success text-center"><b>S/ {{ entidad.Total | moneda }}</b></td>-->
                            </tr>
                        </tfoot>
                    </table>
                </b-row>
            </b-col>
        </b-row>
        <hr v-if="totalIngresos>0 || totalEgresos>0">
        <b-row>
            <b-col v-if="totalIngresos>0">
            <h5>INGRESOS CAJA</h5><br>
            <table class="table b-table table-sm">
                <thead>
                <tr>
                    <th>ID</th><th>Fecha</th><th>Concepto</th><th>Ingreso</th><th class="text-center">Anular</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, index) in ingresos" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.IdCaja }}</td>
                    <td>{{ item.FechaReg }}</td>
                    <td>{{ item.Concepto }}</td>
                    <td>S/. {{ item.Importe | redondear }}</td>
                    <td class="text-center" v-if="!visual"><span v-if="!item.Anulado" class="btn btn-sm text-danger" size="sm" @click="anularMovimiento(item)"><i class="fas fa-ban fa-lg"></i></span><span v-else>-</span></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td class="total"><b>Total Ingresos</b></td>
                    <td class="total"><b>S/. {{ totalIngresos | redondear }}</b></td>
                </tr>
                </tfoot>
            </table>
            </b-col>
            <b-col v-if="totalEgresos>0">
            <h5>EGRESOS CAJA</h5><br>
            <table class="table b-table table-sm">
                <thead>
                <tr>
                    <th>ID</th><th>Fecha</th><th>Concepto</th><th>Salida</th><th class="text-center">Anular</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(item, index) in egresos" :key="index">
                    <td>{{ item.IdCaja }}</td>
                    <td>{{ item.FechaReg }}</td>
                    <td>{{ item.Concepto }}</td>
                    <td>S/. {{ item.Importe | redondear }}</td>
                    <td class="text-center" v-if="!visual"><span v-if="!item.Anulado" class="btn btn-sm text-danger" size="sm" @click="anularMovimiento(item)"><i class="fas fa-ban fa-lg"></i></span><span v-else>-</span></td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td class="total-salida"><b>Total Egresos</b></td>
                    <td class="total-salida"><b>S/. {{ totalEgresos | redondear }}</b></td>
                </tr>
                </tfoot>
            </table>
            </b-col>
        </b-row>
        <agente-registrar-mov ref="movimientoModal" :usuario-reg="vendedor.Usuario" :cbGuardar="cargarCaja"></agente-registrar-mov>
      </b-container>
    </div>
  </template>
  <script>
  import config from '@/store/config.js'
  import AgenteCierres from '@/components/AgenteCierres.vue'
  import AgenteRegistrarMov from '@/components/AgenteRegistrarMov.vue'
  
  export default {
    mounted () {
        if (this.$route.params.id) {
            this.getCierre(this.$route.params.id)
            this.idCajaAbierta = this.$route.params.id
            this.getComprobantes(this.$route.params.id)
            this.visual = true
        } else {
            this.getVendedor(1)
            this.visual = false
        }
    },
    data () {
      return {
        API: config.API,
        ventas: [],
        ingresos: [],
        cargaCorrecta: false,
        vendedor: {},
        data:[],
        idCajaAbierta: null,
        cajaFechaApertura: '',
        cajaFechaCierre: '',
        cajaEstado: 1,
        cajaIdTurno: null,
        visual: false,
        ingresos: [],
        egresos: [],
      }
    },
    computed: {
        totalEgresos () {
            let total = 0
            this.egresos.forEach(egreso => {
                if (!egreso.Anulado) total += egreso.Importe
            });
            return total 
        },
        totalIngresos () {
            let total = 0
            this.ingresos.forEach(ingreso => {
                if (!ingreso.Anulado) total += ingreso.Importe
            });
            return total
        },
    },
    methods: {
        cargarCaja(){
            this.getCaja()
        },
        registrarMov(){
            if(!this.vendedor.Usuario){
                this.$toasted.error('No se cargo el usuario, actualice la pagina o vuelva a iniciar sesión')
                return
            }

            this.$refs.movimientoModal.show = true;


            /*let r = confirm('¿Se registrará un movimiento de caja, está seguro?')
            if (r) {
                this.$swal({
                    title: 'Cerrando Caja...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        this.$swal.showLoading();
                    }
                })
                try {
                    const response = await this.axios.post(`${this.API}agente/cierrecaja/cerrar`, {
                        Usuario: this.vendedor.Usuario
                    });
                    this.$swal.close()
                    if(response.data.success){
                        this.$swal({
                            position: 'center',
                            icon: 'success',
                            title: '¡Caja Cerrada!',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#1D9828',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }else{
                        this.$swal({
                            position: 'center',
                            icon: 'warning',
                            title: response.data.msg,
                            showConfirmButton: true,
                            confirmButtonText: 'Entiendo',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#3085d6',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }
                    this.getVendedor()
                } catch (error) {
                    console.log(error);
                    this.$swal.close()
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                }
            }*/
        },
        getCierre(id) {
            this.axios.get(this.API + 'agente/cierrecaja', {
                params: {
                idCierre: id
                }
            }).then(res =>{
                if(res.data.data[0]){
                    this.cajaFechaApertura = res.data.data[0].FechaApertura
                    this.cajaFechaCierre = res.data.data[0].FechaCierre
                    this.cajaEstado = res.data.data[0].Estado
                    this.axios.get(this.API + 'agente/usuarios/' + res.data.data[0].UsuarioReg)
                        .then((response) => {
                            if(response.data){

                                this.setVendedor(response.data)
                            }
                        })
                        .catch((error) => {
                            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                        })
                }
            })
        },
        async cerrarCaja(){
            if(!this.vendedor.Usuario){
                this.$toasted.error('No se cargo el usuario, actualice la pagina o vuelva a iniciar sesión')
                return
            }

            let r = confirm('¿Está seguro de cerrar su caja?')
            if (r) {
                this.$swal({
                    title: 'Cerrando Caja...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        this.$swal.showLoading();
                    }
                })
                try {
                    const response = await this.axios.post(`${this.API}agente/cierrecaja/cerrar`, {
                        Usuario: this.vendedor.Usuario
                    });
                    this.$swal.close()
                    if(response.data.success){
                        this.$swal({
                            position: 'center',
                            icon: 'success',
                            title: '¡Caja Cerrada!',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#1D9828',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }else{
                        this.$swal({
                            position: 'center',
                            icon: 'warning',
                            title: response.data.msg,
                            showConfirmButton: true,
                            confirmButtonText: 'Entiendo',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#3085d6',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }
                    this.getVendedor()
                } catch (error) {
                    console.log(error);
                    this.$swal.close()
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                }
            }
        },
        async abrirCaja(){
            if(!this.vendedor.Usuario){
                this.$toasted.error('No se cargo el usuario, actualice la pagina o vuelva a iniciar sesión')
                return
            }

            let r = confirm('¿Está seguro de aperturar su caja?')
            if (r) {
                this.$swal({
                    title: 'Aperturando Caja...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        this.$swal.showLoading();
                    }
                })
                try {
                    const response = await this.axios.post(`${this.API}agente/cierrecaja/aperturar`, {
                        Usuario: this.vendedor.Usuario
                    });
                    this.$swal.close()
                    if(response.data.success){
                        this.$swal({
                            position: 'center',
                            icon: 'success',
                            title: '¡Caja Aperturada!',
                            showConfirmButton: true,
                            confirmButtonText: 'Aceptar',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#1D9828',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }else{
                        this.$swal({
                            position: 'center',
                            icon: 'warning',
                            title: respuesta.data.msg,
                            showConfirmButton: true,
                            confirmButtonText: 'Entiendo',
                            target: document.getElementById('conten'),
                            confirmButtonColor:'#3085d6',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                    }
                    this.getVendedor()
                } catch (error) {
                    this.$swal.close()
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                }
            }
        },
        infoComprobante(item){
            this.$swal({
            target: document.getElementById('conten'),
            title: 'INFORMACIÓN DE OPERACIÓN',
            icon: 'info',
            html: `<div style="text-align:left">
                <strong>ESTADO:  ${item.Estado==2?'<span style="color:#28a745">PROCESADO</span>':'<span style="color:#0d6efd">PENDIENTE</span>'} ${item.Anulado==1?' - <span style="color:#dc3545">ANULADO</span>':''} </strong>
                ${item.Estado==2?'<br><strong>N° OPERACION:</strong> '+item.NumeroOperacion:''}
                ${item.Estado==2?'<br><strong>FECHA Y HORA:</strong> '+item.FechaOperacion:''}
                <hr>
                <strong>CLIENTE: </strong> ${item.Cliente}<br>
                <strong>TELEFONO: </strong> ${item.Telefono}<br>
                <strong>ENTIDAD: </strong> ${item.Nombre} - ${item.Empresa} -  ${item.CategoriaEmpresa} - ${item.Servicio}<br>
                ${item.NombreCodigo!=''?' <strong style="color: #dc3545;">'+(''+item.NombreCodigo).toUpperCase()+': </strong><span style="color: #dc3545;">'+item.ValorCodigo+'</span><br>':''}
                <strong>MONTO: </strong> S/ ${(item.Monto > 0 ?  Number.parseFloat(item.Monto).toFixed(2) : 0 )}<br>
                <strong>COMISION: </strong> S/ ${(item.Comision > 0 ?  Number.parseFloat(item.Comision).toFixed(2) : 0)}<br><hr>
                <strong>TOTAL A RECIBIR:  S/ ${Number.parseFloat(Number(item.Comision)+Number(item.Monto)).toFixed(2)}</strong><br>
                <strong>TOTAL A PAGAR (<span style="color:#dc3545">${item.Nombre}</span>):  S/ ${Number.parseFloat(item.Monto).toFixed(2)}</strong><br><hr>
                <strong>OBSERVACION: </strong> ${item.Observacion!=''?item.Observacion:'Sin Observación'}<br>`,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'CERRAR',
            })
        },
        procesarComprobante(item){
            console.log(item);
            this.$swal({
            title: 'Procesar',
            html: `<form onsubmit="event.preventDefault();"><label>Procesar comprobante: <br><strong>${item.Nombre} - ${item.Empresa} -  ${item.CategoriaEmpresa} - ${item.Servicio}</strong> <br>de monto <strong>S/ ${Number.parseFloat(item.Monto).toFixed(2)}</strong></label>
            <hr>
            <label><b style="color: #dc3545;">${item.NombreCodigo}</b>: <b>${item.ValorCodigo}</b></label>
            <br>
            <input type="text" id="codigo" class="swal2-input" placeholder="Codigo de Operación" autocomplete="off"><form>`,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Procesar',
            cancelButtonText: 'Cancelar',
            target: document.getElementById('conten'),
            focusConfirm: false,
                preConfirm: () => {
                const codigo = this.$swal.getPopup().querySelector('#codigo').value
                if (!codigo) {
                this.$swal.showValidationMessage('Ingrese el código de la operación')
                    if(this.$swal.getPopup().querySelector('#codigo')){
                        this.$swal.getPopup().querySelector('#codigo').focus(); 
                    }
                }
                return {codigo: codigo}
            }
            }).then(async (result) => {
                if (result.value.codigo?result.value.codigo:false) {
                    this.$swal({
                        title: 'Procesando Comprobante...',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            this.$swal.showLoading();
                        }
                    })
                    try {
                        let respuesta = await this.axios.post(`${this.API}agente/comprobante/procesar`,{
                        IdComprobante: item.IdComprobante,
                        NumeroOperacion: result.value.codigo
                        });
                        this.$swal.close()
                        if(respuesta.data.success){
                            this.getComprobantes()
                            this.$swal({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Comprobante procesado',
                            showConfirmButton: false,
                            timer: 1500,
                            target: document.getElementById('conten'),
                            })
                        }else{
                            this.$swal({
                                position: 'center',
                                icon: 'warning',
                                title: respuesta.data.msg,
                                showConfirmButton: true,
                                confirmButtonText: 'Entiendo',
                                target: document.getElementById('conten'),
                                confirmButtonColor:'#3085d6',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            })
                        }
                    } catch (error) {
                        console.log(error);
                        this.$swal.close()
                        this.$toasted.show('Ha ocurrido un error')
                    }
                }
            })
        },
        anularComprobante(item){
          this.$swal({
          target: document.getElementById('conten'),
          title: 'Se anulará el comprobante: <br><strong>'+item.Nombre+' - '+item.Empresa+' - '+item.Servicio+'</strong><br> de monto <strong>S/ '+Number.parseFloat(item.Monto).toFixed(2)+'</strong><br> ¿Está seguro? ',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, anular',
          cancelButtonText: 'No, atrás'
          }).then( async (result) => {
              if (result.isConfirmed?result.isConfirmed:false) {
                  this.$swal({
                  title: 'Autorización de Administrador',
                  html: `<form onsubmit="event.preventDefault();"><label>Anulación del comprobante: <br><strong>${item.Nombre} - ${item.Empresa} - ${item.Servicio}</strong><br> de monto <strong>S/ ${Number.parseFloat(item.Monto).toFixed(2)}</strong></label>
                  <input type="password" id="password" class="swal2-input" placeholder="Ingrese su contraseña">
                  <hr><b>Motivo de la anulación: </b><textarea id="motivo" class="swal2-input" style="width: 370px; height:130px" placeholder="Ingrese motivo"></textarea><form>`,
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Autorizar',
                  cancelButtonText: 'Cancelar',
                  target: document.getElementById('conten'),
                  focusConfirm: false,
                      preConfirm: () => {
                      const password = this.$swal.getPopup().querySelector('#password').value
                      if (!password) {
                      this.$swal.showValidationMessage('Ingrese la contraseña de administrador')
                          if(this.$swal.getPopup().querySelector('#password')){
                              this.$swal.getPopup().querySelector('#password').focus(); 
                          }
                      }
                      const motivo = (""+ this.$swal.getPopup().querySelector('#motivo').value).trim()
                      if (motivo=='') {
                      this.$swal.showValidationMessage('Ingrese el motivo de la anulación')
                          if(this.$swal.getPopup().querySelector('#motivo')){
                              this.$swal.getPopup().querySelector('#motivo').focus(); 
                          }
                      }
                      return {password: password, motivo: motivo }
                  }
                  }).then((result) => {
                      if (result.value.password) {
                          this.axios.post(this.API + 'login', {
                          user: 'admin',
                          password: result.value.password
                          }).then(async (r) => {
                              if (r.data) {
                                this.$swal({
                                    title: 'Anulando Comprobante...',
                                    allowEscapeKey: false,
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        this.$swal.showLoading();
                                    }
                                })
                                try {
                                    let respuesta = await this.axios.post(`${this.API}agente/comprobante/anular`,{
                                      IdComprobante: item.IdComprobante,
                                      Motivo: result.value.motivo
                                    });
                                    this.$swal.close()
                                    if(respuesta.data.success){
                                        this.getComprobantes()
                                        this.$swal({
                                          position: 'top-end',
                                          icon: 'success',
                                          title: respuesta.data.msg,
                                          showConfirmButton: false,
                                          timer: 1500,
                                          target: document.getElementById('conten'),
                                        })
                                    }else{
                                        this.$swal({
                                            position: 'center',
                                            icon: 'warning',
                                            title: respuesta.data.msg,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Entiendo',
                                            target: document.getElementById('conten'),
                                            confirmButtonColor:'#3085d6',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                        })
                                    }
                                } catch (error) {
                                  console.log(error);
                                    this.$swal.close()
                                    this.$toasted.show('Ha ocurrido un error')
                                }
                              }else{
                                  this.$toasted.error("¡No autorizado para anular!")
                              }
                          })
                      }else{
                          this.$toasted.error("¡No autorizado para anular!")
                      }
                  })
              }
          }) 
        },
        anularMovimiento(item){
          this.$swal({
          target: document.getElementById('conten'),
          title: 'Se anulará el movimiento de caja con concepto: <br><strong>'+item.Concepto+'</strong><br> e importe de <strong>S/ '+Number.parseFloat(item.Importe).toFixed(2)+'</strong><br> ¿Está seguro? ',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, anular',
          cancelButtonText: 'No, atrás'
          }).then( async (result) => {
              if (result.isConfirmed?result.isConfirmed:false) {
                  this.$swal({
                  title: 'Autorización de Administrador',
                  html: `<form onsubmit="event.preventDefault();"><label>Anulación de movimiento con concepto: <br><strong>${item.Concepto}</strong><br> e importe de <strong>S/ ${Number.parseFloat(item.Importe).toFixed(2)}</strong></label>
                  <input type="password" id="password" class="swal2-input" placeholder="Ingrese su contraseña">
                  <hr><b>Motivo de la anulación: </b><textarea id="motivo" class="swal2-input" style="width: 370px; height:130px" placeholder="Ingrese motivo"></textarea><form>`,
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Autorizar',
                  cancelButtonText: 'Cancelar',
                  target: document.getElementById('conten'),
                  focusConfirm: false,
                      preConfirm: () => {
                      const password = this.$swal.getPopup().querySelector('#password').value
                      if (!password) {
                      this.$swal.showValidationMessage('Ingrese la contraseña de administrador')
                          if(this.$swal.getPopup().querySelector('#password')){
                              this.$swal.getPopup().querySelector('#password').focus(); 
                          }
                      }
                      const motivo = (""+ this.$swal.getPopup().querySelector('#motivo').value).trim()
                      if (motivo=='') {
                      this.$swal.showValidationMessage('Ingrese el motivo de la anulación')
                          if(this.$swal.getPopup().querySelector('#motivo')){
                              this.$swal.getPopup().querySelector('#motivo').focus(); 
                          }
                      }
                      return {password: password, motivo: motivo }
                  }
                  }).then((result) => {
                      if (result.value.password) {
                          this.axios.post(this.API + 'login', {
                          user: 'admin',
                          password: result.value.password
                          }).then(async (r) => {
                              if (r.data) {
                                this.$swal({
                                    title: 'Anulando Movimiento...',
                                    allowEscapeKey: false,
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        this.$swal.showLoading();
                                    }
                                })
                                try {
                                    let respuesta = await this.axios.post(`${this.API}agente/caja/anular`,{
                                      IdCaja: item.IdCaja,
                                      Motivo: result.value.motivo
                                    });
                                    this.$swal.close()
                                    if(respuesta.data.success){
                                        this.getCaja()
                                        this.$swal({
                                          position: 'top-end',
                                          icon: 'success',
                                          title: respuesta.data.msg,
                                          showConfirmButton: false,
                                          timer: 1500,
                                          target: document.getElementById('conten'),
                                        })
                                    }else{
                                        this.$swal({
                                            position: 'center',
                                            icon: 'warning',
                                            title: respuesta.data.msg,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Entiendo',
                                            target: document.getElementById('conten'),
                                            confirmButtonColor:'#3085d6',
                                            allowOutsideClick: false,
                                            allowEscapeKey: false,
                                        })
                                    }
                                } catch (error) {
                                  console.log(error);
                                    this.$swal.close()
                                    this.$toasted.show('Ha ocurrido un error')
                                }
                              }else{
                                  this.$toasted.error("¡No autorizado para anular!")
                              }
                          })
                      }else{
                          this.$toasted.error("¡No autorizado para anular!")
                      }
                  })
              }
          }) 
        },
        async getComprobantes(id=null){
            this.data = []
            try {
                const response = await this.axios.get(`${this.API}agente/cierrecaja/operaciones`, { params:
                    {
                        Usuario: this.vendedor.Usuario,
                        IdCierre: (id?id:this.idCajaAbierta)
                    }
                });
                this.data = response.data
            } catch (error) {
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        async getCaja(id=null){
            this.ingresos = []
            this.egresos = []
            try {
                const response = await this.axios.get(`${this.API}agente/caja`, { params:
                    {
                        Usuario: this.vendedor.Usuario,
                        IdCierre: (id?id:this.idCajaAbierta)
                    }
                });
                this.ingresos = response.data.filter((dt)=> dt.Tipo==0)
                this.egresos = response.data.filter((dt)=> dt.Tipo==1)
            } catch (error) {
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        getVendedor (opc=0) {
            this.vendedor = {}
            this.cargaCorrecta = false
            this.idCajaAbierta = null
            this.cajaFechaApertura = ''
            this.cajaIdTurno = null
            this.data = []
            this.ingresos = []
            this.egresos = []
            let usuario = sessionStorage.getItem('User')
            if(opc==1){
                this.$swal({
                    title: 'Cargando Información...',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        this.$swal.showLoading();
                    }
                })
            }
            this.axios.get(this.API + 'agente/usuarios/' + usuario)
            .then((response) => {
                if(response.data){
                    this.idCajaAbierta = response.data.CajaAbierta
                    this.cajaFechaApertura = response.data.FechaApertura
                    this.cajaIdTurno = response.data.IdTurno
                    this.cargaCorrecta = true
                    this.setVendedor(response.data)
                }else{
                    this.cargaCorrecta = false
                    this.$toasted.show('No existe data del vendedor')
                }
                if(opc==1){
                    this.$swal.close()
                }
            })
            .catch((error) => {
                if(opc==1){
                    this.$swal.close()
                }
                this.cargaCorrecta = false
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        setVendedor (obj) {
            this.vendedor = obj
            if(this.idCajaAbierta > 0){
                this.getComprobantes()
                this.getCaja()
            }
            
        },
    },
    components: {
        AgenteCierres,
        AgenteRegistrarMov
    }
  }
  </script>
  <style scoped>

    .inputLine .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
    }

  th, td {
    font-size: .9em;
    vertical-align: middle;
  }
  .tdgris {
    background-color: #f1f1f1;
  }
  .total {
    background-color: #e4ff95;
  }
  .total-salida {
    background-color: #ffd4d3;  
  }
  .anulado td{
    /*font-style: italic;*/
    text-decoration: line-through solid red 7% !important;
    text-decoration-thickness: 1px;
  }
  .credito {
    background: #f9f9f9;
    padding: 10px;
  }
  .tabla-detalle {
  }
  .tabla-detalle th {
    background-color: #ffffff;
    font-weight: normal;
  }
  .tabla-detalle th, .tabla-detalle td {
    font-size: .9em;
  }
  .detalle-producto {
    width: 270px;
  }
  .adelantos {
    background-color: #ffe8c5;
  }
  @media print {
    .no-print {
      display: none;
    }
    .si-print {
      display: block;
    }
  }
  </style>
  