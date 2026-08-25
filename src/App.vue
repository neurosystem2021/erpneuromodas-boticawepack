<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
.jobi-btn-action {
  margin: 2px 0;
  border-radius: 25px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.3);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.3);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.3);
  font-size: 1.2rem;
  font-weight: bold;
  text-transform: uppercase;
}
</style>
