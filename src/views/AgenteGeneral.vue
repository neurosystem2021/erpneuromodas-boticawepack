<template>
    <div>
        <b-container class="bv-example-row" id="conten" style="border: 2px solid #E6E6E6; padding: 20px;" v-if="entidadSelect == null">
            <b-row>
                <b-col md="5" class="my-1">
                    <b-form-group horizontal label="Buscar" class="mb-0">
                    <b-input-group>
                        <b-form-input v-model="filter" placeholder="Nombre de entidad" @input="currentPage = 1" autocomplete="off" />
                        <b-input-group-append>
                        <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                        </b-input-group-append>
                    </b-input-group>
                    </b-form-group>
                    <br />
                </b-col>

                <b-col md="7" class="my-1">
                    <b-row>
                        <b-col md="5">
                            <b-form-group horizontal label="Mostrar" class="mb-0">
                                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                            </b-form-group>
                        </b-col>
                        <b-col md="3">
                            <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
                        </b-col>
                        <b-col md="4">
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-table small
                        ref="tablaEntidades"
                        :filter="filter"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        striped
                        hover
                        id="my-table" 
                        :busy.sync="isBusy" 
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">


                        <template slot="IdCategoriaEntidad" slot-scope="data">
                            {{ getCategoriaEntidad(data.item.IdCategoriaEntidad) }}
                        </template>

                    
                        <template slot="Orden" slot-scope="data">
                            <b-badge variant="primary" style="font-size: 1rem;">{{ data.item.Orden }}</b-badge>
                        </template>

                        <template slot="Editar" slot-scope="data">
                            <b-button variant="primary" size="sm" @click="editarEntidad(data.item)"><i class="fas fa-pencil-alt fa-lg"></i></b-button>
                        </template>

                        <template slot="Operacion" slot-scope="data">
                            <b-button variant="secondary" size="sm" @click="operacionesEntidad(data.item)"><i class="fas fa-cog fa-lg"></i></b-button>
                        </template>

                        <template slot="Servicio" slot-scope="data">
                            <b-button variant="warning" size="sm" @click="empresasEntidad(data.item)"><i class="fas fa-cogs fa-lg"></i></b-button>
                        </template>

                        <template slot="Anular" slot-scope="data">
                            <b-button v-if="data.item.Anulado" variant="success" size="sm" @click="anularEntidad(data.item,0)"><i class="fas fa-check fa-lg"></i></b-button>
                            <b-button v-else variant="danger" size="sm" @click="anularEntidad(data.item,1)"><i class="fas fa-times fa-lg"></i></b-button>
                        </template>
                    
                    </b-table>
                        <b-row>
                            <b-col md="6" class="my-1">
                                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                            </b-col>
                        </b-row>
                </b-col>
            </b-row>
        </b-container>
        <b-container v-else class="bv-example-row" id="conten2">
            <div v-if="opcionConfiguracion==1">
                <br>
                <b-row>
                    <b-col md="3" class="text-center">
                        <b-button size="md" @click="volverEntidades()" variant="outline-primary"><i class="fas fa-arrow-left fa-lg"></i></b-button>
                    </b-col>
                    <b-col md="6" class="text-center">
                        <h3>{{ entidadSelect.Nombre }} - OPERACIONES</h3>
                    </b-col>
                    <b-col md="3" class="text-center">
                        <b-button variant="success" size="md" @click="nuevaOperacion()"><i class="fa fa-plus" aria-hidden="true"></i> Nueva Operación</b-button>
                    </b-col>
                </b-row>
                <hr>
                <b-row>
                    <b-col>
                        <table id="tableConfig" class="table b-table table-hover table-striped table-sm">
                            <thead>
                                <tr class="table-success">
                                    <th class="text-left">ID</th>
                                    <th class="text-left">Nombre</th>
                                    <th class="text-left">Fecha Registro</th>
                                    <th class="text-center">Tiene Comision</th>
                                    <th class="text-center">Calculado</th>
                                    <th class="text-center">Monto Comision</th>
                                    <th class="text-center">Comision Cada</th>
                                    <th class="text-center">Ganancia</th>
                                    <th class="text-center" width="5%">Editar</th>
                                    <th class="text-center" width="5%">Anular</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(operacion, i) in operaciones" :key="i" :class="{'table-danger': operacion.Anulado}">
                                    <td  style="width: 5% !important">{{ operacion.IdOperacion }}</td>
                                    <td>{{ operacion.Operacion }}</td>
                                    <td>{{ operacion.FechaReg }}</td>
                                    <td class="text-center">
                                        <b-badge :variant="operacion.TieneComision?'success':'danger'" style="font-size: 1rem;">{{ operacion.TieneComision?'SI':'NO' }}</b-badge>
                                    </td>
                                    <td class="text-center">
                                        <b-badge :variant="operacion.Calculo?'success':'danger'" style="font-size: 1rem;">{{ operacion.Calculo?'SI':'NO' }}</b-badge>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="operacion.TieneComision">
                                            <span v-if="operacion.Calculo">
                                                {{ operacion.MontoComision | moneda }}
                                            </span>
                                            <span v-else> - </span>
                                        </span>
                                        <span v-else> - </span>
                                    </td>
                                    <td class="text-center">
                                        <span v-if="operacion.TieneComision">
                                            <span v-if="operacion.Calculo">
                                                <span v-if="!operacion.ComisionFija">
                                                    {{ operacion.MontoMax | moneda }}
                                                </span>
                                                <span v-else>
                                                    -
                                                </span>
                                            </span>
                                            <span v-else> - </span>
                                        </span>
                                        <span v-else> - </span>
                                    </td>
                                    <td class="text-center">
                                        <b-badge :variant="operacion.Ganancia?'success':'danger'" style="font-size: 1rem;">{{ operacion.Ganancia?'SI':'NO' }}</b-badge>
                                    </td>
                                    <td class="text-center">
                                        <b-button variant="primary" size="sm" @click="editarOperacion(operacion)"><i class="fas fa-pencil-alt fa-lg"></i></b-button>
                                    </td>
                                    <td class="text-center">
                                        <b-button v-if="operacion.Anulado" variant="success" size="sm" @click="anularServicio(operacion,0)"><i class="fas fa-check fa-lg"></i></b-button>
                                        <b-button v-else variant="danger" size="sm" @click="anularServicio(operacion,1)"><i class="fas fa-times fa-lg"></i></b-button>
                                    </td>
                                </tr>
                                <tr v-if="operaciones.length == 0">
                                    <td colspan="10" class="text-center" style="font-weight: bold">NO TIENE OPERACIONES</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <operacion-form ref="operacionModal" :operacion-select="operacionSelect" :entidad-select="entidadSelect" :cbGuardar="actualizarOperaciones"></operacion-form>
            </div>
            <div v-if="opcionConfiguracion==2">
                <div v-if="empresaSelect == null">
                    <br>
                    <b-row>
                        <b-col md="3" class="text-center">
                            <b-button size="md" @click="volverEntidades()" variant="outline-primary"><i class="fas fa-arrow-left fa-lg"></i></b-button>
                        </b-col>
                        <b-col md="6" class="text-center">
                            <h3>{{ entidadSelect.Nombre }} - EMPRESAS</h3>
                        </b-col>
                        <b-col md="3" class="text-center">
                            <b-button variant="success" size="md" @click="nuevaEmpresa()"><i class="fa fa-plus" aria-hidden="true"></i> Nueva Empresa</b-button>
                        </b-col>
                    </b-row>
                    <hr>
                    <b-row>
                        <b-col>
                            <table id="tableConfig" class="table b-table table-hover table-striped table-sm">
                                <thead>
                                    <tr class="table-warning">
                                        <th class="text-left">Codigo</th>
                                        <th class="text-left">Empresa</th>
                                        <th class="text-left">Categoria</th>
                                        <th class="text-left">Fecha Registro</th>
                                        <th class="text-center">Servicios</th>
                                        <th class="text-center">Anular</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, i) in empresas" :key="i" :class="{'table-danger': item.Anulado}">
                                        <td  style="width: 5% !important">{{ item.IdEmpresaEntidad }}</td>
                                        <td>{{ item.Empresa }}</td>
                                        <td>{{ item.CategoriaEmpresa }}</td>
                                        <td>{{ item.FechaReg }}</td>
                                        <td class="text-center"><b-button variant="secondary" size="sm" @click="serviciosEmpresa(item)"><i class="fas fa-list fa-lg"></i></b-button></td>
                                        <td class="text-center">
                                            <b-button v-if="item.Anulado" variant="success" size="sm" @click="anularEmpresa(item,0)"><i class="fas fa-check fa-lg"></i></b-button>
                                            <b-button v-else variant="danger" size="sm" @click="anularEmpresa(item,1)"><i class="fas fa-times fa-lg"></i></b-button>
                                        </td>
                                    </tr>
                                    <tr v-if="empresas.length == 0">
                                        <td colspan="10" class="text-center" style="font-weight: bold">NO TIENE EMPRESAS</td>
                                    </tr>
                                </tbody>
                            </table> 
                        </b-col>
                    </b-row>
                    <empresa-agente-form ref="empresaAgenteForm" :entidad-select="entidadSelect" :cbGuardar="actualizarEmpresas"></empresa-agente-form>
                </div>
                <div v-else>
                    <br>
                    <b-row>
                        <b-col md="3" class="text-center">
                            <b-button size="md" @click="volverEmpresa()" variant="outline-primary"><i class="fas fa-arrow-left fa-lg"></i></b-button>
                        </b-col>
                        <b-col md="6" class="text-center">
                            <h4>{{ empresaSelect.Empresa }} - {{ empresaSelect.CategoriaEmpresa }} - SERVICIO</h4>
                        </b-col>
                        <b-col md="3" class="text-center">
                            <b-button variant="primary" size="md" @click="nuevoServicio()"><i class="fa fa-plus" aria-hidden="true"></i> Nueva Servicio</b-button>
                        </b-col>
                    </b-row>
                    <hr>
                    <b-row>
                        <b-col>
                            <table id="tableConfig" class="table b-table table-hover table-striped table-sm">
                                <thead>
                                    <tr class="table-primary">
                                        <th class="text-left">ID</th>
                                        <th class="text-left">Nombre</th>
                                        <th class="text-left">Fecha Registro</th>
                                        <th class="text-center">Tiene Comision</th>
                                        <th class="text-center">Calculado</th>
                                        <th class="text-center">Monto Comision</th>
                                        <th class="text-center">Comision Cada</th>
                                        <th class="text-center">Ganancia</th>
                                        <th class="text-center" width="5%">Editar</th>
                                        <th class="text-center" width="5%">Anular</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(servicio, i) in servicios" :key="i" :class="{'table-danger': servicio.Anulado}">
                                        <td  style="width: 5% !important">{{ servicio.IdServicio }}</td>
                                        <td>{{ servicio.Servicio }}</td>
                                        <td>{{ servicio.FechaReg }}</td>
                                        <td class="text-center">
                                            <b-badge :variant="servicio.TieneComision?'success':'danger'" style="font-size: 1rem;">{{ servicio.TieneComision?'SI':'NO' }}</b-badge>
                                        </td>
                                        <td class="text-center">
                                            <b-badge :variant="servicio.Calculo?'success':'danger'" style="font-size: 1rem;">{{ servicio.Calculo?'SI':'NO' }}</b-badge>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="servicio.TieneComision">
                                                <span v-if="servicio.Calculo">
                                                    {{ servicio.MontoComision | moneda }}
                                                </span>
                                                <span v-else> - </span>
                                            </span>
                                            <span v-else> - </span>
                                        </td>
                                        <td class="text-center">
                                            <span v-if="servicio.TieneComision">
                                                <span v-if="servicio.Calculo">
                                                    <span v-if="!servicio.ComisionFija">
                                                        {{ servicio.MontoMax | moneda }}
                                                    </span>
                                                    <span v-else>
                                                        -
                                                    </span>
                                                </span>
                                                <span v-else> - </span>
                                            </span>
                                            <span v-else> - </span>
                                        </td>
                                        <td class="text-center">
                                            <b-badge :variant="servicio.Ganancia?'success':'danger'" style="font-size: 1rem;">{{ servicio.Ganancia?'SI':'NO' }}</b-badge>
                                        </td>
                                        <td class="text-center">
                                            <b-button variant="primary" size="sm" @click="editarServicio(servicio)"><i class="fas fa-pencil-alt fa-lg"></i></b-button>
                                        </td>
                                        <td class="text-center">
                                            <b-button v-if="servicio.Anulado" variant="success" size="sm" @click="anularServicio(servicio,0)"><i class="fas fa-check fa-lg"></i></b-button>
                                            <b-button v-else variant="danger" size="sm" @click="anularServicio(servicio,1)"><i class="fas fa-times fa-lg"></i></b-button>
                                        </td>
                                    </tr>
                                    <tr v-if="servicios.length == 0">
                                        <td colspan="10" class="text-center" style="font-weight: bold">NO TIENE SERVICIOS</td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-col>
                    </b-row>
                    <servicio-form ref="servicioModal" :servicio-select="servicioSelect" :empresa-select="empresaSelect" :cbGuardar="actualizarServicios"></servicio-form>
                </div>
            </div>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
