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
#modalOrdenCompra .modal-dialog {
  min-width: 70% !important;
}

.spinner {
  /* Tamaño del spinner */
  width: 50px;
  height: 50px;

  /* Grosor y color del borde (el fondo circular) */
  border: 5px solid rgba(0, 0, 0, 0.1);

  /* Color de la parte que gira */
  border-left-color: #3498db;

  /* Lo hacemos circular */
  border-radius: 50%;

  /* Aplicamos la animación */
  animation: spin 1s linear infinite;
}

/* Definición de la animación */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
