<template>
  <b-container fluid>
    <b-row>
        <b-col cols="3">
          <div class="clsCuenta">
            <b-row>
                <b-col cols="12">
                    Cuenta:
                    <b-form-select
                    v-model="IdCuenta"
                    :options="lstCuentas"
                    @input="cargarOperaciones()"
                    value-field="IdCuenta"
                    text-field="Cuenta"
                  
                    ></b-form-select>

                </b-col>
                <b-col cols="12">
                    Fecha:
                    <input type="date" class="form-control" v-model="FechaDoc">
                </b-col>
                <b-col cols="12" class="mt-2 text-center">
                    <b-button variant="success" :disabled="!IdCuenta"><i class="fa fa-file-excel" aria-hidden="true"></i> EXPORTAR</b-button>
                    <b-button variant="info" :disabled="!IdCuenta" @click="abrirModalNuevaOperacion()"><i class="fa fa-plus" aria-hidden="true"></i> NUEVO</b-button>
                </b-col>
            </b-row>
          </div>
        </b-col>
        <b-col cols="9" class="px-5">
          <div class="clsCaja">
          <table class="table table-hover">
              <thead>
                <tr style="background: #1A237E; color:#ffff;border: 1px solid #ffffff;">
                  <th style="border: 1px solid #ffffff;">Cuenta</th>
                  <th colspan="6" style="text-align: center;border: 1px solid #ffffff;">Caja</th>
                </tr>
                <tr style="background: #7986CB; color:#ffff;border: 1px solid #ffffff;">
                  <th style="border: 1px solid #ffffff;">ID</th>
                  <th style="border: 1px solid #ffffff;">Usuario</th>
                  <th style="border: 1px solid #ffffff;">Concepto</th>
                  <th style="border: 1px solid #ffffff;">Ingresos</th>
                  <th style="border: 1px solid #ffffff;">Salida</th>
                  <th style="border: 1px solid #ffffff;">Saldo</th>
                  <th style="border: 1px solid #ffffff;"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(cajabanco,index) in lstCajaBanco" :key="index">
                    <td>{{ cajabanco.IdCajaBanco }}</td>
                    <td>{{ cajabanco.UsuarioReg }}</td>
                    <td>{{ cajabanco.Concepto }}</td>
                    <td>{{ cajabanco.Ingresos }}</td>
                    <td>{{ cajabanco.Salida }}</td>
                    <td>{{ (cajabanco.Ingresos?cajabanco.Ingresos: 0 )- (cajabanco.Salidas?cajabanco.Salidas: 0 ) }}</td>
                   
                    <td>
                      <b-button variant="danger" @click="eliminarPago(cajabanco)"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                    </td>
                </tr>
              </tbody>
          </table>
          </div>
        </b-col>
    </b-row>
    <!-- MODAL AGREGAR  PROVEEDOR-->
    <b-modal 
      v-model="modalRegistro" 
      title="Agregar Tipo Caja" 
      backdrop="static" 
      no-close-on-backdrop
      no-close-on-esc 
      hide-footer
      no-stacking
      container="body"  
      v-if="modalRegistro"
      >
        <b-row>
          <b-col :cols="aplicarLetra?7:12">
            <b-row>
              <b-col cols="6">
                <b-row>
                  <b-col cols="12">
                    Tipo Operación:
                    <b-form-select
                      v-model="IdTipoCajaBanco"
                      :options="lstTipoOpe"
                      value-field="IdTipoCajaBanco"
                      text-field="TipoCajaBanco"
                      @input="cambiarTipo($event)"
                    ></b-form-select>
                  </b-col>
                  <b-col cols="12" v-if="mostrarCliente">
                    Cliente:
                    <autocomplete
                        required="required"
                        ref="inputCliente"
                        :url="API + 'clientes'"
                        anchor="ClienteDniRuc"
                        label="writer"
                        placeholder=""
                        :onInput="(val) => newDniRuc = val"
                        :on-select="setCliente" 
                        :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                  </b-col>
                  <b-col cols="12" v-if="mostrarProveedor">
                    Proveedor
                    <autocomplete
                        required="required"
                        ref="inputProveedor"
                        :url="API + 'proveedor'"
                        anchor="ProveedorDniRuc"
                        label="writer"
                        placeholder=""
                        :onInput="(val) => newRuc = val"
                        :on-select="setProveedor" 
                        :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                  </b-col>
                </b-row>
              </b-col>
              <b-col cols="6" v-if="mostrarDatosPrincipalesOperaciones">
                <b-row>
                  <b-col cols="12">
                    Fecha Operacion: 
                    <input type="date" class="form-control" v-model="FechaDoc">
                  </b-col>
                  <b-col cols="12">
                    Concepto: 
                    <input type="text" class="form-control" v-model="Concepto">
                  </b-col>
                  <b-col cols="12">
                    Importe: 
                    <input type="number" class="form-control" v-model="Importe">
                  </b-col>
                </b-row>
              </b-col>
              <b-col cols="12" class="mt-3">
                <div class="card">
                  <div class="card-header">Documentos a aplicar</div>
              
                <div class="card-body">
                <table class="table">
                    <thead>
                      <tr style="background: #1A237E; color:#ffff;border: 1px solid #ffffff;">
                        <th style="border: 1px solid #ffffff;">Id</th>
                        <th style="border: 1px solid #ffffff;">Fecha</th>
                        <th style="border: 1px solid #ffffff;">FechaVen</th>
                        <th style="border: 1px solid #ffffff;">Correlativo</th>
                        <th style="border: 1px solid #ffffff;">Total</th>
                        <th style="border: 1px solid #ffffff;">Aplicado</th>
                        <th style="border: 1px solid #ffffff;">Saldo</th>
                        <th style="border: 1px solid #ffffff;"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(deuda,index) in lstDeudas" :key="index" :style="{background:deuda.Aplicado==1 ? '#FBE9E7' :''}">
                        <td>{{deuda.Id}}</td>
                        <td>{{deuda.FechaDoc}}</td>
                        <td>{{deuda.FechaCredito}}</td>
                        <td>{{deuda.Correlativo}}</td>
                        <td>{{deuda.Total}}</td>
                        <td>{{deuda.Aplicado}}</td>
                        <td>{{deuda.Saldo}}</td>
                        <td>
                          <b-button @click="pagaLetra(deuda)" :disabled="bloquearAplicar" class="mx-1 my-1" v-if="deuda.Aplicado=='MO'" style="width: 100%;" variant="warning">Pagar Letra</b-button>
                          <b-button class="mx-1 my-1" v-else style="width: 100%;" variant="success" :disabled="!Importe || bloquearAplicar" @click="aplicarPago(deuda)">Aplicar</b-button>
                          <!-- <b-button class="mx-1 my-1" style="width: 100%;" variant="success" :disabled="!Importe" @click="aplicarPago(deuda)">Aplicar</b-button> -->
                          <b-button :disabled="bloquearAplicar" class="mx-1 my-1" v-if="deuda.Aplicado=='0'"  style="width: 100%;" variant="info" @click="generarLetras(deuda)">Letras</b-button>
                        </td>

                      </tr>
                      <tr style="background: #BBDEFB; font-weight: bold; ">
                        <td colspan="4" style="text-align: center;border: 1px solid #ffffff;">
                          Sumatoria
                        </td>
                        <td style="border: 1px solid #ffffff;">
                          {{ parseFloat(
                              lstDeudas.reduce((sum,d)=>{
                                return sum + parseFloat(d.Total)
                              },0)
                          ).toFixed(2) }}
                        </td>
                        <td colspan="3"></td>
                        
                      </tr>
                    </tbody>
                </table>
              </div>
              </div>
              </b-col>
              <b-col cols="12" class="mt-3">
                  <div class="card">
                  <div class="card-header">Documentos Aplicados</div>
                  <div class="card-body">
                    <table class="table table-hover">
                    <thead>
                      <tr style="background: #7986CB; border: 1px solid #ffffff;">
                        <th style="border: 1px solid #ffffff;">Id</th>
                        <th style="border: 1px solid #ffffff;">Fecha</th>
                        <th style="border: 1px solid #ffffff;">FechaVen</th>
                        <th style="border: 1px solid #ffffff;">Correlativo</th>
                        <th style="border: 1px solid #ffffff;">Saldo</th>
                        <th style="border: 1px solid #ffffff;">Aplicado</th>
                        <th style="border: 1px solid #ffffff;">Saldo Fin</th>
                        <!-- <th></th> -->
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(deuda,index) in lstAplicado" :key="index">
                        <td>{{deuda.Id}}</td>
                        <td>{{deuda.FechaDoc}}</td>
                        <td>{{deuda.FechaCredito}}</td>
                        <td>{{deuda.Correlativo}}</td>
                        <td>{{deuda.Total}}</td>
                        <td>{{deuda.Aplicado}}</td>
                        <td>{{deuda.Saldo}}</td>
                      </tr>
                      <tr style="background: #BBDEFB; font-weight: bold;">
                        <td colspan="5" style="text-align: center; border: 1px solid #ffffff;">
                          Sumatoria
                        </td>
                        <td style="border: 1px solid #ffffff;">
                          {{  parseFloat(
                            lstAplicado.reduce((sum,deuda)=>{
                            return sum + parseFloat(deuda.Aplicado)
                          },0)
                          ).toFixed(2) }}
                        </td>
                        <td>
                          {{  parseFloat(
                            lstAplicado.reduce((sum,deuda)=>{
                            return sum + parseFloat(deuda.Saldo)
                          },0)
                          ).toFixed(2) }}
                        </td>
                      </tr>
                    </tbody>
                    </table>
                    </div>
                  </div>
                  <div class="card-footer">
                    <b-button :disabled="lstAplicado.length==0" variant="success" @click="GuardarMovimiento()">Guardar</b-button>
                    <b-button variant="warning">Cancelar</b-button>
                  </div>
              </b-col>
            </b-row>
        </b-col>
        <b-col :cols="aplicarLetra ?5:12" v-if="aplicarLetra">
          <b-row>
            <b-col cols="12">
            <h3>Letras</h3>
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>FechaVen</th>
                    <th>Correlativo</th>
                    <th>Total</th>
                    <th>Aplicado</th>
                    <th>Saldo</th>
                  </tr>	
                </thead>
                <tbody>
                  <tr>
                    <td>{{ deuda.Id }}</td>
                    <td>{{ deuda.FechaDoc }}</td>
                    <td>{{ deuda.FechaCredito }}</td>
                    <td>{{ deuda.Correlativo }}</td>
                    <td>{{ deuda.Total }}</td>
                    <td>{{ deuda.Aplicado }}</td>
                    <td>{{ deuda.Saldo }}</td>
                  </tr>
                </tbody>
              </table>
            </b-col>
            <b-col cols="12">
              <b-form-group>
                <b-input-group>
             <span class="mr-5"> Número Letras: </span>
              <b-button variant="info" @click="addLetra()">+</b-button> 
              <input type="number" disabled class="form-control" v-model="nroLetras" style="max-width: 100px;">
              <b-button variant="warning" :disabled="nroLetras==0" @click="removeLetra()" >-</b-button> 
              </b-input-group>
            </b-form-group>
            </b-col>
            <b-col cols="12">
              <table class="table table-hover small">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Fecha Pago</th>
                    <th>Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(letra,index) in letras" :key="index">
                    <td>{{ index+1 }}</td>
                    <td>
                      <input type="date" class="form-control" v-model="letra.FechaLetra">
                      <p style="color:red">{{ letra.errorFecha }}</p>
                    </td>
                    <td><input type="number" step="0.0001" class="form-control"   v-model="letra.MontoLetra"></td>
                  </tr>
                  <tr>
                    <td colspan="2">Total</td>
                    <td colspan="1">
                        {{ 
                          parseFloat(
                            letras.reduce((sum, letra) => {
                              return sum + parseFloat(letra.MontoLetra);
                            }, 0)
                          ).toFixed(2)  
                        }}
                    </td>
                  </tr>
                </tbody>
              </table>
              <p style="color:red">{{ errorLetras }}</p>
            </b-col>
            <b-col cols="12">
                <b-button :disabled="letras.length==0" variant="success" @click="guardarLetras()">Guardar</b-button>
                <b-button  variant="danger" @click="aplicarLetra=!aplicarLetra">Cancelar</b-button>
            </b-col>
          </b-row>
        </b-col>
        </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      Tipo: "",
      bloquearAplicar: false,
      errorLetras: "",
      letras: [],
      nroLetras: 0,
      deuda: {},
      IdTipoCajaBanco: "",
      modalRegistro: false,
      letrasFlag: false,
      IdCuenta: "",
      lstCuentas: [],
      API: config.API,
      FechaDoc: "",
      lstCajaBanco: [],
      lstTipoOpe: [],
      Cliente: {},
      Proveedor: {},
      newDniRuc: {},
      newRuc: {},
      IdTipoCajaBanco: "",
      FechaDocReg: "",
      Concepto: "",
      Importe: "",
      IdCliente: "",
      IdProveedor: "",
      AplicadoDocVenta: "",
      TipoCajaBanco: "",
      lstDeudas: [],
      lstAplicado: [],
      aplicarLetra: false
    };
  },
  mounted() {
    this.FechaDoc = this.$moment().format("YYYY-MM-DD");
    this.cargarCuentas();
    this.ListarTipoOpe();
  },
  methods: {
    abrirModalNuevaOperacion() {
      this.modalRegistro = true;
    },
    addLetra() {
      this.nroLetras += 1;
      this.generLetrasPorCantidadLetras();
    },
    removeLetra() {
      this.nroLetras -= 1;
      this.generLetrasPorCantidadLetras();
    },
    generLetrasPorCantidadLetras() {
      this.letras = [];
      let MontoLetra = this.deuda.Saldo / this.nroLetras;
      for (let index = 0; index < this.nroLetras; index++) {
        this.letras.push({
          IdDocDet: this.deuda.Id,
          FechaLetra: "",
          MontoLetra: parseFloat(MontoLetra).toFixed(2)
        });
      }
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async cargarCuentas() {
      await this.axios(`${this.API}cuentas/listarcuentas`).then(res => {
        if (res.data) {
          this.lstCuentas = res.data;
        }
      });
    },
    async ListarTipoOpe() {
      await this.axios(`${this.API}ListarTipoOpe`).then(res => {
        if (res.data) {
          this.lstTipoOpe = res.data;
        }
      });
    },
    async cargarOperaciones() {
      this.lstCajaBanco = [];
      let params = {
        IdCuenta: this.IdCuenta,
        FechaDoc: this.FechaDoc
      };
      // controllers/server_processingCajaBanco.php?IdCuenta=1&FechaDoc=2025-03-06&_=1741274426193
      await this.axios(`${this.API}ListarCajaBanco`, { params: params }).then(
        res => {
          if (res.data) {
            this.lstCajaBanco = res.data;
          }
        }
      );
    },
    setCliente(obj) {
      this.Cliente = obj;
      this.getClienteDeudas();
    },
    setProveedor(obj) {
      this.Proveedor = obj;
      this.getProveedorDeudas();
    },
    async getProveedorDeudas() {
      await this.axios(
        `${this.API}proveedores/deudas/letras?idProveedor=${
          this.Proveedor.IdProveedor
        }`
      ).then(res => {
        if (res.data) {
          this.lstDeudas = res.data.aaData;
        }
      });
    },
    async getClienteDeudas() {
      await this.axios(
        `${this.API}clientes/deudas/letras?idCliente=${this.Cliente.IdCliente}`
      ).then(res => {
        if (res.data) {
          let deuda = res.data.aaData;
          deuda = deuda.map(d => {
            d.Id = d.IdDocVenta;
            return d;
          });
          this.lstDeudas = deuda;
        }
      });
    },
    aplicarPago(deuda) {
      if (this.Importe < deuda.Total - deuda.Aplicado) {
        deuda.Aplicado = 1;
        this.lstAplicado.push({
          Id: deuda.Id,
          FechaDoc: deuda.FechaDoc,
          Concepto: this.Concepto,
          FechaCredito: deuda.FechaCredito,
          Correlativo: deuda.Correlativo,
          Total: deuda.Total,
          Aplicado: this.Importe,
          Saldo: deuda.Total - this.Importe
        });
        this.bloquearAplicar = true;
      } else {
        this.$swal({
          icon: "warning",
          title: "Advertencia",
          text: "El Importe no puede ser mayor que la deuda. Verifique."
        });
      }
    },
    generarLetras(deuda) {
      console.log(deuda);
      this.deuda = deuda;
      this.letras = [];
      this.nroLetras = 0;
      this.aplicarLetra = true;
    },
    guardarLetras() {
      let val = true;
      this.letras.forEach(letra => {
        if (!letra.FechaLetra) {
          letra.errorFecha = "Fecha requerida";
          val = false;
        } else {
          letra.errorFecha = "";
        }
      });

      let sumTotal = this.letras.reduce((sum, letra) => {
        return sum + parseFloat(letra.MontoLetra);
      }, 0);

      if (parseFloat(this.Saldo).toFixed(2) < parseFloat(sumTotal).toFixed(2)) {
        val = false;
        this.errorLetras = "Defina correctamente el monto en cada Letra.";
      }

      this.$forceUpdate();
    },
    async guardarLetras() {
      let data = {
        Letras: this.letras,
        Tipo: this.Tipo
      };
      await this.axios.post(`${this.API}letras/generar`, data).then(res => {
        if (res.data) {
          this.aplicarLetra = false;
          this.getProveedorDeudas();
        }
      });
    },
    async GuardarMovimiento() {
      let data = {
        IdTipoCajaBanco: this.IdTipoCajaBanco,
        IdCuenta: this.IdCuenta,
        FechaDoc: this.FechaDoc,
        Concepto: this.Concepto,
        Importe: this.Importe,
        TipoCajaBanco: this.Tipo,
        AplicadoDocVenta: this.lstAplicado,
        IdCliente: this.Cliente ? this.Cliente.IdCliente : null,
        IdProveedor: this.Proveedor ? this.Proveedor.IdProveedor : null,
        User: sessionStorage.getItem("User")
      };
      await this.axios.post(`${this.API}movimiento/guardarMovimiento`, data);
      this.cargarOperaciones();
      this.modalRegistro = false;
      this.limpiarLuegoPago();
    },
    limpiarLuegoPago() {
      this.bloquearAplicar = false;
      this.errorLetras = "";
      this.letras = [];
      this.nroLetras = 0;
      this.deuda = {};
      this.IdTipoCajaBanco = "";
      this.modalRegistro = false;
      this.letrasFlag = false;
      this.Cliente = {};
      this.Proveedor = {};
      this.newDniRuc = {};
      this.newRuc = {};
      this.FechaDocReg = "";
      this.Concepto = "";
      this.Importe = "";
      this.IdCliente = "";
      this.IdProveedor = "";
      this.AplicadoDocVenta = "";
      this.TipoCajaBanco = "";
      this.lstDeudas = [];
      this.lstAplicado = [];
      this.aplicarLetra = false;
    },
    pagaLetra(letra) {
      let idDoc = letra.Id.split("-");

      let data = {
        IdLetraCaja: idDoc[1],
        IdDocDet: idDoc[0],
        IdCuenta: this.IdCuenta,
        Concepto: "Pago de Letra de " + letra.Correlativo,
        Importe: letra.Total,
        IdTipoCajaBanco: this.IdTipoCajaBanco,
        Tipo: this.Tipo,
        IdCliente: this.Cliente ? this.Cliente.IdCliente : null,
        IdProveedor: this.Proveedor ? this.Proveedor.IdProveedor : null,
        User: sessionStorage.getItem("User")
      };
      this.$swal({
        icon: "question",
        title: "Pagar Letra",
        text: `Esta seguro en pagar la letra: ${letra.Id}, Correlativo: ${
          letra.Correlativo
        }`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Pagar",
        cancelButtonText: "Cancelar"
      }).then(async respuesta => {
        if (respuesta.isConfirmed) {
          await this.axios.post(`${this.API}letras/pagar`, data);
          this.modalRegistro = false;
          this.limpiarLuegoPago();
          this.cargarOperaciones();
        }
      });
    },
    cambiarTipo(e) {
      let val = this.lstTipoOpe.filter(v => v.IdTipoCajaBanco == e)[0];
      this.Tipo = val.Tipo;
    },
    eliminarPago(data) {
      console.log("cajabanco", data);
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: `¿Desea Eliminar el registro ${
          data.Concepto
        }?. Recuerde que este proceso es Irreversible`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async respuesta => {
        if (respuesta.isConfirmed) {
          await this.axios
            .post(`${this.API}movimiento/eliminarCajaBanco`, data)
            .then(res => {
              if (res) {
                this.$swal({
                  icon: "success",
                  title: "Correcto",
                  text: "Registro Eliminado."
                });
              }
            });
          this.modalRegistro = false;
          this.limpiarLuegoPago();
          this.cargarOperaciones();
        }
      });
    }
  },
  computed: {
    mostrarProveedor() {
      if (this.IdTipoCajaBanco) {
        return this.lstTipoOpe.some(
          v => v.IdTipoCajaBanco == this.IdTipoCajaBanco && v.Tipo == 1
        );
      }
      return false;
    },
    mostrarCliente() {
      if (this.IdTipoCajaBanco) {
        return this.lstTipoOpe.some(
          v => v.IdTipoCajaBanco == this.IdTipoCajaBanco && v.Tipo == 0
        );
      }
      return false;
    },
    mostrarDatosPrincipalesOperaciones() {
      if (!this.IdTipoCajaBanco) {
        return false;
      }

      if (
        Object.keys(this.Cliente).length === 0 &&
        Object.keys(this.Proveedor).length === 0
      ) {
        return false;
      }

      return true;
    }
  }
};
</script>

<style>
.modal-dialog {
  min-width: 90% !important;
}

.clsCaja {
  padding: 15px;
  margin: 5px 0px;
  border: 2px solid #1a237e;
  border-radius: 25px;
  box-shadow: 3px 3px #1a237e;
}
.clsCuenta {
  padding: 15px;
  margin: 5px 0px;
  border: 2px solid #1a237e;
  border-radius: 25px;
  box-shadow: 3px 3px #1a237e;
}

.modal-header {
  background: #1b5e20;
  color: #ffffff;
}

.close {
  color: #ffffff;
}
</style>
