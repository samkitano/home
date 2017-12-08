import * as actions from './actions'
import mutations from './mutations'

export default function () {
  return {
    actions,
    mutations,
    state: {
      action: '',
      cancel: false,
      console: [],
      creating: false,
      data: {},
      done: false,
      error: false,
      infoModal: {},
      steps: 0,
      step: 1,
      templates: [],
      type: '',
      valid: false,
      working: false
    }
  }
}
