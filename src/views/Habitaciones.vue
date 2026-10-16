<template>
  <div class="nst-screen nst-screen--habitaciones">
    <b-container class="px-2" fluid>
      <div class="piso" v-for="(pi, i) in pisos" :key="i">
        <div v-if="getMostrarPiso(pi)">
          <header class="projects-hero">
            <div><span class="projects-eyebrow">GESTIÓN DE SERVICIOS</span><h1>{{ pi === 1 ? 'Servicios Huánuco' : 'Servicios Pucallpa' }}</h1><p>Organiza tus proyectos, clientes y equipo en un solo lugar.</p></div>
            <div class="projects-hero-actions"><span class="projects-count">{{ getHabitacionesPiso(pi).length }} proyectos</span><b-button variant="success" size="sm" :id="'nuevoProyecto_' + i" v-b-toggle="'crearProyecto_' + i"><i class="fas fa-plus" aria-hidden="true"></i> Agregar proyecto</b-button></div>
          </header>
          <b-collapse :id="'crearProyecto_' + i" class="project-create-panel">
            <h2>Nuevo proyecto</h2><p class="project-create-hint">Selecciona el cliente y responsable, y confirma el periodo del servicio.</p>
            <label>Seleccione un cliente:</label>
            <b-input-group>
              <autocomplete
				        ref="cboClientePrincipal"
                :on-focus="() => listarProyectoCampo('cboClientePrincipal')"
                @mouseenter.native="listarProyectoCampo('cboClientePrincipal')"
                @click.native="abrirListaProyecto($event, 'cboClientePrincipal')"  
                required="required"
                :url="API + 'clientes'"
                anchor="ClienteDniRuc"
                label="writer"
                placeholder=""
                :onInput="(val) => (newDniRuc = val)"
                :on-select="setCliente"
                :classes="{ input: 'form-control' }"
                id="cboCliente"
              >
              </autocomplete>
              <b-input-group-append>
                <clientes-form :project-mode="true"
				        :idModal="'modalProyectoPiso_' + i"
				        :newDniRuc="newDniRuc"
				        @onAddCliente="(obj) => { setCliente(obj); pegarTextoBuscador('cboClientePrincipal', obj); }"
                ></clientes-form>
                <clientes-form v-if="selectCliente.IdCliente" :project-mode="true" :id-modal="'editarClienteProyecto_' + i" :edit-cliente="selectCliente" txt-button="Editar" button-icon="fas fa-pencil-alt" button-title="Editar cliente y teléfono" button-class="project-edit-client" @onAddCliente="(obj) => { setCliente(Object.assign({}, selectCliente, obj)); pegarTextoBuscador('cboClientePrincipal', obj); }"></clientes-form>
              </b-input-group-append>
            </b-input-group><p v-if="selectCliente.IdCliente" class="project-client-phone"><i class="fas fa-phone" aria-hidden="true"></i> {{ selectCliente.Telefono || 'Sin teléfono. Usa Editar para agregarlo.' }}</p>
            <label>Seleccione un Personal Servicio:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'empleado'"
                anchor="EmpleadoDniRuc"
                label="writer"
                placeholder=""
                :onInput="(val) => (newDniRucEmpleado = val)"
                :on-select="setEmpleado"
                :classes="{ input: 'form-control' }"
                id="cboEmpleado"
                ref="cboEmpleado"
                :on-focus="() => listarProyectoCampo('cboEmpleado')"
                @mouseenter.native="listarProyectoCampo('cboEmpleado')"
                @click.native="abrirListaProyecto($event, 'cboEmpleado')"
              >
              </autocomplete>
              <b-input-group-append>
                <empleado-form
                  :newDniRuc="newDniRucEmpleado"
                  @onAddEmpleado="setEmpleado"
                ></empleado-form>
              </b-input-group-append>
            </b-input-group>
            <br />
            <div class="project-description-label"><label>Descripción:</label><b-button type="button" :variant="dictatingDescription ? 'danger' : 'outline-info'" :class="{'project-voice-active':dictatingDescription}" size="sm" @click="toggleDescriptionDictation" :aria-pressed="dictatingDescription"><i :class="dictatingDescription ? 'fas fa-stop' : 'fas fa-microphone'" aria-hidden="true"></i> {{ dictatingDescription ? 'Escuchando · Detener' : 'Dictar descripción' }}</b-button></div><p v-if="dictatingDescription" class="project-dictation-status" role="status">Escuchando… Puedes hacer pausas de hasta 10 segundos.</p>
            <textarea
              class="form-control"
              placeholder="Descripción"
              cols="12"
              rows="3"
              style="width: 100%"
              v-model="selectCliente.Descripcion"
              :readonly="dictatingDescription"
            >
            </textarea>
            <br />
            <label>Fecha Inicio:</label>
            <b-input-group>
              <b-form-input
                v-model="selectCliente.FechaAlquilerInicio"
                type="date"
                placeholder="Fecha Inicio"
              />
            </b-input-group>
            <br />
            <label>Fecha Fin:</label>
            <b-input-group>
              <b-form-input
                v-model="selectCliente.FechaAlquilerFin"
                type="date"
                placeholder="Fecha Fin"
              ></b-form-input>
            </b-input-group>
            <br />
            <b-button variant="success" size="sm" @click="nuevoProyecto()"
              >Empezar Proyecto</b-button
            >
          </b-collapse>
          <!-- </b-modal> -->
          <!-- <b-btn v-show="hab.EstadoHabitacion !== 3" :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Empezar</b-btn>	
              <b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Empezar Proyecto">
                <label>Seleccione un cliente:</label>
                <b-input-group>
                  <autocomplete
                      required="required"
                      :ref="'inputCliente2' + index"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => newDniRuc = val"
                      :on-select="setCliente" 
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                  <b-input-group-append>
                    <!--<proveedores-tabla></proveedores-tabla>
                    <clientes-form :project-mode="true" @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form>
                  </b-input-group-append>
                </b-input-group>
                <br>
                <label>Descripcion:</label>
                <textarea 
                  class="form-control"
                  placeholder="Descripción"
                  cols="12" rows="3"
                  style="width: 100%"
                  v-model="selectCliente.Descripcion">
                </textarea>

                <br>
                <label>Fecha Inicio:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerInicio"
                    type="date"
                    placeholder="Fecha Inicio"></b-form-input>
                </b-input-group>
                <br>
                <label>Fecha Fin:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerFin"
                    type="date"
                    placeholder="Fecha Fin"></b-form-input>
                </b-input-group>
                <br>

                <b-button variant="success" size="sm" @click="alquilarHab(hab)">Empezar Proyecto</b-button>	
              </b-popover> -->
        </div>
        <section class="nst-panel nst-panel--filters"><b-row class="projects-grid">
          <b-col
            class="habitacion project-cell"
            md="2"
            v-for="(hab, index) in getHabitacionesPiso(pi)"
            :key="index"
          >
            <b-card
              :bg-variant="getVariant(hab)"
              :class="
                hab.EstadoHabitacion == 3
                  ? getClaseTexto(hab)
                  : 'text-center text-light'
              "
            >
              <div class="project-card-heading"><span class="project-icon"><i class="fas fa-briefcase" aria-hidden="true"></i></span><span class="project-status">{{ getVariant(hab) === 'danger' ? 'Plazo vencido' : hab.EstadoHabitacion == 3 ? 'En curso' : hab.EstadoHabitacion == 2 ? 'Reservado' : 'Disponible' }}</span></div>
              <h2 class="project-name">{{ hab.Producto }}</h2>
              <p class="project-client">{{ hab.Cliente || 'Sin cliente asignado' }}</p><p class="project-card-creator"><i class="fas fa-user" aria-hidden="true"></i> Generado por: <b>{{ hab.UsuarioCreador || "Sin registro hist?rico" }}</b></p><p class="project-card-phone"><i class="fas fa-phone" aria-hidden="true"></i> {{ hab.Telefono || 'Sin teléfono registrado' }}</p>
              <div class="project-amount project-balance-summary"><div class="project-balance-main"><span>Saldo pendiente</span><strong>S/ {{ hab.SaldoPendiente | moneda }}</strong></div><div class="project-balance-details"><div v-if="Number(hab.IdProforma) > 0 && Number(hab.TotalProf) > 0" class="project-proforma-total"><span>Monto de proforma</span><b>S/ {{ hab.TotalProf | moneda }}</b></div><div><span>Total del proyecto</span><b>S/ {{ hab.TotalServicio | moneda }}</b></div><div><span>Adelanto recibido</span><b>S/ {{ hab.TotalAdelantos | moneda }}</b></div></div></div>
              <div class="project-meta" v-if="hab.EstadoHabitacion == 3"><div><i class="far fa-calendar-alt" aria-hidden="true"></i><span><small>Periodo del servicio</small>{{ hab.FechaAlquiler || 'Sin fecha' }} <span aria-hidden="true">→</span> {{ hab.FechaAlquilerFin || 'Sin fecha' }}</span></div><div><i class="fas fa-user-tie" aria-hidden="true"></i><span><small>Personal de servicio</small>{{ hab.Empleado || 'Sin personal asignado' }}</span></div></div>
              <div class="project-meta" v-if="hab.EstadoHabitacion == 2"><div><i class="far fa-calendar-alt" aria-hidden="true"></i><span><small>Reserva</small>{{ formatFecha(hab.FechaReserva) }}</span></div><div v-if="hab.Vuelo">Vuelo: {{ hab.Vuelo }}</div></div>
              <div slot="footer">
                <b-btn
                  v-show="hab.EstadoHabitacion !== 1"
                  @click="liberarHab(hab)"
                  variant="success"
                  size="sm"
                  >Liberar</b-btn
                >
                <!--<b-btn :id="i + 'popover-reservar' + index" variant="warning" size="sm">Reservar</b-btn>-->
                <!--<b-popover v-if="showPopover"  :target="i + 'popover-reservar' + index" title="Reservar Habitación">
                <label>Seleccione un cliente:</label>
                <autocomplete
                    required="required"
                    :ref="'inputCliente1' + index"
                    :url="API + 'clientes'"
                    anchor="ClienteDniRuc"
                    label="writer"
                    placeholder=""
                    :on-select="setCliente" 
                    :classes="{input: 'form-control'}">
                </autocomplete>
                <label for="inputLive">Fecha Reserva:</label>
                <b-form-input v-model="fechaReserva"
                  type="date"></b-form-input>

                <label for="inputLive">Vuelo:</label>
                <b-form-input v-model="vuelo"
                  type="text"></b-form-input>
                <br />
                <b-button variant="success" size="sm" @click="reservarHab(hab)">Reservar Habitación</b-button>	
              </b-popover>-->
                <b-btn
                  v-show="hab.EstadoHabitacion !== 3"
                  :id="i + 'popover-alquilar' + index"
                  variant="danger"
                  size="sm"
                  >Empezar</b-btn
                >
                <b-popover custom-class="project-popover"
                  v-if="showPopover"
                  :target="i + 'popover-alquilar' + index"
                  title="Empezar Proyecto"
                >
                  <label>Seleccione un cliente:</label>
                  <b-input-group>
                    <autocomplete
                      required="required"
                      :ref="'inputCliente2' + index"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => (newDniRuc = val)"
                      :on-select="setCliente"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                    <b-input-group-append>
                      <!--<proveedores-tabla></proveedores-tabla>-->
                      <clientes-form :project-mode="true"
                      :idModal="'modalHab_' + i + '_' + index"
                      :newDniRuc="newDniRuc" 
                      @onAddCliente="(obj) => { setCliente(obj); pegarTextoBuscador('inputCliente2' + index, obj); }"
                      ></clientes-form>
                    </b-input-group-append>
                  </b-input-group>
                  <br />
                  <label>Descripcion:</label>
                  <textarea
                    class="form-control"
                    placeholder="Descripción"
                    cols="12"
                    rows="3"
                    style="width: 100%"
                    v-model="selectCliente.Descripcion"
                  >
                  </textarea>

                  <!--<br />
                <label>Lugar Procedencia:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.LugarProcedencia"
                    type="text"
                    placeholder="Lugar Procedencia"></b-form-input>
                </b-input-group>
                <br>
                <label>Medio de transporte:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.MedioTransporte"
                    type="text"
                    placeholder="Medio de transporte"></b-form-input>
                </b-input-group>
                <br>
                <label>Próximo destino:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.ProximoDestino"
                    type="text"
                    placeholder="Próximo destino"></b-form-input>
                </b-input-group>-->
                  <br />
                  <label>Fecha Inicio:</label>
                  <b-input-group>
                    <b-form-input
                      v-model="selectCliente.FechaAlquilerInicio"
                      type="date"
                      placeholder="Fecha Inicio"
                    ></b-form-input>
                  </b-input-group>
                  <br />
                  <label>Fecha Fin:</label>
                  <b-input-group>
                    <b-form-input
                      v-model="selectCliente.FechaAlquilerFin"
                      type="date"
                      placeholder="Fecha Fin"
                    ></b-form-input>
                  </b-input-group>
                  <br />

                  <b-button
                    variant="success"
                    size="sm"
                    @click="alquilarHab(hab)"
                    >Empezar Proyecto</b-button
                  >
                </b-popover>
                <b-button
                  v-if="hab.IdPreOrden && hab.EstadoHabitacion == 3"
                  @click="openCarrito(hab)"
                  variant="primary"
                  size="sm"
                  ><i class="fas fa-plus" aria-hidden="true"></i> Detalle</b-button
                >
              </div>
            </b-card>
          </b-col>
        </b-row></section>
      </div>
    </b-container>

    <b-form
      @submit.prevent="guardarProductos"
      @keydown.enter.prevent="() => {}"
    >
      <b-modal
        v-model="show"
        class="modalProyecto"
        id="carrito"
        size="lg"
        no-close-on-backdrop
        :title="'Cesta de compras - Proyecto ' + selectProducto.Producto"
        @hide="stopProjectDictation" ref="modalCarrito" modal-class="nst-dialog project-workspace-modal" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered=""
      >
        <!-- <div id="IdLogo">ssss</div> -->
        <div id="contentModal">
          <div class="row">
            <div id="headerCamiones" class="col-md-12">
              <div class="text-left">
                <h4>Código : {{ selectProducto.IdProducto }}</h4>
              </div>
              <div class="text-right horaFechaImpreso">
                Fecha y Hora del impreso:
                <span id="fechaHoraImp" style="font-weight: bold"></span>
              </div>
              <div class="text-left my-1" >
                Fecha del Proyecto:
                <b>
                  Inicio: {{ selectProducto.FechaAlquiler }} - Fin:
                  {{ selectProducto.FechaAlquilerFin }}</b
                >
              </div>
              <div class="text-left my-1" >
                Personal Servicio
                <b>
                  {{ selectProducto.Empleado }}
                </b>
              </div>
              <div class="text-left my-1"><i class="fas fa-user" aria-hidden="true"></i> Generado por: <b>{{ selectProducto.UsuarioCreador || "Sin registro hist?rico" }}</b></div>
              <div class="text-center" style="margin-bottom: 20px">
                <b
                  >{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b
                >
              </div>
              <div class="project-detail-phone"><div v-if="!editingProjectPhone" class="project-phone-view"><span><i class="fas fa-phone" aria-hidden="true"></i> Teléfono: <b>{{ selectProducto.Telefono || 'Sin teléfono registrado' }}</b></span><div class="project-phone-links"><a v-if="projectPhoneNumber(selectProducto.Telefono)" class="btn btn-success" :href="'https://wa.me/' + projectPhoneNumber(selectProducto.Telefono,true)" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp</a><a v-if="projectPhoneNumber(selectProducto.Telefono)" class="btn btn-outline-info" :href="'tel:' + projectPhoneNumber(selectProducto.Telefono)"><i class="fas fa-phone" aria-hidden="true"></i> Llamar</a><b-button type="button" size="sm" variant="outline-info" @click="editProjectPhone"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Editar teléfono</b-button></div></div><div v-else class="project-phone-editor"><label for="projectPhone">Teléfono del cliente</label><b-form-input id="projectPhone" v-model="projectPhoneDraft" type="tel" autocomplete="tel" :disabled="savingProjectPhone"/><b-button type="button" variant="success" :disabled="savingProjectPhone || !projectPhoneDraft.trim()" @click="saveProjectPhone">{{ savingProjectPhone ? 'Guardando…' : 'Guardar teléfono' }}</b-button><b-button type="button" variant="outline-secondary" :disabled="savingProjectPhone" @click="editingProjectPhone=false">Cancelar</b-button></div></div>
              <div class="project-description-section">
                <label>Descripción del proyecto</label>
                <b-row>
                  <b-col cols="12">
                    <b-button
                      :variant="dictatingProjectDescription ? 'danger' : 'info'"
                      :class="{'project-voice-active':dictatingProjectDescription}"
                      :aria-pressed="dictatingProjectDescription"
                      type="button"
                      class="mb-3"
                      v-if="editarDescripcion"
                      @click="startRecognition($event)"
                    >
                      <i :class="dictatingProjectDescription ? 'fas fa-stop' : 'fas fa-microphone'" aria-hidden="true"></i>
                      {{ dictatingProjectDescription ? 'Escuchando · Detener' : 'Dictado por voz' }}</b-button
                    >
                    <p v-if="dictatingProjectDescription" class="project-dictation-status" role="status">Puedes hablar. Se detiene después de 10 segundos de silencio; lo escrito se conserva.</p>
                  </b-col>
                  <b-col md="9">
                    <textarea
                      :disabled="!editarDescripcion"
                      :readonly="dictatingProjectDescription"
                      class="form-control"
                      placeholder="Descripción"
                      cols="12"
                      rows="3"
                      style="width: 100%"
                      id="txtDescripcion"
                      v-model="selectProducto.Descripcion"
                    />
                  </b-col>
                  <b-col md="3" class="project-description-actions">
                    <b-button
                      v-if="!editarDescripcion"
                      style="margin-top: 5px"
                      variant="primary"
                      size="sm"
                      @click="editarDescripcionFun()"
                      ><i class="fas fa-pencil-alt fa-sm"></i> Editar
                      Descripcion</b-button
                    >
                    <b-button
                      v-else
                      style="margin-top: 5px"
                      variant="success"
                      size="sm"
                      @click="guardarDescripcion()"
                      ><i class="fas fa-save fa-sm"></i> Guardar Descripcion
                    </b-button>
                    <br />
                    <b-button
                      v-if="!editarDescripcion"
                      @click="imprimirReciboServicio(selectProducto)"
                      style="margin-top: 5px"
                      variant="info"
                      size="sm"
                      ><i class="fas fa-print fa-sm"></i> Recibo de servicio
                    </b-button>
                  </b-col>
                  <b-col cols="12" class="project-barcode-actions mt-2">
                    <label>Etiquetas del proyecto</label>
                    <b-input-group>
                      <b-form-input
                        v-model="cantidadCodigoBarra"
                        type="number"
                        style="width: 80px"
                      ></b-form-input>
                      <b-input-group-append>
                        <b-btn
                          variant="info"
                          target="_blank"
                          :href="
                            '/views/codigoBarra.php?idProducto=' +
                            selectProducto.IdProducto +
                            '&cantidad=' +
                            cantidadCodigoBarra
                          "
                          >Código de barra</b-btn
                        >
                      </b-input-group-append>
                    </b-input-group>
                  </b-col>
                </b-row>
              </div>
              <div class="text-left" v-if="proformaTotal">
                <b style="padding: 4px; background: #ebf0ff">
                  <b>PROFORMA:</b> {{ codProforma }} <br />
                  S/ {{ proformaTotal }}
                </b>
              </div>
              <br />

              <b-row>
                <b-col md="8" id="productosProduccion">
                  <pre-orden-form-det
                    v-if="show"
                    :id-preorden="selectProducto.IdPreOrden"
                    :id-producto="selectProducto.IdProducto"
                    :esProyecto="true"
                    ref="preOrdenProductos"
                    @onUpdateProductos="calcularManoObra"
                  ></pre-orden-form-det>

                  <b-row>
                    <b-col md="4">
                      <table
                        class="table b-table border table-footer table-sm text-center"
                      >
                        <tbody>
                          <tr>
                            <td>Mano de obra bruta</td>
                          </tr>
                          <tr>
                            <td>
                              <h3>{{ totalManoObraBruta | redondear }}</h3>
                            </td>
                          </tr>
                          <tr>
                            <td>Mano de obra - Materiales</td>
                          </tr>
                          <tr>
                            <td>
                              <h3
                                :class="{
                                  'text-danger': totalManoObraMenoMat <= 0,
                                }"
                              >
                                {{ totalManoObraMenoMat | redondear }}
                              </h3>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </b-col>
                    <b-col md="4">
                      <table
                        class="table b-table border table-footer table-sm text-center"
                      >
                        <tbody>
                          <tr>
                            <td>Total de Gastos</td>
                          </tr>
                          <tr>
                            <td>
                              <h3>{{ totalGastos | redondear }}</h3>
                            </td>
                          </tr>
                          <tr>
                            <td>Mano de obra neta + Operativos</td>
                          </tr>
                          <tr>
                            <td>
                              <h3
                                :class="{
                                  'text-danger': totalManoObraNeta <= 0,
                                }"
                              >
                                {{ totalManoObraNeta | redondear }}
                              </h3>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </b-col>
                    <b-col md="4">
                      <table
                        class="table b-table border table-footer table-sm text-center"
                      >
                        <tbody>
                          <tr>
                            <td>Total Productos</td>
                          </tr>
                          <tr>
                            <td>
                              <h3>{{ totalGeneral | redondear }}</h3>
                            </td>
                          </tr>
                          <tr>
                            <td>Total Adelantos</td>
                          </tr>
                          <tr>
                            <td>
                              <h3>{{ totalAdelantos | redondear }}</h3>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </b-col>
                  </b-row>
                </b-col>
                <b-col md="4">
                  <label><b>Gastos</b></label>
                  <input
                    v-model="selectProducto.Gasto"
                    size="sm"
                    type="number"
                    class="form-control"
                    step="0.01"
                    placeholder="Gasto S/."
                  />
                  <b-input-group>
                    <b-form-input
                      v-model="selectProducto.GastoDesc"
                      size="sm"
                      type="text"
                      placeholder="Descripción"
                    ></b-form-input>
                    <b-button @click="insertarGasto" variant="primary" size="sm"
                      >+</b-button
                    >
                    <b-form-text id="inputLiveHelp"
                      >Los gastos se reflejará en Caja y Banco.</b-form-text
                    >
                  </b-input-group>
                  <br />
                  <table class="table b-table table-sm">
                    <thead>
                      <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Cantidad</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(ade, index) in gastos" :key="index">
                        <td>{{ ade.FechaDoc }}</td>
                        <td>{{ ade.Concepto.split(" - ").pop() }}</td>
                        <td>
                          {{ ade.Tipo ? "-" + ade.Importe : ade.Importe }}
                        </td>
                         <td>
                          <b-button variant="danger" size="sm" @click="eliminarGasto(ade)">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                          </b-button>
                         </td>
                      </tr>
                      <tr class="bg-info text-white">
                        <td colspan="3">TOTAL</td>
                        <td>{{ totalGastos }}</td>
                       
                      </tr>
                    </tbody>
                  </table>

                  <hr />

                  <label><b>Adelantos</b></label>
                  <input
                    v-model="selectProducto.Adelanto"
                    size="sm"
                    type="number"
                    class="form-control"
                    step="0.01"
                    placeholder="Adelanto S/."
                  />
                  <b-input-group>
                    <b-form-input
                      v-model="selectProducto.AdelantoDesc"
                      size="sm"
                      type="text"
                      placeholder="Descripción"
                    ></b-form-input>
                    <b-button
                      @click="insertarAdelanto"
                      variant="primary"
                      size="sm"
                      >+</b-button
                    >
                    <b-form-text id="inputLiveHelp"
                      >Adelanto se reflejará en Caja y Banco.</b-form-text
                    >
                  </b-input-group>

                  <br />
                  <table class="table b-table table-sm">
                    <thead>
                      <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th>Cantidad</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(ade, index) in adelantos" :key="index">
                        <td>{{ ade.FechaDoc }}</td>
                        <td>{{ ade.Concepto.split(" - ").pop() }}</td>
                        <td>
                          {{ ade.Tipo ? "-" + ade.Importe : ade.Importe }}
                        </td>
                         <td>
                          <b-button variant="danger" size="sm" @click="eliminarGasto(ade)">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                          </b-button>
                         </td>
                      </tr>
                      <tr class="bg-info text-white">
                        <td colspan="3">TOTAL</td>
                        <td>{{ totalAdelantos }}</td>
                       
                      </tr>
                    </tbody>
                  </table>
                </b-col>
              </b-row>

              <div class="text-center">
                <h4>Gran total: {{ totalSumaProductos }}</h4>
              </div>
            </div>
          </div>
        </div>

        <div slot="modal-footer" class="project-workspace-actions">
          <div class="project-secondary-actions">
            <b-btn
              class="btnAction2" 
              variant="info"
              type="submit"
              target="_blank"
              :href="
                '/views/V_VentaForm.php?idPreOrden=' +
                selectProducto.IdPreOrden +
                '&idCliente=' +
                selectProducto.IdClienteReserva
              "
            >
              Pagar Ahora
            </b-btn>
            <b-btn class="btnAction2"  variant="" @click="imprimir"> Imprimir detalle </b-btn>
            <div class="project-proforma-action">
              <proformas
                @onSelectProforma="selectProforma"
                @mounted="mountedProforma"
              ></proformas>
            </div>
          </div>
          <b-btn class="btnAction2" variant="success" type="submit"> Guardar </b-btn>
          <b-btn class="btnAction2" variant="secondary" @click="show = false"> Cerrar </b-btn>
        </div>
      </b-modal>
    </b-form>
  </div>
