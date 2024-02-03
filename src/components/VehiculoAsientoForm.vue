<template>
    <div>
      <b-form @submit="guardarVehiculo" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigVehiculoAsiento" :title="title" size="md" ref="vehiculoAsientoForm" v-model="show">
          <b-row>
            <b-button variant="success" class="ml-2" @click="agregarFila()">Agregar Fila</b-button>
            <b-button variant="primary" class="ml-2" @click="agregarObjeto(true)">Agregar Asiento</b-button>
            <b-button variant="secondary" class="ml-2" @click="agregarObjeto(false)">Agregar Espacio Vacio</b-button>
          </b-row>
          <div style="overflow-x: scroll !important;">
            <b-row style="width: 1150px !important;" class="m-1">
              <div style="width: 139px;">
                <b-img src="static/cabeza.png"  alt=""></b-img>
              </div>
              <div style="display: grid; align-content: center; width: 972px !important;">
                <b-row>
                  <b-col md="12" dir="ltr">
                    <draggable class="m-2 row" tag="div" :list="fila" group="people" @change="log" v-for="(fila, i) in vehiculoData" :key="'fila-'+i" style="border: 2px dashed black; height: 75px;">
                          <div v-for="(element, index) in fila"
                            :key="element.id+'-'+(element.asiento?'asiento':'espacio')" :class="{'espacio': !element.asiento }" style="background-color: #0cb70c; width: 58px; height: 62px; margin: 5px;"  >
                            <b class="p-1" style="font-size: 1.5rem; color: black;">{{ (element.asiento?element.id:'*') }}</b>
                          </div>
                    </draggable>
                  </b-col>
                </b-row>
              </div>
              <div md="1" style="width: 27px;">
                <b-img src="static/cola.png"  alt=""></b-img>
              </div>
            </b-row>
          </div>
          <div slot="modal-footer" >
            <b-btn  variant="secondary" @click="show=false">
              Cerrar
            </b-btn>
          </div>
        </b-modal>
      </b-form>
    </div>
  </template>
  <script>
  import config from '@/store/config.js'
  import draggable from "vuedraggable";

  export default {
    data () {
      return {
        title: '',
        API: config.API,
        show: false,
        vehiculo: {},
        filas: 0,
        vehiculoData: []
        /*list1: [
          { name: "John", id: 1 },
          { name: "Joao", id: 2 },
          { name: "Jean", id: 3 },
          { name: "Gerard", id: 4 }
        ],
        list2: [
          { name: "Juan", id: 5 },
          { name: "Edgard", id: 6 },
          { name: "Johnson", id: 7 }
        ]*/
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
            this.vehiculo.Vehiculo = newVal.Vehiculo
            this.vehiculo.Placa = newVal.Placa
            this.vehiculo.Ejes = newVal.Ejes
            this.vehiculo.Propietario = newVal.Propietario
            this.vehiculo.Asientos = newVal.Asientos
          }else{
            this.title = 'Nuevo Vehiculo'
            this.clearForm()
          }
        }
      }
    },
    methods: {
      agregarFila(){
        if(this.vehiculoData.length > 4){
          return
        }
        this.vehiculoData.push([])
      },
      agregarObjeto(opc){
        if(!(this.vehiculoData.length > 0)){
          this.$toasted.show('Debe tener una fila minimo para agregar asiento u obstaculo')
          return
        }

        if(this.vehiculoData[0].length==14){
          this.$toasted.show('Debe mover los asientos a otra fila para seguir agregando')
          return
        }
        let maxNumber = 0
        let array = []
        this.vehiculoData.map((v)=>{
          let dataFin = v.filter((v)=>v.asiento == opc)
          let maxValue = dataFin.reduce((acc, value) => {
	          return (acc = acc > value.id ? acc : value.id);
          }, 0);

          if(maxValue){
            array.push(maxValue)
          }
        })

        if(array.length>0){
          maxNumber = Math.max(...array);
        }

        maxNumber++
        console.log(opc);
        this.vehiculoData[0].push({id: maxNumber, asiento:opc })
      },
      //DRAG
      /*add: function() {
        this.list.push({ name: "Juan" });
      },
      replace: function() {
        this.list = [{ name: "Edgard" }];
      },
      clone: function(el) {
        return {
          name: el.name + " cloned"
        };
      },*/
      log: function(evt) {
        window.console.log(evt);
      },
      //FIN DRAG
      clearForm () {
        this.initForm()
      },
      initForm () {
        this.vehiculo = {
          Vehiculo: '',
          Placa: '',
          Ejes: 0,
          Asientos: 0,
          Anulado: 0,
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      async cambiarEstado(vehiculo){
        let r = confirm('¿Esta seguro de '+(vehiculo.Anulado ? 'Habilitar':'Anular')+' este vehiculo?')
        if (r) {
          try {
            let respuesta = await this.axios.post(this.API + 'transporte/vehiculo/estado', {
              IdVehiculo: vehiculo.IdVehiculo,
              Estado: vehiculo.Anulado? 0 : 1
            })
            if (respuesta.data.updated) {
              this.$toasted.info('El vehiculo con Placa ' + vehiculo.Placa + ' ha sido '+(vehiculo.Anulado ? 'Habilitado' : 'Anulado'))
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.vehiculoAsientoForm.hide()
            }
          } catch (error) {
            this.$toasted.error('Ha ocurrido un error')
          }
        }
      },
      async guardarVehiculo(e) {
        e.preventDefault();
  
        for (const [key, value] of Object.entries(this.vehiculo)) {
          if((''+value).trim().toUpperCase()=='' && key!='Asientos' && key!='Ejes'){
            this.$toasted.show(`Complete el dato ${key} es requerido`)
            this.vehiculo[key] = (''+value).trim()
            return
          }

          if((key=='Asientos' || key=='Ejes') && !(value > 0)){
            this.$toasted.show(`Complete el dato ${key} es requerido y númerico`)
            return
          }
        }

        for (const [key, value] of Object.entries(this.vehiculo)) {
          if(key!='Asientos' && key!='Ejes'){
            this.vehiculo[key] = (''+value).trim().toUpperCase()
          }
        }

        this.vehiculo.usuario = sessionStorage.getItem('User')
        try {
            let repuesta = await this.axios.post(`${this.API}transporte/vehiculo`, this.vehiculo);
  
            if (repuesta.data.error) {
              this.$toasted.show(repuesta.data.msg)
              return
            } else {
              this.$toasted.success('Vehiculo guardado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.vehiculoForm.hide()
            }  
        } catch (error) {
          this.$toasted.error('Ha ocurrido un error')
        }
      }
    },
    computed : {
    },
    components: {
      draggable
    }
  }
  </script>
  <style>
  #modalConfigVehiculoAsiento .modal-dialog{
    max-width: 1200px !important;
  }

  #modalConfigVehiculoAsiento {
    padding: 10px !important;
  }
  .espacio {
    background-color: rgb(142, 56, 56) !important;
  }
  </style>