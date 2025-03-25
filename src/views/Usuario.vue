<template>
  <b-container>
    <b-row>
        <b-col cols="12">
            <b-tabs content-class="mt-3 mx-3" fill card pills>
              <b-tab title="MODULOS" class="py-0">
                  <b-row>
                    <b-button variant="success" @click="abrirNuevoModulo()">
                      NUEVO
                    </b-button>
                    <b-table 
                        :items="lstModulos" 
                        class="table-sm"
                        :per-page="perPage"
                        :current-page="currentPage"  
                        :fields="fields"
                      ></b-table>
                    <b-pagination 
                      :total-rows="lstModulos.length" 
                      :per-page="perPage" 
                      v-model="currentPage" 
                      class="my-0" 
                    />
                  </b-row>
                  <!-- {{ lstModulos }} -->
                  
                </b-tab>
                <b-tab title="PERFILES" class="py-0">
                    <b-row>
                        <b-col cols="12">
                          <b-button variant="success" @click="abrirNuevoPerfil()">Nuevo Perfil</b-button>
                        </b-col>
                        <b-col cols="12">
                            <table class="table table-sm">
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
                            </table>
                        </b-col>
                    </b-row>
                </b-tab>
                <b-tab title="USUARIOS"  active class="py-0"> 
                    <b-row>
                        <b-col cols="12" class="my-1">
                            <b-button  @click="abrirNuevoUsuario()" variant="success">Nuevo Usuario</b-button>
                        </b-col>
                        <b-col cols="12">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th >Perfil</th>
                                        <th >Nombre Usuario</th>
                                        <th >Usuario</th>
                                        <th >Activo</th>
                                        <th ></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(usuario,index) in lstUsuario" :key="index">
                                        <td>{{usuario.UsuarioPerfil}}</td>
                                        <td>{{usuario.NombreUsuario}}</td>
                                        <td>{{usuario.Usuario}}</td>
                                        <td>
                                            <div class="badge bg-success px-3 py-3" style="color:#ffffff; font-weight: bold;"  v-if="usuario.Anulado==0" role="alert">
                                                SI
                                            </div>
                                            <div class="badge bg-danger px-3 py-3"  style="color:#ffffff; font-weight: bold;" role="alert" v-else>
                                                NO
                                            </div>
                                        </td>
                                        <td>
                                            <b-button variant="warning" @click="abrirEditarUsuario(usuario)">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </b-button>
                                            <b-button variant="danger" v-if="usuario.Anulado==0" @click="eliminarUsuario(usuario)">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </b-button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </b-col>
                    </b-row>
                </b-tab>
              
            </b-tabs>
        </b-col>
    </b-row>
    
    <b-modal v-model="editarUsuarioFlag" size="lg" :title="`Editar Usuario: ${usuario.Usuario}`" hide-footer>
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
        <b-form-checkbox class="my-3 d-block w-100" :value="1" :unchecked-value="0" v-model="usuario.Anulado"> Anulado</b-form-checkbox>
        <b-form-checkbox class="my-3 d-block w-100"  v-model="usuario.actualizarClave"> Actualizar Clave</b-form-checkbox>
        <b-input-group v-if="usuario.actualizarClave">
            <b-input-group-prepend>
                <b-input-group-text @click="verclave=!verclave">
                    <i :class="!verclave ? 'fa fa-eye' : 'fa fa-eye-slash'"></i> <!-- Ícono de Bootstrap Icons -->
                </b-input-group-text>
            </b-input-group-prepend>
            <input :type="verclave ? 'text ': 'password'" v-model="usuario.clave" class="form-control" v-if="usuario.actualizarClave" placeholder="Nueva Clave">
        </b-input-group>
        
        <b-button class="my-3" variant="success" @click="actualizarUsuario()"> Actualizar </b-button>
        <b-button class="my-3" variant="danger" @click="editarUsuarioFlag=!editarUsuarioFlag"> Cancelar </b-button>
    </b-modal>

    <b-modal v-model="nuevoUsuarioFlag" size="lg" title="Nuevo Usuario" hide-footer>
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
           
           
            <b-input-group class="my-3">
                <b-input-group-prepend>
                    <b-input-group-text @click="verclave=!verclave">
                        <i :class="!verclave ? 'fa fa-eye' : 'fa fa-eye-slash'"></i> <!-- Ícono de Bootstrap Icons -->
                    </b-input-group-text>
                </b-input-group-prepend>
                <input :type="verclave ? 'text ': 'password'" v-model="usuario.clave" class="form-control" placeholder="Clave" required>
            </b-input-group>
            <p style="color: red;">{{ errorUsuario }}</p>
            <b-button type="submit" class="my-3" variant="success"> Guardar </b-button>
            <b-button class="my-3" variant="danger" @click="nuevoUsuarioFlag=!nuevoUsuarioFlag"> Cancelar </b-button>
        </b-form>
    </b-modal>

    <b-modal v-model="editarUsuarioPerfilFlag" no-close-on-esc  no-close-on-backdrop size="lg" title="Editar Perfil" hide-footer>
      Perfil
      <input type="text" class="form-control" v-model="perfil.UsuarioPerfil">
      <p style="text-align: center; color: red;"> {{ errorPerfil }}</p>
      <b-form-checkbox class="my-3 d-block w-100" :value="1" :unchecked-value="0" v-model="perfil .Anulado"> Anulado</b-form-checkbox>
      Module
      <b-input-group class="my-3">
        
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
              ADD
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

    <b-modal v-model="nuevoUsuarioPerfilFlag" no-close-on-esc  no-close-on-backdrop size="lg" title="Nuevo Perfil" hide-footer>
      Perfil
      <input type="text" class="form-control" v-model="perfil.UsuarioPerfil">
      <p style="text-align: start; color: red;"> {{ errorPerfil }}</p>
      Module
      <b-input-group class="my-3">
        
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
              ADD
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
     
    <b-modal v-model="nuevoModuloFlag" hide-footer title="Nuevo Módulo">
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
  mounted() {
    this.cargarModulos();
    this.cargarListadoPerfil();
    this.cargarListadoUsuario();
    this.cargarListadoAlmacen();
    this.cargarPuntoVenta();
  },
  methods: {
    async cargarListadoUsuario() {
      await this.axios(`${this.API}usuarios/listado`).then(res => {
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
      users.Anulado = 1;
      this.$swal({
        icon: "question",
        title: "Eliminar registro",
        text: `Desea desactivar el registro ${users.NombreUsuario}`,
        showCancelButton: true
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios
            .post(`${this.API}usuario/actualizar`, users)
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
</style>
