<template>
    <div>
      <b-form @submit="guardarVehiculo" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigVehiculoAsiento" :title="title" size="md" ref="vehiculoAsientoFormConfig" v-model="show">
          <b-row>
            <b-button variant="success" class="ml-2" @click="agregarColumna()">Agregar Columna</b-button>
          </b-row>
          <hr>
          <div style="overflow-x: scroll !important;">
            <b-row style="width: max-content; margin: 5px;">
              <div>
                  <b-img src="static/cabeza.png" style="height: 322px;"  alt=""></b-img>
              </div>
              <div v-for="(fila, i) in vehiculoData" :key="'fila-'+i" style="margin: 2px; border: 1.2px dashed black; width: 70px !important; height: 375px;" >
                <div v-for="(element, index) in fila" :key="'asiento-'+index" @click="accionLugar(fila,i,element,index)" :class="{'espacio': element.opcion == 0, 'asiento': element.opcion == 1, 'objeto': element.opcion == 2, 'abierto': element.open  }"  :id="i + 'popover-espacio' + index" style="border: 1.5px solid black; width: 58px; height: 58px; margin: 5px; display: flex; justify-content: center; align-items: center;">
                  <b-img v-if="element.opcion == 6" src="static/sshh.png" width="40px" height="40px" alt=""></b-img>
                  <b-img v-if="element.opcion == 5" src="static/escalera.png" width="40px" height="40px" alt=""></b-img>
                  <b-img v-if="element.opcion == 4" src="static/tv.png" width="40px" height="40px" alt=""></b-img>
                  <label v-if="element.opcion == 1" style="font-size: 1.8rem; margin-bottom: 0 !important;"><b>{{ element.id }}</b></label>
                  <b-popover :show.sync="element.open" :target="i + 'popover-espacio' + index" title="Opciones de espacio" triggers="click">
										<b-row>
                      <b-col m="12">
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,3)">Espacio</b-button>
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,1)">Asiento</b-button>
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,2)">Objeto</b-button>
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,4)">Television</b-button>
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,5)">Escalera</b-button>
                        <b-button variant="primary" size="sm" block @click="configurarLugar(fila,i,element,index,6)">SS.HH.</b-button>
                        <hr>
                        <b-button variant="danger" size="sm" block @click="cerrarLugar()">Cerrar</b-button>
                      </b-col>
                    </b-row>

									</b-popover>
                </div>
                <div style="width: 58px; height: 58px;" class="text-center mt-2">
                  <span class="btn btn-sm text-danger ml-2" style="font-size: 1rem;" size="sm" @click="eliminarColumna(fila,i)"><i class="fas fa-trash fa-lg"></i></span> 
                </div>
              </div>
              <div>
                <b-img src="static/cola.png" style="height: 322px;" alt=""></b-img>
              </div>
            </b-row>
          </div>
          <div slot="modal-footer" >
            <b-btn  variant="secondary" @click="show=false" class="mr-2">
              Cerrar
            </b-btn>
            <b-btn  variant="success" type="submit">
              Guardar
            </b-btn>
          </div>
        </b-modal>
      </b-form>
    </div>
  </template>
  <script>
  import config from '@/store/config.js'

  export default {
    data () {
      return {
        title: '',
        API: config.API,
        show: false,
        vehiculo: {},
        filas: 0,
        vehiculoData: [],
        editar: false,
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['vehiculoSelect','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.title = 'Editar Asientos'
          let newVal = this.vehiculoSelect  || {}
          if(newVal.IdVehiculo){
            this.vehiculo.IdVehiculo = newVal.IdVehiculo
            if (newVal.Plano) {
              this.vehiculo.Plano = JSON.parse(newVal.Plano)
              this.vehiculoData = JSON.parse(newVal.Plano)
            }
          }else{
            this.show = false
          }
          this.editar = false
        }
      }
    },
    methods: {
      cerrarLugar(){
        this.vehiculoData.map((f)=>{
          f.map((d)=>{
            d.open = false
          })
        })
      },
      configurarLugar(fila,indexfila,cuadro,indexcuadro,opc){
        if(opc == 1){
          let numero = window.prompt('Ingrese el número de asiento (Debe ser un número mayor a cero)')
          if (Number(numero)>0) {
            console.log(Number(numero));
            this.vehiculoData[indexfila][indexcuadro].opcion = opc
            this.vehiculoData[indexfila][indexcuadro].id = Math.trunc(Number(numero))
            this.cerrarLugar()
          }else{
              this.$toasted.show("No se creó el asiento")
          }
        }else{
          this.vehiculoData[indexfila][indexcuadro].opcion = opc
          this.vehiculoData[indexfila][indexcuadro].id = null
          this.cerrarLugar()
        }

      },
      accionLugar(fila,indexfila,cuadro,indexcuadro){

        this.vehiculoData.map((f)=>{
          f.map((d)=>{
            d.open = false
          })
        })
        this.vehiculoData[indexfila][indexcuadro].open =true
      },
      editarLugar(){
        this.editar = !this.editar
      },
      eliminarColumna(fila,index){
        this.vehiculoData.splice(index, 1)
      },
      agregarColumna(){
        let asientos = [
          {id: null, opcion: 0, open: false, ocupado: 0 },{id: null, opcion: 0, open: false, ocupado: 0 },{id: null, opcion: 0, open: false, ocupado: 0 },{id: null, opcion: 0, open: false, ocupado: 0 },{id: null, opcion: 0, open: false, ocupado: 0 }
        ]
        this.vehiculoData.push(asientos)
      },
      clearForm () {
        this.initForm()
      },
      initForm () {
        this.vehiculo = {
          Plano: [],
          Asientos: 0,
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      async guardarVehiculo(e) {
        e.preventDefault();
  
        let asientos = []
        this.vehiculoData.map((f)=>{
          f.map((d)=>{
            if(d.opcion == 1){
              asientos.push(d)
            }
          })
        })

        if(asientos.length == 0){
          this.$toasted.error('El vehículo debe tener asientos para guardarse',{duration:4000})
          return
        }

        let existeDuplicado = false

        for (let i = 0; i < asientos.length; i++) {
          let existe = asientos.filter((e)=>e.id === asientos[i].id).length
          if(existe > 1){
            existeDuplicado = true
            this.$toasted.error('Existen duplicados del asiento: '+asientos[i].id+', deben ser unicos',{duration:4000})
            break;
          }
        }

        if(existeDuplicado){
          return
        }

        this.vehiculo.usuario = sessionStorage.getItem('User')
        this.vehiculo.Plano = this.vehiculoData
        this.vehiculo.Asientos = asientos.length
        try {
            let repuesta = await this.axios.post(`${this.API}transporte/vehiculo/plano`, this.vehiculo);
  
            if (repuesta.data.error) {
              this.$toasted.show(repuesta.data.msg)
              return
            } else {
              this.$toasted.success('Vehiculo guardado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.vehiculoAsientoFormConfig.hide()
            }  
        } catch (error) {
          console.log(error);
          this.$toasted.error('Ha ocurrido un error')
        }
      }
    },
    computed : {
    },
    components: {
    }
  }
  </script>
  <style>
  #modalConfigVehiculoAsiento .modal-dialog{
    max-width: max-content !important;
  }

  #modalConfigVehiculoAsiento {
    /*padding: 10px !important;*/
  }
  .abierto{
    border: red 1.5px solid !important;
  }
  .asiento {
    background-color: #0cb70c;
    color: white;
  }
  .objeto {
    background-color: rgb(102, 79, 79) !important;
  }
  </style>