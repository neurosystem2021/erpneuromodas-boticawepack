<template>
<b-container class="nst-screen nst-screen--usuario">
  <section class="nst-panel nst-panel--filters"><b-row>
      <b-col cols="12">
          <b-tabs content-class="mt-3 mx-3" fill card pills>
            <b-tab title="MODULOS" class="py-0"><template slot="title"><i class="fas fa-th-large nst-tab-icon" aria-hidden="true"></i><span>MODULOS</span></template>
                <b-row>
                  <b-col cols="12" class="mb-3 mt-1" style="text-align: end;">
                     <b-button variant="success" @click="abrirNuevoModulo()">
                      <i class="fa fa-user-plus" aria-hidden="true"></i> Nuevo Módulo
                    </b-button>
                  </b-col>
                  <b-table 
                      :items="lstModulos" 
                      class="table-sm"
                      :per-page="perPage"
                      :current-page="currentPage"  
                      :fields="fields" responsive=""
                     show-empty empty-text="No hay registros para los filtros seleccionados." empty-filtered-text="No se encontraron coincidencias." ></b-table>
                  <b-pagination 
                    :total-rows="lstModulos.length" 
                    :per-page="perPage" 
                    v-model="currentPage" 
                    class="my-0" 
                  />
                </b-row>
                <!-- {{ lstModulos }} -->
                
              </b-tab>
              <b-tab title="PERFILES" class="py-0"><template slot="title"><i class="fas fa-id-badge nst-tab-icon" aria-hidden="true"></i><span>PERFILES</span></template>
                  <b-row>
                      <b-col cols="12" class="mb-3 mt-1" style="text-align: end;">
                        <b-button variant="success" @click="abrirNuevoPerfil()"><i class="fa fa-user-plus" aria-hidden="true"></i>  Nuevo Perfil</b-button>
                      </b-col>
                      <b-col cols="12">
                          <div class="nst-table-scroll" tabindex="0" role="region" aria-label="Tabla de resultados"><table class="table table-sm">
                              <thead>
                                  <tr>
                                      <th >Perfil</th>
                                      <!-- <th >Nombre Usuario</th> -->
                                      <!-- <th >Usuario</th> -->
                                      <th >Activo</th>
                                      <th ></th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr v-for="(perfil,index) in lstPerfil" :key="index">
                                      <td>{{perfil.UsuarioPerfil}}</td>
                                      <!-- <th>{{perfil.NombreUsuario}}</th> -->
                                      <!-- <th>{{perfil.Usuario}}</th> -->
                                      <td>
                                          <div class="badge bg-success px-3 py-3" style="color:#ffffff; font-weight: bold;"  v-if="perfil.Anulado==0" role="alert">
                                              SI
                                          </div>
                                          <div class="badge bg-danger px-3 py-3"  style="color:#ffffff; font-weight: bold;" role="alert" v-else>
                                              NO
                                          </div>
                                      </td>
                                      <td>
                                          <b-button variant="warning" @click="abrirEditarPerfil(perfil)">
                                              <i class="fa fa-edit" aria-hidden="true"></i>
                                          </b-button>
                                          <b-button variant="danger" v-if="perfil.Anulado==0" @click="eliminarPerfil(perfil)">
                                              <i class="fa fa-trash" aria-hidden="true"></i>
                                          </b-button>
                                      </td>
                                  </tr>
                              </tbody>
                          </table></div>
                      </b-col>
                  </b-row>
              </b-tab>
              <b-tab title="USUARIOS" active class="py-0"><template slot="title"><i class="fas fa-users nst-tab-icon" aria-hidden="true"></i><span>USUARIOS</span></template>
                  <section class="usuario-panel mt-2">
                    <div class="usuario-toolbar">
                      <div>
                        <span class="usuario-eyebrow">CONTROL DE ACCESOS</span>
                        <h3>Administración de usuarios</h3>
                        <p>Los usuarios eliminados dejan de aparecer en accesos y listas operativas.</p>
                      </div>
                      <b-button @click="abrirNuevoUsuario()" class="usuario-nuevo">
                        <i class="fa fa-user-plus" aria-hidden="true"></i> Nuevo usuario
                      </b-button>
                    </div>
                    <div class="usuario-filtros">
                      <div class="usuario-segmentado" role="group" aria-label="Estado de usuarios">
                        <button type="button" :class="{ activo: vistaUsuarios === 'activos' }" @click="vistaUsuarios = 'activos'">
                          <i class="fa fa-users"></i> Activos <span>{{ usuariosActivos.length }}</span>
                        </button>
                        <button type="button" :class="{ activo: vistaUsuarios === 'eliminados' }" @click="vistaUsuarios = 'eliminados'">
                          <i class="fa fa-trash-o"></i> Eliminados <span>{{ usuariosEliminados.length }}</span>
                        </button>
                      </div>
                      <div class="usuario-buscador">
                        <i class="fa fa-search"></i>
                        <input v-model.trim="filtroUsuarios" type="search" placeholder="Buscar nombre, usuario o perfil">
                      </div>
                    </div>
                    <div class="usuario-tabla-wrap">
                          <div class="nst-table-scroll" tabindex="0" role="region" aria-label="Tabla de resultados"><table class="table table-sm usuario-tabla">
                              <thead>
                                  <tr>
                                      <th >Perfil</th>
                                      <th >Nombre Usuario</th>
                                      <th >Usuario</th>
                                      <th>Estado</th>
                                      <th class="text-right">Acciones</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <tr v-for="usuario in usuariosFiltrados" :key="usuario.Usuario">
                                      <td>{{usuario.UsuarioPerfil}}</td>
                                      <td>{{usuario.NombreUsuario}}</td>
                                      <td>{{usuario.Usuario}}</td>
                                      <td>
                                          <span class="usuario-estado activo" v-if="Number(usuario.Anulado || 0) === 0"><i></i> Activo</span>
                                          <span class="usuario-estado eliminado" v-else><i></i> Eliminado</span>
                                      </td>
                                      <td class="text-right usuario-acciones">
                                          <b-button class="btn-editar" v-if="Number(usuario.Anulado || 0) === 0" @click="abrirEditarUsuario(usuario)">
                                              <i class="fa fa-pencil" aria-hidden="true"></i><span>Editar</span>
                                          </b-button>
                                          <b-button class="btn-eliminar" v-if="Number(usuario.Anulado || 0) === 0" @click="eliminarUsuario(usuario)">
                                              <i class="fa fa-trash" aria-hidden="true"></i><span>Eliminar</span>
                                          </b-button>
                                          <b-button class="btn-reactivar" v-else @click="reactivarUsuario(usuario)">
                                              <i class="fa fa-undo" aria-hidden="true"></i><span>Reactivar</span>
                                          </b-button>
                                      </td>
                                  </tr>
                                  <tr v-if="usuariosFiltrados.length === 0">
                                    <td colspan="5" class="usuario-vacio">
                                      <i class="fa fa-user-circle-o"></i>
                                      <strong>No hay usuarios {{ vistaUsuarios === 'activos' ? 'activos' : 'eliminados' }}</strong>
                                      <span>Prueba con otro término de búsqueda.</span>
                                    </td>
                                  </tr>
                              </tbody>
                          </table></div>
                    </div>
                  </section>
              </b-tab>
            
          </b-tabs>
      </b-col>
  </b-row></section>
  
  <b-modal v-model="editarUsuarioFlag" size="lg" :title="`Editar Usuario: ${usuario.Usuario}`" hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      Nombre de Usuario
      <input type="text" v-model="usuario.NombreUsuario" class="form-control">
      Perfil
      <b-select
       :options="lstPerfil"
        v-model="usuario.IdUsuarioPerfil" 
        class="form-control" 
        value-field="IdUsuarioPerfil" 
        text-field="UsuarioPerfil">
      </b-select>
      Almacén
      <b-select
       :options="lstAlmacen"
        v-model="usuario.IdAlmacen" 
        class="form-control" 
        value-field="IdAlmacen" 
        text-field="Almacen">
      </b-select>
     
      Punto de Venta
          <b-select
          :options="lstPuntoVenta"
          v-model="usuario.IdDocVentaPuntoVenta" 
          class="form-control" 
          value-field="IdDocVentaPuntoVenta" 
          text-field="PuntoVenta"
          required>
          </b-select>
      <b-form-checkbox class="mb-3 mt-1 d-block w-100" :value="1" :unchecked-value="0" v-model="usuario.Anulado"> Anulado</b-form-checkbox>
      <b-form-checkbox class="mb-3 mt-1 d-block w-100"  v-model="usuario.actualizarClave"> Actualizar Clave</b-form-checkbox>
      <b-input-group v-if="usuario.actualizarClave">
          <b-input-group-prepend>
              <b-input-group-text @click="verclave=!verclave">
                  <i :class="!verclave ? 'fa fa-eye' : 'fa fa-eye-slash'"></i> <!-- Ícono de Bootstrap Icons -->
              </b-input-group-text>
          </b-input-group-prepend>
          <input :type="verclave ? 'text ': 'password'" v-model="usuario.clave" class="form-control" v-if="usuario.actualizarClave" placeholder="Nueva Clave">
      </b-input-group>
      
      <b-button class="mb-3 mt-1" variant="success" @click="actualizarUsuario()"> Actualizar </b-button>
      <b-button class="mb-3 mt-1" variant="danger" @click="editarUsuarioFlag=!editarUsuarioFlag"> Cancelar </b-button>
  </b-modal>

  <b-modal v-model="nuevoUsuarioFlag" size="lg" title="Nuevo Usuario" hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-form  @submit="registrarUsuario($event)"> 
          Usuario
          <input type="text" v-model="usuario.Usuario" class="form-control" required>
          Nombre de Usuario
          <input type="text" v-model="usuario.NombreUsuario" class="form-control" required>
          Perfil
          <b-select
          :options="lstPerfil"
          v-model="usuario.IdUsuarioPerfil" 
          class="form-control" 
          value-field="IdUsuarioPerfil" 
          text-field="UsuarioPerfil"
          required>
          </b-select>
          Almacén
          <b-select
          :options="lstAlmacen"
          v-model="usuario.IdAlmacen" 
          class="form-control" 
          value-field="IdAlmacen" 
          text-field="Almacen"
          required>
          </b-select>
          Punto de Venta
          <b-select
          :options="lstPuntoVenta"
          v-model="usuario.IdDocVentaPuntoVenta" 
          class="form-control" 
          value-field="IdDocVentaPuntoVenta" 
          text-field="PuntoVenta"
          required>
          </b-select>
         
         
          <b-input-group class="mb-3 mt-1">
              <b-input-group-prepend>
                  <b-input-group-text @click="verclave=!verclave">
                      <i :class="!verclave ? 'fa fa-eye' : 'fa fa-eye-slash'"></i> <!-- Ícono de Bootstrap Icons -->
                  </b-input-group-text>
              </b-input-group-prepend>
              <input :type="verclave ? 'text ': 'password'" v-model="usuario.clave" class="form-control" placeholder="Clave" required>
          </b-input-group>
          <p style="color: red;">{{ errorUsuario }}</p>
          <b-button type="submit" class="mb-3 mt-1" variant="success"> Guardar </b-button>
          <b-button class="mb-3 mt-1" variant="danger" @click="nuevoUsuarioFlag=!nuevoUsuarioFlag"> Cancelar </b-button>
      </b-form>
  </b-modal>

  <b-modal v-model="editarUsuarioPerfilFlag" no-close-on-esc  no-close-on-backdrop size="lg" title="Editar Perfil" hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
    Perfil
    <input type="text" class="form-control" v-model="perfil.UsuarioPerfil">
    <p style="text-align: center; color: red;"> {{ errorPerfil }}</p>
    <b-form-checkbox class="mb-3 mt-1 d-block w-100" :value="1" :unchecked-value="0" v-model="perfil .Anulado"> Anulado</b-form-checkbox>
    Module
    <b-input-group class="mb-3 mt-1">
      
        <b-select
        :options="lstModulos"
        v-model="IdUsuarioModulo" 
        class="form-control" 
        value-field="IdUsuarioModulo" 
        text-field="UsuarioModulo"
         @change="seleccionarModulo"
        required>
        </b-select>
        <b-input-group-prepend>
          <b-input-group-text class="btn btn-acepted" @click="anadirModule()">
            AAÑADIR
          </b-input-group-text>
          <!-- <b-input-group-text class="btn btn-info" @click="abrirNuevoModulo()">
            NUEVO
          </b-input-group-text> -->
        </b-input-group-prepend>
    </b-input-group>
    <table class="table">
      <thead>
        <tr>
          <th>Módulo</th>
          <th style="text-align: center;">Lectura</th>
          <th style="text-align: center;">Escritura</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(modulo,index) in lstModulosPerfil" :key="index">
          <td>{{modulo.UsuarioModulo  }}</td>
          <td style="text-align: center;">
            <b-form-checkbox class="mx-auto" :value="1" :unchecked-value="0" v-model="modulo.Lectura"> </b-form-checkbox>
          </td>
          <td style="text-align: center;"> 
            <b-form-checkbox class="mx-auto" :value="1" :unchecked-value="0" v-model="modulo.Escritura"> </b-form-checkbox>
          </td>
        </tr>
      </tbody>
    </table>
    <div style="text-align: right;">
        <b-button variant="success" @click="actualizarUsuarioPerfil()">Guardar</b-button>
        <b-button variant="danger" @click="editarUsuarioPerfilFlag=!editarUsuarioPerfilFlag">Cancelar</b-button>
      </div>
  </b-modal>

  <b-modal v-model="nuevoUsuarioPerfilFlag" no-close-on-esc  no-close-on-backdrop size="lg" title="Nuevo Perfil" hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
    Perfil
    <input type="text" class="form-control" v-model="perfil.UsuarioPerfil">
    <p style="text-align: start; color: red;"> {{ errorPerfil }}</p>
    Module
    <b-input-group class="mb-3 mt-1">
      
        <b-select
        :options="lstModulos"
        v-model="IdUsuarioModulo" 
        class="form-control" 
        value-field="IdUsuarioModulo" 
        text-field="UsuarioModulo"
         @change="seleccionarModulo"
        required>
        </b-select>
        <b-input-group-prepend>
          <b-input-group-text class="btn btn-acepted" @click="anadirModule()">
            AÑADIR
          </b-input-group-text>
          <!-- <b-input-group-text class="btn btn-info" @click="abrirNuevoModulo()">
            NUEVO
          </b-input-group-text> -->
        </b-input-group-prepend>
    </b-input-group>
    <table class="table">
      <thead>
        <tr>
          <th>Módulo</th>
          <th style="text-align: center;">Lectura</th>
          <th style="text-align: center;">Escritura</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(modulo,index) in lstModulosPerfil" :key="index">
          <td>{{modulo.UsuarioModulo  }}</td>
          <td style="text-align: center;">
            <b-form-checkbox class="mx-auto" :value="1" :unchecked-value="0" v-model="modulo.Lectura"> </b-form-checkbox>
          </td>
          <td style="text-align: center;"> 
            <b-form-checkbox class="mx-auto" :value="1" :unchecked-value="0" v-model="modulo.Escritura"> </b-form-checkbox>
          </td>
        </tr>
      </tbody>
    </table>
    <div style="text-align: right;">
      <b-button variant="success" @click="guardarUsuarioPerfil()">Guardar</b-button>
      <b-button variant="danger" @click="nuevoUsuarioPerfilFlag=!nuevoUsuarioPerfilFlag">Cancelar</b-button>
    </div>
  </b-modal>
   
  <b-modal v-model="nuevoModuloFlag" hide-footer title="Nuevo Módulo" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
    Modulo
    <input type="text" class="form-control mb-3" v-model="modulo">
    <b-button variant="success" @click="guardarModulo()">Guardar</b-button>
    <b-button variant="danger" @click="nuevoModuloFlag=!nuevoModuloFlag">Cancelar</b-button>
    <p v-if="errorModulo" style="color: red; text-align: center;" class="my-2">{{ errorModulo }}</p>
  </b-modal>