import EmpresaAgenteForm from '@/components/EmpresaAgenteForm'
import ServicioForm from '@/components/ServicioForm'
import OperacionForm from '@/components/OperacionForm'

export default {
    beforeMount () {
    },
  	mounted () {
	},
	data () {
		return {
			API: config.API,
            //VARIABLES DE ENTIDAD
            pageOptions: [ 5, 10, 15, 50, 100 ],
            filter: null,
            sortBy: 'IdEntidad',
            sortDesc: false,
            items: [],
            isBusy: false,
            fields: [
                    { key: 'IdEntidad', label: 'Codigo', sortable: true },
                    { key: 'Nombre', sortable: true },
                    { key: 'IdCategoriaEntidad', label: 'Categoria', sortable: true},
                    { key: 'FechaReg', label: 'Fecha Registro', sortable: true },
                    { key: 'Orden', label: 'Orden', sortable: true, class: 'text-center'},
                    { key: 'Editar', label: 'Editar', sortable: false, class: 'text-center' },
                    { key: 'Operacion', label: 'Operaciones', sortable: false, class: 'text-center' },
                    { key: 'Servicio', label: 'Servicios', sortable: false, class: 'text-center' },
                    { key: 'Anular', label: 'Acción', sortable: false, class: 'text-center' },
            ],
            totalRows: 50,
            perPage: 10,
            currentPage: 1,
            entidadSelect: null,
            categorias:[
                { value: 1, nombre: 'ENTIDAD BANCARIA' },
                { value: 2, nombre: 'BILLETERA MOVIL' },
                { value: 3, nombre: 'SERVICIOS' },
                { value: 4, nombre: 'OTROS'}
            ],
            //FIN VARIABLES DE ENTIDAD
            //VARIABLES EMPRESA ENTIDAD
            empresas: [],
            empresaSelect: null,
            servicios: [],
            servicioSelect: {},
            opcionConfiguracion: null,
            operaciones: [],
            operacionSelect: {},
		}
	},
	computed: {

	},
	methods: {
        sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms * 1000));
        },
        /* METODOS DE OPERACIONES */
        nuevaOperacion(){
            this.operacionSelect = {}
            this.$refs.operacionModal.show = true;
        },
        editarOperacion(operacion){
            this.operacionSelect = operacion
            this.$refs.operacionModal.show = true;
        },
        actualizarOperaciones(){
            this.getOperacionesEntidad()
        },
        async getOperacionesEntidad(entidad=null){
            this.$swal({
                title: 'Cargando Operaciones de '+ (entidad?entidad.Nombre:this.entidadSelect.Nombre),
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    this.$swal.showLoading();
                }
            })
            this.operaciones = []
            try {
                const response = await this.axios.get(`${this.API}agente/entidad/operacion`, { params:
                    {
                        q: this.busqueda || '',
                        IdEntidad: entidad?entidad.IdEntidad:this.entidadSelect.IdEntidad,
                        noLimit: 1,
                        mostrarTodo: 1,
                    }
                });
                this.$swal.close()
                this.operaciones = response.data.data
                if(entidad){
                    this.entidadSelect = entidad
                    this.opcionConfiguracion = 1
                }

            } catch (error) {
                this.$swal.close()
                console.log(error);
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        /* METODOS EMPRESA SERVICIOS */
        nuevoServicio(){
            this.servicioSelect = {}
            this.$refs.servicioModal.show = true;
        },
        editarServicio(servicio){
            this.servicioSelect = servicio
            this.$refs.servicioModal.show = true;
        },
        volverEmpresa(){
            this.empresaSelect = null
            this.getEmpresasServicio()
        },
        serviciosEmpresa(empresa){
            //this.empresaSelect = empresa
            this.getServicios(empresa)
        },
        actualizarServicios(){
            this.getServicios()
        },
        async getServicios(empresa){
            this.$swal({
                title: 'Cargando servicios de '+ (empresa?empresa.Empresa:this.empresaSelect.Empresa),
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    this.$swal.showLoading();
                }
            })
            this.servicios = []
            try {
                const response = await this.axios.get(`${this.API}agente/entidad/empresa/servicio`, { params:
                    {
                        q: this.busqueda || '',
                        IdEmpresaEntidad: empresa?empresa.IdEmpresaEntidad:this.empresaSelect.IdEmpresaEntidad,
                        noLimit: 1,
                        mostrarTodo: 1,
                    }
                });
                this.$swal.close()
                this.servicios = response.data.data
                if(empresa){
                    this.empresaSelect = empresa
                }

            } catch (error) {
                this.$swal.close()
                console.log(error);
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        /* METODOS DE EMPRESA ENTIDAD */
        actualizarEmpresas(){
            this.getEmpresasServicio()
        },
        nuevaEmpresa(){
            this.$refs.empresaAgenteForm.show = true;
        },
        async getEmpresasServicio(entidad=null){
            this.$swal({
                title: 'Cargando Empresas de '+ (entidad?entidad.Nombre:this.entidadSelect.Nombre),
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    this.$swal.showLoading();
                }
            })
            this.empresas = []
            try {
                const response = await this.axios.get(`${this.API}agente/entidad/empresa`, { params:
                    {
                        q: this.busqueda || '',
                        IdEntidad: entidad?entidad.IdEntidad:this.entidadSelect.IdEntidad,
                        noLimit: 1,
                        mostrarTodo: 1,
                    }
                });
                this.$swal.close()
                this.empresas = response.data.data
                if(entidad){
                    this.entidadSelect = entidad
                    this.opcionConfiguracion = 2
                }

            } catch (error) {
                this.$swal.close()
                console.log(error);
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        /* FIN DE METODOS EMPRESA ENTIDAD */
        /* METODOS DE ENTIDAD */
        volverEntidades(){
            this.actualizarTabla()
        },
        getCategoriaEntidad(val){
            let index = this.categorias.findIndex((item)=>item.value == val)
            if(index !== -1){
                return this.categorias[index].nombre
            }
            return 'NO ESPECIFICADO'
        },
        operacionesEntidad(entidad){
            this.getOperacionesEntidad(entidad)
        },
        empresasEntidad(entidad){
            //this.entidadSelect = entidad
            this.getEmpresasServicio(entidad)
        },
        editarEntidad(operacion){
        },
        anularEntidad(entidad, anulado){
            this.$swal({
            target: document.getElementById('conten'),
            title: 'Se '+(anulado==1?'anulará':'activará')+' la entidad <b>'+entidad.Nombre+'</b> ¿Está seguro? ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, '+(anulado==1?'anular':'activar'),
            cancelButtonText: 'No, atrás'
            }).then(async (result) => {
                if (result.isConfirmed?result.isConfirmed:false) {
                    this.$swal({
                        title: (anulado==1?'Anulando':'Activando')+'... Espere porfavor....',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            this.$swal.showLoading();
                        }
                    })

                    try {
                        let repuesta = await this.axios.post(`${this.API}entidad/anular`,{
                            IdEntidad: entidad.IdEntidad,
                            Anulado: anulado
                        });
                        
                        if(repuesta.data.success){
                            this.$toasted.success('La entidad se '+(anulado==1?'anuló':'activó'))
                        }
                        this.actualizarTabla()
                        this.$swal.close()
                    } catch (error) {
                        this.$swal.close()
                        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                    }       
                }
            })
            
        },
        anularEmpresa(empresa, anulado){
            this.$swal({
            target: document.getElementById('conten2'),
            title: 'Se '+(anulado==1?'anulará':'activará')+' la empresa <br><b>'+empresa.Empresa+' - '+empresa.CategoriaEmpresa+'</b></br> ¿Está seguro? ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, '+(anulado==1?'anular':'activar'),
            cancelButtonText: 'No, atrás'
            }).then(async (result) => {
                if (result.isConfirmed?result.isConfirmed:false) {
                    this.$swal({
                        title: (anulado==1?'Anulando':'Activando')+'... Espere porfavor....',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            this.$swal.showLoading();
                        }
                    })

                    try {
                        let repuesta = await this.axios.post(`${this.API}empresa/anular`,{
                            IdEmpresaEntidad: empresa.IdEmpresaEntidad,
                            Anulado: anulado
                        });
                        
                        if(repuesta.data.success){
                            this.$toasted.success('La empresa se '+(anulado==1?'anuló':'activó'))
                        }
                        this.actualizarEmpresas()
                        this.$swal.close()
                    } catch (error) {
                        this.$swal.close()
                        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                    }       
                }
            })  
        },
        anularServicio(servicio, anulado){
            this.$swal({
            target: document.getElementById('conten2'),
            title: 'Se '+(anulado==1?'anulará':'activará')+' el servicio <br><b>'+servicio.Servicio+' - '+servicio.Empresa+'</b></br> ¿Está seguro? ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, '+(anulado==1?'anular':'activar'),
            cancelButtonText: 'No, atrás'
            }).then(async (result) => {
                if (result.isConfirmed?result.isConfirmed:false) {
                    this.$swal({
                        title: (anulado==1?'Anulando':'Activando')+'... Espere porfavor....',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            this.$swal.showLoading();
                        }
                    })

                    try {
                        let repuesta = await this.axios.post(`${this.API}servicio/anular`,{
                            IdServicio: servicio.IdServicio,
                            Anulado: anulado
                        });
                        
                        if(repuesta.data.success){
                            this.$toasted.success('El servicio se '+(anulado==1?'anuló':'activó'))
                        }
                        this.actualizarServicios()
                        this.$swal.close()
                    } catch (error) {
                        this.$swal.close()
                        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                    }       
                }
            })
        },
        actualizarTabla(){
            this.entidadSelect = null
            this.opcionConfiguracion = null
            setTimeout(() => {
                this.$refs.tablaEntidades.refresh()    
            }, 50);
        },
        myProvider (ctx) {
            let url = this.API + 'agente/entidad?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
            url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
            let promise = this.axios.get( url )

            return promise.then((result) => {
            this.entidadSelect = null
            this.opcionConfiguracion = null
            this.items = result.data.data
            this.totalRows = result.data.count.total
            this.items.map(r=>{
                if(r.Anulado==1){
                    r._rowVariant='danger'
                }
            })
            return(this.items)
            }).catch(error => {
            return []
            })
        },
        /* FIN DE METODOS DE ENTIDAD */
	},
	components: {
        EmpresaAgenteForm,
        ServicioForm,
        OperacionForm
	}
}
</script>
<style>
</style>