<template>
  <div id="app" :class="{ 'modern-report-page': isModernReport }">
    <router-view/>
  </div>
</template>

<script>
export default {
  name: "App",
  computed: {
    isModernReport() {
      const path = this.$route && this.$route.path ? this.$route.path.toLowerCase() : "";
      return path === "/busquedapormodelo" ||
        path === "/reimpresion" ||
        path.indexOf("/reportes/") === 0 ||
        path.indexOf("/reporte/") === 0;
    }
  }
};
</script>

<style>
@import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap");

html,
body,
#app {
  min-height: 100%;
}

body {
  margin: 0;
  background: #f4f7fb;
  color: #172033;
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  -webkit-font-smoothing: antialiased;
}

.btnAction {
  margin: 2px 0;
  border-radius: 25px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  font-size: 1.2rem;
  font-weight: bold;
}
.modal .modal-xl {
  min-width: 80%;
  width: 80%;
}
@media only screen and (max-width: 750px) {
  .modal .modal-xl {
    min-width: 100%;
    width: 100%;
    overflow-x: auto;
  }
}
</style>