</b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      API: config.API,
      verclave: false,
      nuevoModuloFlag: false,
      nuevoUsuarioPerfilFlag: false,
      IdModule: "",
      Module: {},
      usuario: {},
      lstUsuario: [],
      vistaUsuarios: "activos",
      filtroUsuarios: "",
      perfil: {},
      lstPerfil: [],
      lstAlmacen: [],
      lstPuntoVenta: [],
      lstModulos: [],
      lstModulosPerfil: [],
      fields: [
        { key: "IdUsuarioModulo", text: "Id" },
        { key: "UsuarioModulo", text: "Módulo" }
      ],
      items: [
        {
          text: "SEGURIDAD",
          href: "#"
        },
        {
          text: "USUARIOS",
          active: true
        }
      ],
      editarUsuarioFlag: false,
      editarUsuarioPerfilFlag: false,
      nuevoUsuarioFlag: false,
      errorUsuario: "",
      IdUsuarioModulo: "",
      modulo: "",
      errorModulo: "",
      errorPerfil: ""
    };
  },
  computed: {
    usuariosActivos() {
      return this.lstUsuario.filter(usuario => Number(usuario.Anulado || 0) === 0);
    },
    usuariosEliminados() {
      return this.lstUsuario.filter(usuario => Number(usuario.Anulado || 0) === 1);
    },
    usuariosFiltrados() {
      const origen = this.vistaUsuarios === "eliminados" ? this.usuariosEliminados : this.usuariosActivos;
      const termino = this.filtroUsuarios.toLowerCase();
      if (!termino) return origen;
      return origen.filter(usuario =>
        [usuario.NombreUsuario, usuario.Usuario, usuario.UsuarioPerfil]
          .join(" ")
          .toLowerCase()
          .includes(termino)
      );
    }
  },
  mounted() {
    this.cargarModulos();
    this.cargarListadoPerfil();
    this.cargarListadoUsuario();
    this.cargarListadoAlmacen();
    this.cargarPuntoVenta();
  },
  methods: {
    async cargarListadoUsuario() {
      await this.axios(`${this.API}lstusuarios/listado`).then(res => {
        if (res.data) {
          this.lstUsuario = res.data;
        }
      });
    },
    async cargarListadoPerfil() {
      await this.axios(`${this.API}perfil/listado`).then(res => {
        if (res.data) {
          this.lstPerfil = res.data;
        }
      });
    },
    async cargarPuntoVenta() {
      await this.axios(`${this.API}puntoventa/listado`).then(res => {
        if (res.data) {
          this.lstPuntoVenta = res.data;
        }
      });
    },
    async cargarListadoAlmacen() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmacen = res.data;
        }
      });
    },
    abrirNuevoUsuario() {
      console.log("ddd");
      this.usuario = {};
      this.nuevoUsuarioFlag = true;
    },
    abrirEditarUsuario(usr) {
      this.usuario = usr;
      this.editarUsuarioFlag = true;
    },
    async actualizarUsuario() {
      await this.axios
        .post(`${this.API}usuario/actualizar`, this.usuario)
        .then(res => {
          this.$swal({
            icon: "success",
            title: "Actualización Correcta"
          });
        });
      this.cargarListadoUsuario();
      this.editarUsuarioFlag = false;
    },
    async registrarUsuario(event) {
      event.preventDefault();
      await this.axios
        .post(`${this.API}usuario/registro`, this.usuario)
        .then(res => {
          let response = res.data;
          console.log(res.data);
          if (response.estado) {
            this.cargarListadoUsuario();
            this.editarUsuarioFlag = false;
            this.$swal({
              icon: "success",
              title: "Registro Correcto."
            });
            this.registrarUsuario = false;
          } else {
            this.errorUsuario = response.msg;
          }
        });
      this.cargarListadoUsuario();
      this.editarUsuarioFlag = false;
    },
    eliminarUsuario(users) {
      this.$swal({
        icon: "warning",
        title: "Eliminar usuario",
        html: `<b>${users.NombreUsuario}</b> dejará de iniciar sesión y no aparecerá en las listas operativas.`,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#dc3545"
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios
            .post(`${this.API}usuario/actualizar`, { ...users, Anulado: 1, actualizarClave: false })
            .then(res => {
              if (res.data) {
                this.$swal({
                  icon: "success",
                  title: "Actualización Correcta."
                });
              }
            });
          this.cargarListadoUsuario();
        }
      });
      //   console.log(users);
    },
    reactivarUsuario(users) {
      this.$swal({
        icon: "question",
        title: "Reactivar usuario",
        html: `<b>${users.NombreUsuario}</b> volverá a estar disponible en el sistema.`,
        showCancelButton: true,
        confirmButtonText: "Reactivar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#10b981"
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios.post(`${this.API}usuario/actualizar`, {
            ...users,
            Anulado: 0,
            actualizarClave: false
          });
          await this.cargarListadoUsuario();
          this.vistaUsuarios = "activos";
          this.$swal({
            icon: "success",
            title: "Usuario reactivado",
            timer: 1600,
            showConfirmButton: false
          });
        }
      });
    },
    async abrirEditarPerfil(item) {
      let perfil = { ...item };
      this.IdUsuarioModulo = "";
      this.perfil = perfil;
      await this.axios(`${this.API}listarusuarioperfilmodulo`, {
        params: perfil
      }).then(response => {
        if (response.data) {
          this.lstModulosPerfil = response.data;
        }
      });
      this.editarUsuarioPerfilFlag = true;
    },
    abrirNuevoPerfil() {
      this.perfil = {};
      this.lstModulosPerfil = [];
      this.nuevoUsuarioPerfilFlag = true;
    },
    async cargarModulos() {
      await this.axios(`${this.API}listarmodulos`).then(response => {
        if (response.data) {
          this.lstModulos = response.data;
        }
      });
      // this.editarUsuarioPerfilFlag = true;
    },

    seleccionarModulo(IdUsuarioModulo) {
      this.Module = this.lstModulos.find(
        m => m.IdUsuarioModulo === IdUsuarioModulo
      );
    },
    anadirModule() {
      if (!this.IdUsuarioModulo) {
        return;
      }
      let IdUsuarioModules = this.lstModulosPerfil.map(v => v.IdUsuarioModulo);

      // Verificar si el IdUsuarioModulo de this.Module ya existe en lstModulos
      if (
        this.Module &&
        IdUsuarioModules.includes(this.Module.IdUsuarioModulo)
      ) {
        return false;
      }

      // Asegurar que Module sea un objeto válido antes de modificarlo
      if (this.Module) {
        this.Module.Lectura = 1;
        this.Module.Escritura = 1;

        // Agregar el objeto a la lista
        this.lstModulosPerfil.push({ ...this.Module });
        this.IdUsuarioModulo = "";
      }
    },
    abrirNuevoModulo() {
      this.nuevoModuloFlag = true;
    },
    guardarModulo() {
      this.errorModulo = "";

      if (!this.modulo) {
        this.errorModulo = "Dato Requerido.";
        return false;
      }

      if (this.lstModulos.some(v => v.UsuarioModulo == this.modulo)) {
        this.errorModulo = "El módulo ya se encuentra registrado.";
        return false;
      }

      let data = {
        modulo: this.modulo
      };
      this.axios.post(`${this.API}modulo/registro`, data).then(res => {
        if (res.data) {
          this.nuevoModuloFlag = false;
          this.$swal({
            icon: "success",
            title: "Registro Correcto"
          });
          this.cargarModulos();
        }
      });
    },
    guardarUsuarioPerfil() {
      this.errorPerfil = "";
      if (!this.perfil.UsuarioPerfil) {
        this.errorPerfil = "Dato requerido";
        return;
      }
      let data = {
        UsuarioPerfil: this.perfil.UsuarioPerfil,
        lstModulosPerfil: this.lstModulosPerfil
      };

      this.axios.post(`${this.API}usuarioperfil/registro`, data).then(res => {
        if (res.data) {
          this.cargarListadoPerfil();
          this.nuevoUsuarioPerfilFlag = false;
        }
      });

      console.log(data);
    },
    actualizarUsuarioPerfil() {
      this.errorPerfil = "";
      if (!this.perfil.UsuarioPerfil) {
        this.errorPerfil = "Dato Requerido".return;
      }

      let data = {
        ...this.perfil,
        lstModulosPerfil: this.lstModulosPerfil
      };

      this.axios.post(`${this.API}usuarioperfil/actualizar`, data).then(res => {
        if (res.data) {
          this.cargarListadoPerfil();
          this.editarUsuarioPerfilFlag = false;
        }
      });
    },
    eliminarPerfil(perfil) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: "Desea eliminar el Perfil",
        showCancelButton: true,
        cancelButtonText: "Cancelar"
      }).then(res => {
        if (res.isConfirmed) {
          this.axios
            .post(`${this.API}usuarioperfil/eliminar`, perfil)
            .then(res => {
              if (res.data) {
                this.cargarListadoPerfil();
                // this.editarUsuarioPerfilFlag = false;
              }
            });
        }
      });
    }
  }
};
</script>

