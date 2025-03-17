<template>
  <div class="contenedor">
    <div class="login">
       <div class="clsTitle"> <img src="/resources/images/logo.png" style="width:90%; margin-bottom:-10px"/></div>
       <div class="clsGroupInput">
        <i class="clsIcon fa fa-user" aria-hidden="true"></i>
        <input class="clsInput" type="text" name="" id="usr" placeholder="Usuario" v-model="user" autocomplete="off">
       </div>
       <div class="clsGroupInput">
        <i @click="mostrarClave=!mostrarClave" v-if="!mostrarClave" class="clsIcon fa fa-eye" aria-hidden="true"></i>
        <i @click="mostrarClave=!mostrarClave" v-else class="clsIcon fa fa-eye-slash" aria-hidden="true"></i>
        <input :type="mostrarClave ? 'text': 'password'" class="clsInput"  name="" id="pass" placeholder="Clave" v-model="password" autocomplete="off">
       </div>
       <div class="clsButon">
        <p style="color:red">{{ errorLogin }}</p>
        <button class="btnLogin" @click="acceder()">ACCEDER</button>
       </div>
       <div class="clsFooter">
        <p>CONTACTO</p>
        <p>Telf: (062) 511550 -  Cel: 954370221</p>
        <p>Cel. Soporte: 997578199</p>
        <p><a href="http://neurosystemperu.com" target="_blank" class="clsLink">Neuro System Perú</a></p>
       </div>
    </div>
  </div>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      mostrarClave: false,
      user: "",
      password: "",
      errorLogin: ""
    };
  },
  mounted() {
    this.$nextTick(() => {
      const input = document.getElementById("usr");
      input.style.fontSize = "1.5em"; // Forzar la aplicación del tamaño
      input.focus(); // Mantiene el focus inicial
      input.blur(); // Lo quita para evitar que quede activo
    });
  },
  methods: {
    async acceder() {
      this.errorLogin = "";
      let data = {
        user: this.user,
        password: this.password
      };
      await this.axios.post(`${this.API}login`, data).then(res => {
        if (res.data) {
          //   console.log(window.location);
          //   let url = `${window.location.origin}/views/V_VentaForm.php`;
          //   console.log(url);
          //   window.location = url;
          setTimeout(() => {
            window.location.href = "/views/V_VentaForm.php";
          }, 1000);
        } else {
          this.errorLogin = "Datos Incorrectos";
        }
      });
    }
  }
};
</script>

<style scoped>
.contenedor {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  width: 100vw;
  background-color: #bdbdbd !important;
  background-image: url("/resources/images/mini.jpg");
  background-size: cover;
  background-position: center;
  text-align: center;
}
.login {
  border: 1px solid #fff;
  color: #fff;
  border-radius: 25px;
  align-content: center;
  align-items: center;
  text-align: center;
  backdrop-filter: blur(100px);
  transition: opacity 0.5s ease-in-out;
  background: rgb(224, 224, 224, 0.15);
  min-width: 30%;
  min-height: 60%;
  padding: 0 0 1% 0;
}
.clsTitle {
  margin: 35px 0;
  font-size: 4rem;
  font-weight: bold;
}
.clsGroupInput {
  background: #fff;
  margin: 25px auto;
  width: 80%;
  border-radius: 25px;
  padding: 5px;
  /* margin: auto!important; */
}
.clsButon {
  margin: 6% 0;
  /* padding: 5% 0; */
  font-size: 1.5em;
}

.btnLogin {
  background: #ffd600;
  border: none;
  width: 50%;
  font-weight: bold;
  font-size: 1.5em;
  border-radius: 10px;
  color: #fff;
}
.clsIcon {
  color: #ffd600;
  font-size: 1.5em;
}
.clsInput {
  font-size: 1.5em;
  background: #fff !important;
  border: none;
  width: 80%;
  text-align: center;
  -webkit-text-size-adjust: 100%;
  /* margin: 0 5px; */
}

.clsFooter {
  font-size: 1.2em;
}
.clsLink {
  text-decoration: none;
  font-size: 1.2em;
  cursor: pointer;
  background: #fff;
  padding: 1% 5%;
  border: none;
  border-radius: 25px;
  margin: 0 0 5% 0;
}
p {
  padding: 0 !important;
  margin: 1.5% !important;
}
@media (max-width: 1024px) {
  .login {
    border: 1px solid #fff;
    color: #fff;
    border-radius: 25px;
    align-content: center;
    align-items: center;
    text-align: center;
    backdrop-filter: blur(100px);
    transition: opacity 0.5s ease-in-out;
    background: rgb(224, 224, 224, 0.15);
    /* background: rgba(255, 255, 255, 0.15); */
    backdrop-filter: blur(5px);
    min-width: 50%;
    min-height: 60%;
  }
}
</style>
