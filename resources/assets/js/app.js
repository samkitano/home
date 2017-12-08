/* global Vue */
import BootstrapVue from 'bootstrap-vue'
import VueSweetalert2 from 'vue-sweetalert2'
import Echo from 'laravel-echo'
import Vuex from 'vuex'
import store from './vuex/store'
import H from './components/App.vue'

import 'babel-polyfill'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

window.Vue = require('vue')
window.axios = require('axios')
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
window.Pusher = require('pusher-js')
window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'f375095695b9b0f96c1c',
  cluster: 'eu',
  encrypted: true
})

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token')
}

// Vue.use(BootstrapVue); // FIXME: uncomment after removing below workaround
Vue.use(VueSweetalert2)
Vue.use(Vuex)
let vStore = store()
Vue.component('app', Object.assign({}, H, {
  store: new Vuex.Store(vStore)
}))

/**
 * FIXME
 *
 * workaround for bootstrapvue
 * waiting for new release
 * see: https://github.com/bootstrap-vue/bootstrap-vue/issues/1201
 */
let originalVueComponent = Vue.component
Vue.component = function (name, definition) {
  if (Array.isArray(definition.components) && definition.components.length === 1) {
    definition.components = {[name]: definition.components[0]}
  }
  originalVueComponent.apply(this, [name, definition])
}

Vue.use(BootstrapVue)
Vue.component = originalVueComponent

Vue.config.productionTip = false

require('./mixins')

const app = new Vue({ // eslint-disable-line no-unused-vars
  el: '#app'
})
