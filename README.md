# boticawebpack

> A Vue.js project

## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build

# build for production and view the bundle analyzer report
npm run build --report
```

For a detailed explanation on how things work, check out the [guide](http://vuejs-templates.github.io/webpack/) and [docs for vue-loader](http://vuejs.github.io/vue-loader).

## Producción estandarizada

`npm run deploy:boticav1` también publica en el backend los verificadores de
producción. En cada servidor/proyecto se realiza una sola instalación:

```bash
sudo bash build/deploy/install-production.sh
```

Las actualizaciones posteriores ejecutan automáticamente la validación después
de `git pull`. Las credenciales permanecen únicamente en
`config/database.local.php`, que está excluido de Git.
