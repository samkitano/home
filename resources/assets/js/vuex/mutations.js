const mutations = {
  CANCEL (state) {
    state.cancel = true
    state.console = []
    state.done = false
    state.error = false
    state.step = 1
    state.type = ''
    state.working = false
  },
  CLEAR_CONSOLE (state) {
    state.console = []
  },
  NEXT_STEP (state) {
    state.step++
    state.error = false
  },
  OUTPUT (state, msg) {
    state.console.push(msg)
  },
  PREV_STEP (state) {
    state.step--
    state.error = false
  },
  POP_CONSOLE (state) {
    state.console.pop()
  },
  RESET_CREATE (state) {
    state.console = []
    state.error = false
    state.done = false
    state.step = 1
    state.type = ''
  },
  RESET_STEP (state) {
    state.step = 1
  },
  SET_CANCEL (state) {
    state.cancel = true
  },
  SET_CREATING (state) {
    state.creating = true
    state.working = true
    state.console.pop()
  },
  SET_DONE (state) {
    state.done = true
    state.creating = false
    state.working = false
  },
  SET_ERROR (state) {
    state.error = true
  },
  SET_INFO_MODAL (state, data) {
    state.infoModal = data
  },
  SET_PROJECTS_DATA (state, data) {
    state.data = data
  },
  SET_STEPS (state, data) {
    state.steps = data
  },
  SET_TEMPLATES (state, data) {
    state.templates = data
  },
  SET_TYPE (state, type) {
    state.type = type
  },
  SET_VALID (state, boolState) {
    state.valid = boolState
  },
  SET_WORKING (state, stt) {
    state.working = stt
  },
  UNSET_CANCEL (state) {
    state.cancel = false
  },
  UNSET_CREATING (state) {
    state.create = false
  },
  UNSET_DONE (state) {
    state.done = false
  },
  UNSET_ERROR (state) {
    state.error = false
  },
  UNSET_WORKING (state) {
    state.working = false
  },
  UPDATE_SITES (state, site) {
    state.data.sites.push(site)
  }
}

export default mutations
