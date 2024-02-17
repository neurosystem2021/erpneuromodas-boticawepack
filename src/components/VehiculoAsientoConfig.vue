<template>
    <div>
      <b-form @submit="guardarVehiculo" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigVehiculoAsiento" :title="title" size="md" ref="vehiculoAsientoFormConfig" v-model="show">
          <b-row>
            <b-button variant="success" class="ml-2" @click="agregarColumna()">Agregar Columna</b-button>
            <!--<b-button :variant="editar?'danger':'primary'" class="ml-2" @click="editarLugar()">{{ editar?'Parar edición':'Editar lugares' }}</b-button>
            <b-button variant="primary" class="ml-2" @click="agregarObjeto(true)">Agregar Asiento</b-button>
            <b-button variant="secondary" class="ml-2" @click="agregarObjeto(false)">Agregar Espacio Vacio</b-button>-->
          </b-row>
          <hr>
          <div style="overflow-x: scroll !important;">
            <b-row style="width: max-content; margin: 5px;">
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
            </b-row>

            <!--<b-row style="width: 1150px !important;" class="m-1">
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
            </b-row>-->
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
        if(!this.editar){
          return
        }

        if(this.vehiculoData[indexfila][indexcuadro].opcion==2){
          this.vehiculoData[indexfila][indexcuadro].opcion=0
          return
        }

        this.vehiculoData[indexfila][indexcuadro].opcion++
      },
      editarLugar(){
        this.editar = !this.editar
      },
      eliminarColumna(fila,index){
        this.vehiculoData.splice(index, 1)
      },
      agregarColumna(){
        /*if(this.vehiculoData.length > 4){
          return
        }*/
        let asientos = [
          {id: null, opcion: 0, open: false },{id: null, opcion: 0, open: false },{id: null, opcion: 0, open: false },{id: null, opcion: 0, open: false },{id: null, opcion: 0, open: false }
        ]
        this.vehiculoData.push(asientos)
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
          Plano: []
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
    max-width: 1200px !important;
  }

  #modalConfigVehiculoAsiento {
    /*padding: 10px !important;*/
  }
  .abierto{
    border: red 1.5px solid !important;
  }
  .espacio {
    
  }
  .asiento {
    background-color: #0cb70c;
  }
  .objeto {
    background-color: rgb(142, 56, 56) !important;
  }
  </style>