</template>
<script>
import { projectPrintReport } from "@/utils/projectPrintReport";
import { createDescriptionDictation } from "@/utils/descriptionDictation";
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import PreOrdenFormDet from "@/components/PreOrdenFormDet";
import ClientesForm from "@/components/ClientesForm";
import EmpleadoForm from "@/components/EmpleadoForm";
import Proformas from "@/components/ProformaLista.vue";

export default {
  mounted() {
    this.getHabitaciones();
    this.axios.get(this.API+'empresa/id/'+(sessionStorage.getItem('empresa') || 1)).then(result=>{this._reportCompany=result.data || {};}).catch(()=>{});
  },
  data() {
    return {
      editingProjectPhone:false,
      savingProjectPhone:false,
      projectPhoneDraft:'',
      dictatingProjectDescription:false,
      dictatingDescription: false,
      mdlNuevoProyecto: false,
      API: config.API,
      show: false,
      showPopover: true,
      showNuevoProyecto: true,
      habitaciones: [],
      selectCliente: {FechaAlquilerInicio:this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.$moment().format("YYYY-MM-DD")},
      selectEmpleado: {},
      selectProducto: {},
      vuelo: "",
      fechaReserva: this.$moment().format("YYYY-MM-DD"),
      pisos: [1, 2],
      adelantos: [],
      gastos: [],
      montoTotal: 0,
      IdProforma: null,
      proformaSeleccionada: false,
      proformaTotal: null,
      codProforma: null,
      profTotHab: 0,
      // proformaTotalCard: null,
      // habitacionesPiso: []

      //nuevas funciones
      totalManoObraBruta: 0,
      totalManoObraMenoMat: 0,
      totalManoObraNeta: 0,
      totalSumaProductos: 0,
      totalGastos: 0,
      totalGeneral: 0,
      totalAdelantos: 0,
      editarDescripcion: false,
      newDniRuc: "",
      newDniRucEmpleado: "",
      cantidadCodigoBarra: 1
    };
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos
    })

    /* 		totalAdelantos() {
    let sumatoria = 0
    this.adelantos.map(ad => {
      if (ad.Tipo) {
        sumatoria -= parseFloat(ad.Importe)
      } else {
        sumatoria += parseFloat(ad.Importe)
      }
    })

    return sumatoria;
  },
  totalGastos() {
    let sumatoria = 0
    this.gastos.map(ad => {
      sumatoria += parseFloat(ad.Importe)
    })

    return sumatoria;
  }, */
  },
  beforeDestroy() { if (this._descriptionDictation) this._descriptionDictation.dispose(); if(this._projectDescriptionDictation)this._projectDescriptionDictation.dispose(); },
  methods: {
    projectPhoneNumber(value,whatsapp=false) {
      const first=String(value || '').split(/[;,/]|(?:\s+-\s+)/)[0];
      const digits=first.replace(/\D/g,'');
      if(digits.length<7) return '';
      if(whatsapp) return digits.length===9 ? '51'+digits : digits;
      return first.trim().startsWith('+') ? '+'+digits : digits;
    },
    editProjectPhone() { this.projectPhoneDraft=this.selectProducto.Telefono || ''; this.editingProjectPhone=true; },
    async saveProjectPhone() {
      if(this.savingProjectPhone) return;
      const product=this.selectProducto;
      this.savingProjectPhone=true;
      try {
        const response=await this.axios.post(this.API+'proyecto/telefono',{IdProducto:product.IdProducto,Telefono:this.projectPhoneDraft});
        this.$set(product,'Telefono',response.data.Telefono);
        this.habitaciones.filter(item => Number(item.IdClienteReserva)===Number(response.data.IdCliente)).forEach(item => this.$set(item,'Telefono',response.data.Telefono));
        this.editingProjectPhone=false;
        this.$toasted.success('Teléfono guardado correctamente');
      } catch(error) { this.$toasted.error(error.response && error.response.data && error.response.data.error || 'No se pudo guardar el teléfono.'); }
      finally { this.savingProjectPhone=false; }
    },
    abrirListaProyecto(event, ref) {
      if (event.target && event.target.tagName === 'INPUT') this.listarProyectoCampo(ref);
    },
    listarProyectoCampo(ref) {
      const entry=this.$refs[ref];
      const field=Array.isArray(entry) ? entry[0] : entry;
      if (!field || field.showList) return;
      ['cboClientePrincipal','cboEmpleado'].filter(name => name !== ref).forEach(name => {
        const other=this.$refs[name];
        const component=Array.isArray(other) ? other[0] : other;
        if (component) component.showList=false;
      });
      field.handleDoubleClick();
    },
    toggleDescriptionDictation() {
      if (this.dictatingDescription) { this._descriptionDictation.stop(); return; }
      const Recognition=window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!Recognition) { this.$toasted.error('El dictado no está disponible en este navegador. Usa Chrome o Edge.'); return; }
      if (this._descriptionDictation) this._descriptionDictation.dispose();
      this._descriptionDictation=createDescriptionDictation({Recognition,initialText:this.selectCliente.Descripcion || '',
        onText:text => { this.$set(this.selectCliente,'Descripcion',text); },
        onState:active => { this.dictatingDescription=active; },
        onError:error => { this.$toasted.error(error === 'not-allowed' ? 'Permite el acceso al micrófono para dictar.' : 'No se pudo continuar el dictado. Tu descripción se conserva.'); }
      });
      this._descriptionDictation.start();
    },
    pegarTextoBuscador(nombreRef, clienteObj) {
      // 1. Armamos el texto tal como quieres que se vea
      let textoMostrar = clienteObj.DniRuc + ' - ' + clienteObj.Cliente;

      // 2. Esperamos a que la vista se actualice
      this.$nextTick(() => {
      // Obtenemos el componente buscador correcto
      let componente = Array.isArray(this.$refs[nombreRef]) 
              ? this.$refs[nombreRef][0] 
              : this.$refs[nombreRef];

      if (componente && componente.$el) {
        // Buscamos la etiqueta <input> HTML nativa y le forzamos el texto
        let inputDOM = componente.$el.querySelector('input');
        if (inputDOM) {
        inputDOM.value = textoMostrar;
        }
        
        // (Opcional) Le avisamos al componente interno por si usa esta propiedad
        if (componente.type !== undefined) componente.type = textoMostrar;
      }
      });
    },
    abrirMdlNuevoProyecto() {
      this.mdlNuevoProyecto = true;
    },
    calcularGeneral(productos) {
      this.$nextTick(() => {
        // console.log("ssss", this.productos);
        let total = productos.reduce((sum, producto) => {
          console.log(producto);
          return sum + parseFloat(producto.Precio * producto.Cantidad);
        }, 0);
        // return parseFloat(total).toFixed(2);

        this.totalGeneral = total;
      });
    },
    stopProjectDictation() { if(this._projectDescriptionDictation)this._projectDescriptionDictation.stop(); },
    startRecognition(event) {
      if(event)event.stopPropagation();
      if(this.dictatingProjectDescription){this.stopProjectDictation();return;}
      const Recognition=window.SpeechRecognition || window.webkitSpeechRecognition;
      if(!Recognition){this.$toasted.error('El dictado no está disponible en este navegador. Usa Chrome o Edge.');return;}
      if(this._descriptionDictation)this._descriptionDictation.stop();
      if(this._projectDescriptionDictation)this._projectDescriptionDictation.dispose();
      const project=this.selectProducto;
      this._projectDescriptionDictation=createDescriptionDictation({Recognition,initialText:project.Descripcion || '',
        onText:text=>this.$set(project,'Descripcion',text),
        onState:active=>{this.dictatingProjectDescription=active;},
        onError:error=>this.$toasted.error(error==='not-allowed'?'Permite el acceso al micrófono para dictar.':'No se pudo continuar el dictado. El texto escrito se conserva.')
      });
      this._projectDescriptionDictation.start();
    },
    handleError(error) {
      let errorMessage = "";
      if (error === "no-speech") {
        errorMessage =
          "No se detectó ningún discurso. Por favor, inténtalo de nuevo.";
      } else if (error === "audio-capture") {
        errorMessage =
          "No se pudo acceder al micrófono. Asegúrate de que esté correctamente conectado y de que el navegador tenga permisos para usarlo.";
      } else if (error === "not-allowed") {
        errorMessage =
          "Permiso para usar el micrófono denegado. Por favor, habilita el acceso en la configuración de tu navegador.";
      } else {
        errorMessage =
          "Ocurrió un error desconocido. Por favor, inténtalo de nuevo.";
      }
      alert(errorMessage);
    },
    mountedProforma(modal) {
      document.body.appendChild(modal);
    },
    selectProforma(proforma) {
      this.show = true;
      const projectId=this.selectProducto.IdPreOrden;
      this.$nextTick(() => {
        if(this.selectProducto.IdPreOrden !== projectId) return;
        proforma.productos.map(p => {
          this.$refs.preOrdenProductos.productosACargar.push(p);
        });

        this.IdProforma = proforma.IdProforma;
        this.proformaSeleccionada = true;
      });
    },
    cargarAdelantos() {
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: this.selectProducto.IdPreOrden,
            TipoCajaBanco: 0
          }
        })
        .then(res => {
          this.adelantos = res.data;
          this.calculoGastosAdelantos();
        });
    },
    cargarGastos() {
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: this.selectProducto.IdPreOrden,
            TipoCajaBanco: 1
          }
        })
        .then(res => {
          this.gastos = res.data;
          this.calculoGastosAdelantos();
        });
    },
    async insertarAdelanto() {
      let usuario = sessionStorage.getItem("User");

      if (this.selectProducto.Adelanto <= 0) {
        return;
      }

      const confirmation = await this.$swal({title:'Registrar adelanto',text:'Se registrará un adelanto de S/ ' + Number(this.selectProducto.Adelanto).toFixed(2) + ' en este proyecto.',icon:'question',showCancelButton:true,confirmButtonText:'Sí, registrar',cancelButtonText:'Cancelar',confirmButtonColor:'#079e87',reverseButtons:true,customClass:{popup:'project-confirm-dialog'}});
      const r = confirmation.isConfirmed;

      if (r) {
        this.axios
          .post(this.API + "preorden/cajaybanco", {
            producto: this.selectProducto,
            concepto:
              "Adelanto Proyecto " +
              this.selectProducto.Producto +
              " - " +
              this.selectProducto.AdelantoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            Adelanto: this.selectProducto.Adelanto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            this.cargarAdelantos();
            this.getHabitaciones();
            this.selectProducto.Adelanto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.AdelantoDesc = "";
          });
      }
    },

    async insertarGasto() {
      let usuario = sessionStorage.getItem("User");

      if (this.selectProducto.Gasto <= 0) {
        return;
      }

      const confirmation = await this.$swal({title:'Registrar gasto',text:'Se registrará un gasto de S/ ' + Number(this.selectProducto.Gasto).toFixed(2) + ' en este proyecto.',icon:'question',showCancelButton:true,confirmButtonText:'Sí, registrar',cancelButtonText:'Cancelar',confirmButtonColor:'#079e87',reverseButtons:true,customClass:{popup:'project-confirm-dialog'}});
      const r = confirmation.isConfirmed;

      if (r) {
        this.axios
          .post(this.API + "preorden/cajaybanco/gasto", {
            producto: this.selectProducto,
            concepto:
              "Gasto Proyecto " +
              this.selectProducto.Producto +
              " - " +
              this.selectProducto.GastoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            Gasto: this.selectProducto.Gasto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            this.cargarGastos();
            this.selectProducto.Gasto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.GastoDesc = "";
          });
      }
    },

    getMostrarPiso(piso) {
      let id = this.$route.query.id ? this.$route.query.id : 1;
      return piso == id;
    },
    getHabitacionesPiso(piso) {
      return this.habitaciones.filter(hab => {
        return hab.Piso === piso;
      });
    },
    guardarProductos() {
      this.axios
        .post(this.API + "preorden/detalle", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          IdHabitacion: this.selectProducto.IdProducto,
          productos: this.$refs.preOrdenProductos.productos,
          IdProforma: this.proformaSeleccionada ? this.IdProforma : null
        })
        .then(res => {
          this.$toasted.success("Guardado correctamente");
          this.getHabitaciones();
          this.show = false;
        });
    },
    openCarrito(hab) {
      this.stopProjectDictation();
      this.editingProjectPhone=false;
      // console.log(hab);
      this.proformaTotal = null;
      this.selectProducto = hab;
      this.IdProforma = hab.IdProforma || null;
      this.proformaSeleccionada = false;
      this.$refs.modalCarrito.show();
      this.cargarAdelantos();
      this.cargarGastos();
      this.cargarProformaTotal(this.selectProducto.IdProforma);
    },
    cargarProformaTotal(idProforma) {
      if (idProforma) {
        this.axios
          .get(this.API + "preorden/proforma", {
            params: { idProforma: idProforma }
          })
          .then(res => {
            this.proformaTotal = res.data.Total;
            this.codProforma = res.data.IdProforma;
            this.codProforma = res.data.Anio + " - " + res.data.Numero;
          });
      }
    },
    getHabitaciones() {
      let id = this.$route.query.id ? this.$route.query.id : 1;
      this.axios
        .get(
          this.API +
            "habitaciones?sortBy=Gen_Producto.IdProducto&sortDesc=DESC&pisoAlm=" +
            id
        )
        .then(res => {
          this.habitaciones = res.data;
          /* this.pisos.forEach(piso => {
        this.habitacionesPiso[piso] = this.getHabitacionesPiso(piso)
      }) */
        });
    },
    getClaseTexto(hab) {
      if (hab.FechaAlquilerFin < this.$moment().format("YYYY-MM-DD")) {
        return "text-center text-light";
      }
      return "text-center text-dark";
    },
    getVariant(hab) {
      if (hab.EstadoHabitacion == 3) {
        if (hab.FechaAlquilerFin < this.$moment().format("YYYY-MM-DD"))
          return "danger";
        return "warning";
      }
      if (hab.EstadoHabitacion == 2) return "warning";
      //if (hab.EstadoHabitacion == 3) return 'danger'
      if (hab.EstadoHabitacion == 1 && hab.EstaSucio == 1) return "secondary";
      return "success";
    },
    reservarHab(hab) {
      if (!this.selectCliente.Cliente) {
        this.$toasted.error("Seleccione un cliente.");
      }
      if (!this.selectEmpleado.Empleado) {
        this.$toasted.error("Seleccione un empleado.");
      }

      this.axios
        .post(this.API + "habitaciones/reservar", {
          IdProducto: hab.IdProducto,
          IdCliente: this.selectCliente.IdCliente,
          IdEmpleado: this.selectEmpleado.IdEmpleado,
          FechaReserva: this.fechaReserva,
          Vuelo: this.vuelo,
          pisoAlm: this.$route.query.id
        })
        .then(res => {
          this.getHabitaciones();
          this.limpiarPopover();
          this.limpiarDespues();
          this.$toasted.success(
            "La Habitación: " + hab.Producto + " ha sido reservada"
          );
          this.showPopover = false;
          this.$nextTick(() => {
            this.showPopover = true;
          });
        });
    },
    liberarHab(hab) {
      let r = confirm(
        "¿Está seguro que desea liberar el proyecto? Asegurese de haber realizado la venta"
      );

      if (r) {
        this.axios
          .post(this.API + "habitaciones/liberar", {
            IdProducto: hab.IdProducto
          })
          .then(res => {
            this.getHabitaciones();
            this.limpiarPopover();
            this.$toasted.success(
              "El proyecto: " + hab.Producto + " ha sido liberado"
            );
            this.showPopover = false;
            this.$nextTick(() => {
              this.showPopover = true;
            });
          });
      }
    },
    async nuevoProyecto() {
      if (this._descriptionDictation) this._descriptionDictation.stop();
      console.log("selectCliente", this.selectCliente);
      if (!this.selectCliente.IdCliente) {
        this.$toasted.error("Seleccione un cliente.");
        return false;
      }
      let data = {
        // IdProducto: hab.IdProducto,
        IdCliente: this.selectCliente.IdCliente,
        IdEmpleado: this.selectEmpleado.IdEmpleado,
        FechaReserva: this.fechaReserva,
        Vuelo: this.vuelo,
        Descripcion: this.selectCliente.Descripcion,
        FechaAlquilerInicio: this.selectCliente.FechaAlquilerInicio,
        FechaAlquilerFin: this.selectCliente.FechaAlquilerFin,
        pisoAlm: this.$route.query.id
      };
      await this.axios
        .post(this.API + "producto/registro_proyecto", data)
        .then(res => {
          this.getHabitaciones();
          this.limpiarPopover();
          this.limpiarDespues();
          this.$toasted.success("La Habitación: ha sido reservada");
          this.showPopover = false;
          this.selectCliente = {FechaAlquilerInicio:this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.$moment().format("YYYY-MM-DD")};
          this.selectEmpleado = {};
          this.mdlNuevoProyecto = false;
        });
      window.location.reload();
      // this.getHabitaciones();
    },
    alquilarHab(hab) {
      if (!this.selectCliente.Cliente) {
        this.$toasted.error("Seleccione un cliente.");
      }

      let r = confirm("¿Está seguro que desea empezar el proyecto?");

      if (r) {
        this.axios
          .post(this.API + "habitaciones/alquilar", {
            IdProducto: hab.IdProducto,
            IdCliente: this.selectCliente.IdCliente,
            IdEmpleado: this.selectEmpleado.IdEmpleado,
            cliente: this.selectCliente,
            empleado: this.selectEmpleado,
            usuario: sessionStorage.getItem("User")
          })
          .then(res => {
            this.getHabitaciones();
            this.limpiarPopover();
            this.limpiarDespues();
            this.$toasted.success(
              "El proyecto: " + hab.Producto + " ha sido iniciado."
            );
            this.showPopover = false;
            this.$nextTick(() => {
              this.showPopover = true;
            });
          });
      }
    },
    setCliente(obj, index) {
      if (index >= 0) {
        this.$refs["inputCliente2" + index][0].setValue(obj.Cliente);
        this.newDniRuc = "";
      }
      this.selectCliente = Object.assign({}, obj, {FechaAlquilerInicio:this.selectCliente.FechaAlquilerInicio || this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.selectCliente.FechaAlquilerFin || this.$moment().format("YYYY-MM-DD")});
    },
    setEmpleado(obj, index) {
      let ref = `cboEmpleado`;
      this.$refs[ref][0].setValue(obj.Empleado);
      this.selectEmpleado = obj;
    },
    /*focusAutocomplete (ref) {
    this.$refs[ref][0].handleDoubleClick()
  },*/
    limpiarPopover() {
      //this.cliente = {}
      //this.$refs.inputCliente.setValue('')
    },
    limpiarDespues() {
      this.selectCliente = {FechaAlquilerInicio:this.$moment().format("YYYY-MM-DD"),FechaAlquilerFin:this.$moment().format("YYYY-MM-DD")};
      this.selectEmpleado = {};
      this.selectProducto = {};
    },
    formatFecha(fecha) {
      return this.$moment(fecha, "YYYY-MM-DD hh:mm:ss").format("YYYY-MM-DD");
    },
    imprimir() {
      const popup=window.open('','_blank','width=1000,height=760');
      if(!popup){this.$toasted.error('Permite las ventanas emergentes para imprimir.');return;}
      popup.document.write('<p>Preparando reporte del proyecto…</p>');
      try {
        const company=this._reportCompany || {};
        const html=projectPrintReport({project:this.selectProducto,products:this.$refs.preOrdenProductos.productos,advances:this.adelantos,expenses:this.gastos,labor:this.totalManoObraBruta,netLabor:this.totalManoObraNeta,company,user:sessionStorage.getItem('User'),date:new Date().toLocaleString('es-PE')});
        popup.document.open();popup.document.write(html);popup.document.close();
        const print=()=>{if(!popup.closed){popup.focus();popup.print();}};
        const logo=popup.document.querySelector('img');
        let printed=false;const ready=()=>{if(printed)return;printed=true;print();};
        if(logo && !logo.complete){logo.onload=ready;logo.onerror=ready;setTimeout(ready,700);}else setTimeout(ready,50);
      }catch(error){popup.close();this.$toasted.error('No se pudo preparar el reporte.');}
    },
    sumProductos() {
      let total = 0;
      if (this.$refs.preOrdenProductos) {
        total = this.$refs.preOrdenProductos.sumProductos - this.totalAdelantos;
      }
      return Math.abs(total).toFixed(2);
    },
    eliminarGasto(ade) {
      console.log(ade);
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: `Desea Eliminar : ${ade.Concepto}`,
        confirmButtonText: "Si, Eliminar",
        cancelButtonText: "Cancelar",
        showCancelButton: true,
        showCloseButton: true
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios.put(this.API + "cajabanco/eliminar", ade);
          this.cargarGastos();
          this.cargarAdelantos();
        }
      });
    },
    /* 		totManoObraBruta() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraBruta
    }
    return total?total.toFixed(2):0
  }, */
    /* 		totManoObraMenoMat() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraMenosMat
    }
    return total?total.toFixed(2):0
  }, */
    /* 		totManoObraNeta() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraMenosMat - this.totalGastos
    }
    return total?total.toFixed(2):0
  }, */
    calcularManoObra(e) {
      console.log(e);
      let pManoObra = e.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.Precio : 0;
      let cantidad = pManoObra
        ? pManoObra.Cantidad == undefined
          ? 0
          : pManoObra.Cantidad
        : 0;
      this.totalManoObraBruta = Number(precio * cantidad);
      this.totalManoObraMenoMat = Number(precio * cantidad);
      e.map(preorden => {
        if (preorden.EsManoDeObra == 1) {
          this.totalManoObraMenoMat =
            this.totalManoObraMenoMat - preorden.Precio * preorden.Cantidad;
        }
      });
      this.calculoGastosAdelantos();
      this.calcularGeneral(e);
    },
    calculoGastosAdelantos() {
      let sumatoriaGas = 0;
      this.gastos.map(ad => {
        sumatoriaGas += parseFloat(ad.Importe);
      });
      this.totalGastos = sumatoriaGas;
      this.totalManoObraNeta = this.totalManoObraMenoMat - this.totalGastos;

      let sumatoriaAde = 0;
      this.adelantos.map(ad => {
        if (ad.Tipo) {
          sumatoriaAde -= parseFloat(ad.Importe);
        } else {
          sumatoriaAde += parseFloat(ad.Importe);
        }
      });
      this.totalAdelantos = sumatoriaAde;

      if (this.$refs.preOrdenProductos) {
        this.totalSumaProductos = Math.abs(
          this.$refs.preOrdenProductos.sumProductos - this.totalAdelantos
        ).toFixed(2);
        if (this.$refs.preOrdenProductos.productos) {
          this.$refs.preOrdenProductos.productos.map(pro => {
            if (pro.CodigoBarra == "GASTOS") {
              this.totalManoObraNeta += pro.Cantidad * pro.Precio || 0;
            }
          });
        }
      }
    },
    editarDescripcionFun() {
      this.editarDescripcion = !this.editarDescripcion;
    },
    guardarDescripcion() {
      this.stopProjectDictation();
      this.axios
        .post(this.API + "proyecto/descripcion", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          Descripcion: this.selectProducto.Descripcion
        })
        .then(res => {
          this.editarDescripcion = false;
          this.$toasted.info("Descripcion guardada correctamente");
        })
        .catch(error => {
          this.$toasted.show("No se guardo la descripcion, hubo un error");
        });
    },
    imprimirReciboServicio(selectProducto) {
      console.log(selectProducto);
      window.open(
        "/imprimir/imprimirReciboServicio.php?IdProducto=" +
          selectProducto.IdProducto,
        "_blank"
      );
    }
  },
  components: {
    PreOrdenFormDet,
    ClientesForm,
    Proformas,
    EmpleadoForm
  }
};
</script>
<style>
.project-popover {
  max-width: 500px!important;
  width: min(500px, calc(100vw - 24px)) !important;
}

.project-popover .popover-body {
  width: 100% !important;
}

.modalProyecto .modal-dialog {
  width: 90% !important; /* Cambia el 80% al ancho que necesitas */
  max-width: 90% !important;
}
@media print {
  body .printable,
  body .modal {
    display: block;
  }
  #pisos {
    visibility: hidden;
  }
  .modal {
    /**Remove scrollbar for printing.**/
    overflow: visible !important;
    position: absolute;
  }
  .modal-dialog {
    visibility: visible !important;
    /**Remove scrollbar for printing.**/
    overflow: visible !important;
  }
  .modal-footer {
    visibility: hidden;
  }
  .horaFechaImpreso {
    display: block;
  }
}
</style>
<style scoped > 
h5 {
  font-size: 1.2rem;
}
h6 {
  font-size: 1rem;
}
.card-footer {
  padding-left: 0;
  padding-right: 0;
}
.habitacion {
  margin-bottom: 10px;
}
.detalle {
  list-style: none;
  padding: 0;
  margin-bottom: 0px;
  text-align: left;
  font-size: 1rem;
}
.horaFechaImpreso {
  display: none;
}
</style>