<style>
.btn {
  cursor: pointer;
}
.btn-acepted {
  background: #e65100;
  color: #ffffff;
  font-weight: bold;
}
.btn-acepted:hover {
  background: #e65100;
  color: #ffffff;
  font-weight: bold;
}
.btn-info {
  background: #1b5e20;
  color: #ffffff;
  font-weight: bold;
}
.btn-info:hover {
  background: #1b5e20;
  color: #ffffff;
  font-weight: bold;
}
.card-header {
  text-align: center !important;
  align-content: center;
  align-items: center;
}

.nav .nav-pills .card-header-pills {
  text-align: center !important;
  align-content: center !important;
  align-items: center !important;
}

.usuario-panel {
  overflow: hidden;
  border: 1px solid rgba(34, 211, 238, 0.24);
  border-radius: 18px;
  background: #f8fbff;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.1);
}
.usuario-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 20px 24px;
  color: #fff;
  background: linear-gradient(120deg, #073b5c 0%, #075f70 55%, #29153d 100%);
}
.usuario-toolbar h3 { margin: 2px 0 3px; font-size: 24px; font-weight: 800; }
.usuario-toolbar p { margin: 0; color: #c9eaf1; }
.usuario-eyebrow { color: #67e8f9; font-size: 11px; font-weight: 800; letter-spacing: .18em; }
.usuario-nuevo {
  min-width: 160px;
  border: 1px solid rgba(255,255,255,.45) !important;
  border-radius: 12px !important;
  color: #fff !important;
  font-weight: 800 !important;
  background: linear-gradient(135deg, #06b6d4, #2563eb) !important;
  box-shadow: 0 10px 24px rgba(6,182,212,.25);
}
.usuario-filtros {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 14px 18px;
  border-bottom: 1px solid #dbe7f3;
}
.usuario-segmentado { display: flex; gap: 8px; }
.usuario-segmentado button {
  padding: 9px 14px;
  border: 1px solid #c8d7e8;
  border-radius: 11px;
  color: #38526b;
  font-weight: 800;
  background: #fff;
  cursor: pointer;
  transition: .2s ease;
}
.usuario-segmentado button.activo {
  border-color: #22d3ee;
  color: #fff;
  background: linear-gradient(135deg, #0891b2, #4f46e5);
  box-shadow: 0 7px 18px rgba(37,99,235,.2);
}
.usuario-segmentado span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 22px;
  margin-left: 5px;
  padding: 0 6px;
  border-radius: 10px;
  background: rgba(255,255,255,.2);
}
.usuario-buscador { position: relative; width: min(360px, 100%); }
.usuario-buscador i { position: absolute; top: 12px; left: 13px; color: #5f7891; }
.usuario-buscador input {
  width: 100%;
  height: 41px;
  padding: 0 14px 0 38px;
  border: 1px solid #c8d7e8;
  border-radius: 12px;
  outline: none;
  background: #fff;
}
.usuario-buscador input:focus { border-color: #22d3ee; box-shadow: 0 0 0 3px rgba(34,211,238,.12); }
.usuario-tabla-wrap { overflow-x: auto; padding: 8px 18px 18px; }
.usuario-tabla { margin: 0; }
.usuario-tabla thead th { padding: 13px 10px; border-top: 0; color: #557087; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; }
.usuario-tabla tbody td { vertical-align: middle; padding: 11px 10px; border-color: #e7eef6; }
.usuario-estado { display: inline-flex; align-items: center; gap: 7px; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; }
.usuario-estado i { width: 8px; height: 8px; border-radius: 50%; }
.usuario-estado.activo { color: #047857; background: #d1fae5; }
.usuario-estado.activo i { background: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.14); }
.usuario-estado.eliminado { color: #b91c1c; background: #fee2e2; }
.usuario-estado.eliminado i { background: #ef4444; }
.usuario-acciones .btn { margin-left: 6px; padding: 7px 11px; border: 0; border-radius: 9px; color: #fff; font-weight: 700; }
.usuario-acciones .btn span { margin-left: 5px; }
.btn-editar { background: linear-gradient(135deg, #f59e0b, #f97316) !important; }
.btn-eliminar { background: linear-gradient(135deg, #ef4444, #be123c) !important; }
.btn-reactivar { background: linear-gradient(135deg, #10b981, #059669) !important; }
.usuario-vacio { padding: 38px !important; text-align: center; color: #71869a; }
.usuario-vacio i, .usuario-vacio strong, .usuario-vacio span { display: block; }
.usuario-vacio i { margin-bottom: 8px; font-size: 30px; color: #8da5ba; }
@media (max-width: 768px) {
  .usuario-toolbar, .usuario-filtros { align-items: stretch; flex-direction: column; }
  .usuario-buscador { width: 100%; }
  .usuario-segmentado button { flex: 1; }
  .usuario-acciones .btn span { display: none; }
}
</style>
