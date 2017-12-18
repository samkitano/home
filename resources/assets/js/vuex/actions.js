/** global axios */
import axios from 'axios'

let isJson = (str) => {
  try {
    let j = JSON.parse(str)
    if (j && typeof j === 'object') return j
  } catch (e) {}

  return false
}

let formatWhiteMsg = (str) => {
  let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
  let r2 = r1.replace(/\*\*/g, '</span>')

  return r2
}

let consoleColors = {
  info: 'cyan',
  error: 'red',
  warning: 'yellow',
  default: 'green'
}

export const clearConsole = ({ commit }) => {
  commit('CLEAR_CONSOLE')
}

export const popConsole = ({ commit }) => {
  commit('POP_CONSOLE')
}

export const writeToConsole = ({ commit }, out) => {
  let json = isJson(out)
  let type = 'default'
  let msg = out

  if (json) {
    type = json['type'] ? json.type : type
    msg = json['message'] ? json.message : msg
  }

  // strings between two asterisks will be parsed white
  if (msg.indexOf('**') > -1) {
    msg = formatWhiteMsg(msg)
  }

  // a nice blinking cursor
  if (msg === '_CURSOR_') {
    commit('OUTPUT', '<span class="blink">_</span>')
    return
  }

  commit('OUTPUT', `<span style="color:${consoleColors[type]}">${msg}</span>`)
}

export const output = ({ commit }, msg) => {
  commit('OUTPUT', msg)
}

export const setInfoModal = ({ commit }, data) => {
  commit('SET_INFO_MODAL', data)
}

export const setProjectsData = ({ commit }, data) => {
  commit('SET_PROJECTS_DATA', data)
}

export const openCreateForm = ({ commit }, type) => {
  commit('OPEN_FORM', type)
}

export const closeCreateForm = ({ commit }) => {
  commit('CLOSE_FORM')
  commit('CLEAR_CONSOLE')
}

export const setTemplate = ({ commit }, tpl) => {
  commit('SET_TEMPLATE', tpl)
}

export const setTemplateOptions = ({ commit, state }, tpl) => {
  commit('SET_TEMPLATE', tpl)

  if (!tpl && !state.templateOptions) return
  if (!tpl && state.hasTemplates) return

  commit('SET_WORKING')

  axios
    .get(`/options/${state.type}/${tpl}`)
    .then(r => {
      commit('SET_TEMPLATE_OPTIONS', r.data.options)
      commit('UNSET_WORKING')
    })
    .catch((e) => {
      let err = e.response.data

      commit('SET_ERROR')
      commit('OUTPUT', `<span style="color:red">ERROR!</span>`)

      if (err.exception) {
        commit('OUTPUT', `<span style="color:red">${err.exception}</span>`)
      }
      if (err.message) {
        commit('OUTPUT', `<span style="color:red">${err.message}</span>`)
      }
      if (err.file) {
        commit('OUTPUT', `<span style="color:red">File: ${err.file}</span>`)
      }
      if (err.line) {
        commit('OUTPUT', `<span style="color:red">Line: ${err.line}</span>`)
      }
      commit('UNSET_WORKING')
    })
}

export const resetTemplateOptions = ({ commit }) => {
  commit('RESET_TEMPLATE_OPTIONS')
}

export const setWorking = ({ commit }) => {
  commit('SET_WORKING')
}

export const unsetDone = ({ commit }) => {
  commit('UNSET_DONE')
}

export const unsetError = ({ commit }) => {
  commit('UNSET_ERROR')
}

export const unsetWorking = ({ commit }) => {
  commit('UNSET_WORKING')
}

export const updateSites = ({ commit }, site) => {
  commit('UPDATE_SITES', site)
}
