
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

window.Bus = new Vue()

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
window.Pusher = require('pusher-js')

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'f375095695b9b0f96c1c',
  cluster: 'eu',
  encrypted: true
})

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token')
}

// Vue.use(BootstrapVue); // FIXME: uncomment after removing below workaround
Vue.use(VueSweetalert2)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
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
  if (name === 'bFormCheckboxGroup' || name === 'bCheckboxGroup' ||
    name === 'bCheckGroup' || name === 'bFormRadioGroup') {
    definition.components = { bFormCheckbox: definition.components[0] }
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
