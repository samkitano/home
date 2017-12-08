export const cancel = ({ commit }) => {
  commit('CANCEL')
}

export const clearConsole = ({ commit }) => {
  commit('CLEAR_CONSOLE')
}

export const nextStep = ({ commit }) => {
  commit('NEXT_STEP')
}

export const prevStep = ({ commit }) => {
  commit('PREV_STEP')
}

export const popConsole = ({ commit }) => {
  commit('POP_CONSOLE')
}

export const resetCreate = ({ commit }) => {
  commit('RESET_CREATE')
}

export const resetStep = ({ commit }) => {
  commit('RESET_STEP')
}

export const output = ({ commit }, msg) => {
  commit('OUTPUT', msg)
}

export const setCreating = ({ commit }) => {
  commit('SET_CREATING')
}

export const setDone = ({ commit }) => {
  commit('SET_DONE')
}

export const setError = ({ commit }) => {
  commit('SET_ERROR')
}

export const setInfoModal = ({ commit }, data) => {
  commit('SET_INFO_MODAL', data)
}

export const setSteps = ({ commit }, nSteps) => {
  commit('SET_STEPS', nSteps)
}

export const setTemplates = ({ commit }, templates) => {
  commit('SET_TEMPLATES', templates)
}

export const setProjectsData = ({ commit }, data) => {
  commit('SET_PROJECTS_DATA', data)
}

export const setType = ({ commit }, str) => {
  commit('SET_TYPE', str)
}

export const setValid = ({ commit }, stt) => {
  commit('SET_VALID', stt)
}

export const setWorking = ({ commit }) => {
  commit('SET_WORKING', true)
}

export const unsetCancel = ({ commit }) => {
  commit('UNSET_CANCEL')
}

export const unsetError = ({ commit }) => {
  commit('UNSET_ERROR')
}

export const unsetCreating = ({ commit }) => {
  commit('SET_CREATING', false)
}

export const unsetDone = ({ commit }) => {
  commit('SET_DONE', false)
}

export const unsetType = ({ commit }) => {
  commit('SET_TYPE', '')
}

export const unsetWorking = ({ commit }) => {
  commit('SET_WORKING', false)
}

export const updateSites = ({ commit }, data) => {
  commit('UPDATE_SITES', data)
}
