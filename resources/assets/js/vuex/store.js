import * as actions from './actions'
import mutations from './mutations'

export default function () {
  return {
    actions,
    mutations,
    state: {
      error: false,
      console: [],
      creating: false,
      data: {},
      hasTemplates: false,
      infoModal: {},
      showCreateModal: false,
      template: '',
      templateOptions: {},
      templates: [],
      type: '',
      working: false
    }
  }
}
