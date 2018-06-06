import { API } from './config.js'
import axios from 'axios'

export default {
    buscarProveedores ({ commit }, search) {
        axios.get(API + 'proveedores?limit=10&q=' + search).then((response) => {
          commit('LLENAR_PROVEEDORES_FILTRADOS', response.data)
        })
    },
    buscarAlmacenes ({ commit }, search) {
        axios.get(API + 'almacenes?q=' + search).then((response) => {
            commit('LLENAR_ALMACENES_FILTRADOS', response.data)
        })
    }
}