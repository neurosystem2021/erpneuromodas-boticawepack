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
.modal .modal-xl {
  min-width: 80%;
  width: 80%;
}
/* #modalProformas___BV_modal_body_ {
  min-width: 80%;
  width: 80%;
} */
html,
body {
  height: 100%;
}

body {
  background-color: transparent !important;
  margin: 0;
  font-family: Roboto, Arial, sans-serif;
}

.container,
.container-fluid {
  background-color: transparent; /* deja ver el fondo del body */
}
.btnAction {
  margin: 2px 0;
  border-radius: 25px !important;
  border: 0px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  font-size: 1.2rem !important;
  font-weight: bold !important;
  display: block;
  width: 100%;
  margin: 1.5% 0px;
  text-transform: uppercase;
  /* margin: 20px 0 0; */
}
.container,
.container-fluid,
. {
  background-color: transparent !important;
  background: none !important;
}

.card {
  background-color: #e3f2fd !important;
}
</style>
