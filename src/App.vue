<template>
  <div id="app" class="mt-0 pt-0">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: "App"
};
</script>

<style>
.btnAction {
  margin: 2px 0;
  border-radius: 25px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  font-size: 1.2rem;
  font-weight: bold;
  text-transform: uppercase;
}
.btn .bnt-sire {
  border: 1px solid #b5bd00;
  background: #ffffff;
  font-size: 1.3rem;
  font-weight: bold;
  color: #b5bd00;
}
.btn .bnt-sire:hover {
  border: 1px solid #b5bd00;
  background: #dadf7d;
}
.calc__numbers .botones {
  /* border: 1px solid #000000; */
  cursor: pointer;
  text-align: center;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
  border: 1px;
}
.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers .botones {
  /* border: 1px solid #000000; */
  cursor: pointer;
  text-align: center;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
  border: 1px;
}
#modalCodigoBarra {
  padding: 0 !important;
}
#modalCodigoBarra .modal-dialog {
  max-width: 50vw !important;
  max-height: 50vh !important;
}
#modalCodigoBarra .modal-body {
  padding-bottom: 1px !important;
  margin-bottom: 1px !important;
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
  .ocultarMovil {
    display: none;
  }
  .mostrarMovil {
    display: block;
  }
}
@media only screen and (max-width: 375px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
}
#codigoBarraStyle video,
canvas {
  width: 100%;
  height: 100%;
}
.cameraBarra {
  height: 20em;
  width: auto;
  margin: 0 auto;
}

.headerTable {
  background: #1a237e !important;
  color: #ffff;
  border: 1px solid #ffffff !important;
}
.headerSubTitle {
  background: #7986cb !important;
  color: #ffff;
  border: 1px solid #ffffff !important;
}
</style>
