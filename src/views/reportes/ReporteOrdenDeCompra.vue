<template>
  <b-container fluid>
    <center>
        <h1>Reporte de Orden de Compra</h1>
    </center>
    <b-card no-body>
      <b-tabs v-model="tabIndex" card>
        <b-tab title="Reporte Por Cliente">
            Tab contents 1
        </b-tab>
        <b-tab title="Reporte por Productos">
            Tab contents 2
        </b-tab>
        
      </b-tabs>
    </b-card>
  </b-container>
</template>

<script>
export default {
  data() {
    return {
      tabIndex: 0,
      lstRepCliente: [],
      lstRepProductos: []
    };
  }
};
</script>

<style>
</style>
