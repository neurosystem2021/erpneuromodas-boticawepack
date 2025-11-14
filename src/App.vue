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
</style>
