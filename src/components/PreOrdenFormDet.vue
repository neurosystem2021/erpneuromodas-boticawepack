<template>
  <div>
    <b-form-group>
      <label>Agrega Productos:</label>
      <b-input-group>
        <autocomplete
          :url="API + 'productos'"
          ref="inputProducto"
          anchor="Producto"
          :onShouldRenderChild="renderChild"
          label="writer"
          style="width:200px"
          placeholder="Seleccionar Producto"
          :on-select="addProducto"
          :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
        <autocomplete
          :url="API + 'productos/kits'"
          ref="productoKit"
          anchor="Producto"
          :onShouldRenderChild="renderChild"
          label="writer"
          style="width:200px"
          placeholder="Seleccionar Kit"
          :on-select="addProductoKit"
          :onFocus="focusAutocomplete.bind(null, 'productoKit')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
      </b-input-group>
    </b-form-group>
    <inventory-camera :embedded="true" :continuous="true" v-if="showProjectScanner" @close="showProjectScanner=false" @code="readProjectBarcode"/>
    <section class="project-barcode-search"><label for="projectProductBarcode"><i class="fas fa-barcode" aria-hidden="true"></i> Buscar producto por código de barras</label><div class="project-barcode-controls"><input id="projectProductBarcode" v-model="loadCode" @keydown.enter.prevent="loadBarcode" type="text" class="form-control" placeholder="Escanea o escribe el código" autocomplete="off" inputmode="text"/><b-button type="button" variant="primary" :disabled="loadingBarcode || !loadCode.trim()" @click="loadBarcode"><i class="fas fa-search" aria-hidden="true"></i> Buscar</b-button><b-button type="button" variant="success" :disabled="loadingBarcode" @click="openProjectScanner"><i class="fas fa-barcode" aria-hidden="true"></i> Escanear</b-button></div><small>Usa la cámara, un lector USB o escribe el código.</small></section>

    <div class="preorder-table-scroll"><table class="table b-table table-sm">
      <thead>
        <tr>
          <th>Mano de Obra</th>
          <th>Id</th>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Total</th>
          <th>Pagado</th>
          <th>Deuda</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(prod, index) in productos" v-show="!prod.isDisabled" :key="index" :class="{'bg-secondary text-white': prod.isDisabled,'bg-facturado':prod.Facturado==1}">
          <td data-label="Mano de obra">
            <input 
            @change="(e) => addManoDeObra(index, e)"
            v-if="prod.CodigoBarra != 'MANODEOBRA'" 
            v-model="prod.EsManoDeObra"
            :disabled="prod.Facturado==1"
            class="form-check-input" type="checkbox" />
          </td>
          <td data-label="Código">{{ prod.IdProducto }}</td>
          <td data-label="Producto">{{ prod.Producto }}</td>
          <td data-label="Precio unitario">
            <span class="hideInModal">{{ prod.Precio }}</span>
            <input :disabled="prod.Facturado ==1" step="0.0001" required type="number" class="form-control" v-model="prod.Precio" @input="emitUpdate" />
          </td>
          <td data-label="Cantidad">
            <span class="hideInModal">{{ prod.Cantidad }}</span> 
            <input :disabled="prod.isDisabled ==1 || prod.Facturado==1" required type="number" class="form-control" v-model="prod.Cantidad" @input="emitUpdate" />
          </td>
          <td data-label="Total">
            <span>{{parseFloat(prod.Precio * prod.Cantidad).toFixed(2)}}</span>
            
          </td>
          <td data-label="Pagado">{{ prod.MontoPagado }}</td>
          <td data-label="Saldo">
            <span>{{parseFloat(prod.Precio * prod.Cantidad).toFixed(2) - prod.MontoPagado }}</span>
            
          </td>
          <td data-label="Estado">
             {{ prod.Facturado==1 ? 'Facturado' : 'Sin comprobante'}}
          </td>
          <td class="text-center" data-label="Acciones"><b-button :disabled="prod.isDisabled==1" variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
        </tr>
      </tbody>
    </table></div>
  </div>    
</template>
<script>
import InventoryCamera from "@/components/InventoryCamera";
import config from "@/store/config.js";

