<template>
  <div id="app">
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
}

.clsCaja {
  padding: 15px !important;
  margin: 5px 0px !important;
  border: 2px solid #1a237e !important;
  border-radius: 25px !important;
  box-shadow: 3px 3px #1a237e !important;
}
.clsCuenta {
  padding: 15px !important;
  margin: 5px 0px !important;
  border: 2px solid #1a237e !important;
  border-radius: 25px !important;
  box-shadow: 3px 3px #1a237e !important;
}
</style>
