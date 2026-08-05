<template>
    <div class="txtText" v-click-outside="handleOutsideClick" >
      <input 
        type="text" 
        class="form-control txtBuscar" 
        v-model="text"  
        @input="buscar"
        @focus="handleFocus"
        @click="$event.target.select()"
      />
      <div>
        <div v-for="(list,index) in listado" :key="index" @click="guardarCliente(list)" class="trDetails">
           {{ list.ClienteDniRuc }} 
        </div>

      </div>
    </div>
  </template>
  
  <script>
import config from "@/store/config.js";

export default {
  name: "AutoCompleteComponent",
  props: {
    value: {
      type: String,
      default: ""
    },
    touchEnabled: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      API: config.API,
      text: this.value,
      listado: []
    };
  },
  watch: {
    value() {
      this.text = this.value;
    },
    text(newVal) {
      this.$emit("input", newVal); // Emite el cambio al padre
    }
  },
  directives: {
    clickOutside: {
      bind(el, binding, vnode) {
        el.clickOutsideEvent = event => {
          // Verifica si el clic fue fuera del elemento
          if (!(el === event.target || el.contains(event.target))) {
            binding.value(event); // Llama a la función cuando ocurre
          }
        };
        document.addEventListener("click", el.clickOutsideEvent);
      },
      unbind(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
      }
    }
  },
  mounted() {
    document.addEventListener("keydown", this.handleKeyDown);
  },
  beforeDestroy() {
    document.removeEventListener("keydown", this.handleKeyDown);
  },
  methods: {
    handleFocus() {
      if (this.touchEnabled) {
        this.$emit("touch-focus", this.text);
        return;
      }
      this.buscar();
    },
    setTextAndSearch(value) {
      this.text = value == null ? "" : String(value);
      this.$nextTick(() => this.buscar());
    },
    handleOutsideClick() {
      this.listado = [];
    },
    async buscar() {
      this.$emit("dniRuc", this.text);
      await this.axios(`${this.API}clientes`, {
        params: { q: this.text }
      }).then(res => {
        this.listado = res.data;
      });
    },
    handleKeyDown(event) {
      if (event.key === "Escape" || event.keyCode === 27) {
        this.listado = [];
      }
    },
    guardarCliente(event) {
      this.$emit("guardarCliente", event);
      this.listado = [];
    }
  }
};
</script>
  
<style>
.txtText {
  position: relative; /* Define un contenedor relativo */
  min-width: 82%;
  max-width: 82%;
}

.trDetails {
  font-weight: bold;
  background: #fff;
  cursor: pointer;
  font-size: 0.9rem;
  border: none;
  border: 0 0 1px 0 solid #000;
  text-align: left;
  padding: 2px 10px;
}

.trDetails:hover {
  background: #007bff;
  color: #fff;
}

/* Estilo para la lista flotante */
.txtText > div {
  position: absolute; /* Superpone la tabla */
  top: 100%; /* Debajo del input */
  left: 0;
  width: 100%;
  background: white;
  border: 1px solid #ccc;
  max-height: 200px; /* Control de tamaño */
  overflow-y: auto; /* Scroll si es necesario */
  z-index: 1000; /* Asegura estar encima */
}
.txtBuscar {
  min-width: 725px !important;
}
@media (max-width: 1250px) {
  .txtBuscar {
    min-width: 240px !important;
  }
}
</style>
