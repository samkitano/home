import { find } from 'lodash'

let cursor = '<span class="blink">_</span>'

const mutations = {
  CLEAR_CONSOLE (state) {
    state.console = []
  },

  CLOSE_FORM (state) {
    state.showCreateModal = false
  },

  POP_CONSOLE (state) {
    if (state.console[state.console.length - 1] === cursor) {
      state.console.pop()
    }
  },

  OUTPUT (state, msg) {
    if (state.console[state.console.length - 1] === cursor) {
      state.console.pop()
    }

    state.console.push(msg)
  },

  RESET_TYPE (state) {
    state.type = ''
  },

  SET_INFO_MODAL (state, str) {
    state.infoModal = str
  },

  SET_PROJECTS_DATA (state, data) {
    state.data = data
  },

  SET_TEMPLATE (state, str) {
    state.template = str
  },

  SET_TEMPLATE_OPTIONS (state, options) {
    state.templateOptions = options
  },

  RESET_TEMPLATE_OPTIONS (state) {
    state.templateOptions = {}
    state.template = ''
    state.templates = []
  },

  OPEN_FORM (state, type) {
    let manager = find(state.data.managers, { name: type })

    state.done = false
    state.error = false
    state.type = type
    state.templates = manager.templates
    state.hasTemplates = manager.templates.length > 0
    state.showCreateModal = true
  },

  UPDATE_SITES (state, site) {
    state.data.sites.push(site)
  },

  // CREATING
  SET_CREATING (state) {
    state.creating = true
  },
  UNSET_CREATING (state) {
    state.create = false
  },

  // DONE
  SET_DONE (state) {
    state.done = true
  },

  UNSET_DONE (state) {
    state.done = false
  },

  // ERROR
  SET_ERROR (state) {
    state.error = true
  },

  UNSET_ERROR (state) {
    state.error = false
  },

  // WORKING
  SET_WORKING (state) {
    state.working = true
  },
  UNSET_WORKING (state) {
    state.working = false
  }
}

export default mutations