export default {
  components:{InventoryCamera},
  beforeDestroy(){if(this._scanAudio){this._scanAudio.close().catch(()=>{});this._scanAudio=null;}},
  mounted() {
    this.getProductos();
  },
  data() {
    return {
      API: config.API,
      showProjectScanner:false,
      loadingBarcode:false,
      productosACargar: [],
      productos: [],
      loadCode: "",
      IdsProductoKit: []
    };
  },
  props: ["idPreorden", "idProducto"],
  computed: {
    sumProductos() {
      var sum = 0;
      this.productos.map(prod => {
        if (prod.EsManoDeObra == 0) {
          sum += prod.Cantidad * prod.Precio || 0;
        }
      });
      return sum;
    },
    manoDeObraBruta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.PrecioMO : 0;
      return precio;
    },
    manoDeObraMenosMat() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.PrecioMOmM : 0;
      return precio;
    }
  },
  watch: {
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].Precio) {
        this.productos[pi].Precio = this.productos[pi].Precio;
      }
    }
  },
  methods: {
    emitUpdate() {
      this.$emit("onUpdateProductos", this.productos);
    },
    getProductos() {
      if (this.idPreorden) {
        this.axios
          .get(this.API + "preorden/detalle", {
            params: { idPreOrden: this.idPreorden, sortDesc: "DEC" }
          })
          .then(res => {
            this.productos = res.data.map(item => {
              item.PrecioMO = item.Precio;
              item.PrecioMOmM = item.Precio;
              item.isDisabled = 0;
              if (item.IdProducto === this.idProducto) {
                item.isDisabled = true;
              }
              return item;
            });

            this.productosACargar.map(pc => {
              let producto = this.productos.find(
                pr => pr.IdProducto === pc.IdProducto
              );
              if (!producto) {
                this.productos.push(pc);
              }
            });
            this.emitUpdate();
          })
          .catch(err => {
            console.log(err);
          });
      }
    },
    renderChild(data) {
      return `
        <b>${data.IdProducto} - ${data.Producto} - ${data.CodigoBarra}</b>
      `;
    },
    openProjectScanner() {
      const Audio=window.AudioContext || window.webkitAudioContext;
      if(Audio){try { if(!this._scanAudio)this._scanAudio=new Audio();this._scanAudio.resume().catch(()=>{}); }catch(error){} }
      this.showProjectScanner=true;
    },
    readProjectBarcode(code) {
      if(this.loadingBarcode) return;
      this.loadCode=String(code);
      if(this._scanAudio){try{const oscillator=this._scanAudio.createOscillator(),gain=this._scanAudio.createGain();oscillator.connect(gain);gain.connect(this._scanAudio.destination);oscillator.frequency.value=1100;gain.gain.setValueAtTime(.12,this._scanAudio.currentTime);gain.gain.exponentialRampToValueAtTime(.001,this._scanAudio.currentTime+.15);oscillator.start();oscillator.stop(this._scanAudio.currentTime+.15);}catch(error){} }
      this.loadBarcode();
    },
    async loadBarcode(e) {
      if(e && e.preventDefault)e.preventDefault();
      const code=String(this.loadCode || '').trim();
      if(!code || this.loadingBarcode)return;
      this.loadingBarcode=true;
      try {
        const response=await this.axios.get(this.API+'productos/id/'+encodeURIComponent(code),{params:{codigoBarra:code}});
        if(response.data && response.data.IdProducto){this.addProducto(response.data);this.loadCode='';}
        else this.$toasted.error('No se encontró un producto con ese código.');
      }catch(error){this.$toasted.error('No se pudo buscar el código. Intente nuevamente.');}
      finally{this.loadingBarcode=false;}
    },

    addProductoKit(data) {
      // if (this.IdsProductoKit.some(v => v.IdProducto == data.IdProducto)) {
      //   return false;
      // }

      this.IdsProductoKit.push(data);
      let detalle = data.detalle;

      detalle.forEach(element => {
        if (this.productos.some(v => v.IdProducto == element.IdProducto)) {
          this.$toasted.error("Producto ya se encuentra en la lista");
          return false;
        }
        element.PrecioContado = element.PrecioContado;
        element.cantidad = element.Cantidad;
        element.Cantidad = element.Cantidad;

        this.productos.unshift(element);
        this.$toasted.success("Productos agregados");
      });
      this.emitUpdate();
      // console.log(data);
    },
    addProducto(data) {
      let index = this.productos.findIndex(
        el => el.IdProducto == data.IdProducto
      );

      if (index >= 0) {
        this.$toasted.show("El producto ya está agregado");
        return;
      }

      if (data.EsHabitacion) {
        this.$toasted.show("No puede ingresar este producto");
        return;
      }

      data.Precio = data.PrecioContado;
      data.cantidad = 1;
      data.Cantidad = 1;

      this.productos.unshift(data);
      this.$toasted.success("Producto agregado");
      this.emitUpdate();
    },
    quitarProducto(prod) {
      let index = this.productos.findIndex(
        el => el.IdProducto == prod.IdProducto
      );
      this.productos.splice(index, 1);
      this.emitUpdate();
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    addManoDeObra(index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");
      if (pi < 0) {
        this.$toasted.error("Primero seleccione el producto MANODEOBRA");

        this.productos[index].EsManoDeObra = 0;
        return;
      }

      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff";
        this.productos[pi].PrecioMOmM -=
          parseFloat(this.productos[index].Precio) *
          parseFloat(this.productos[index].Cantidad);
        this.productos[index].disabled = true;
      } else {
        e.target.parentNode.parentNode.removeAttribute("style");
        this.productos[pi].PrecioMOmM +=
          parseFloat(this.productos[index].Precio) *
          parseFloat(this.productos[index].Cantidad);
        this.productos[index].disabled = false;
      }
      this.emitUpdate();
    },
    updateManoDeObraNeta(index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precio : 0;

      this.productos.filter(p => p.EsManoDeObra).map(p => {
        precio -= parseFloat(p.precio) * parseFloat(p.Cantidad);
      });

      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio;
      }
    }
  }
};
</script>
<style scoped>
.hideInModal {
  display: none;
}
.form-check-input {
  margin-left: 0.75rem !important;
}
.bg-facturado {
  background: #dcedc8 !important;
}
</style>
