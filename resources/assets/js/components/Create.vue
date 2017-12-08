<template lang="html">
  <b-modal
    id="project"
    ref="project"
    size="lg"
    v-model="showModal"
    @hide="preventCloseIfWorking"
    @hidden="cancelCreating">

    <div slot="modal-header" class="w-100">
      <v-form-head/>
    </div>

    <v-form/>

    <div slot="modal-footer" class="w-100">
      <v-form-footer/>
    </div>
  </b-modal>
</template>

<script type="text/javascript">
/* global Echo */
import vForm from './createModalForm'
import vFormHead from './createModalHead'
import vFormFooter from './createModalFooter'
import { find } from 'lodash'
import { mapActions } from 'vuex'

const consoleColors = {
  info: 'cyan',
  error: 'red',
  warning: 'yellow',
  default: 'green'
}

export default {
  components: {
    vForm,
    vFormHead,
    vFormFooter
  },

  created () {
    Echo
      .channel('console')
      .listen('ConsoleMessageEvent', (e) => {
        if (e.message) {
          this.sendOutput(e.message)
        }
      })
  },

  data () {
    return {
      showModal: false
    }
  },

  methods: Object.assign({}, mapActions([
    'cancel',
    'clearConsole',
    'nextStep',
    'output',
    'prevStep',
    'resetStep',
    'resetCreate',
    'setSteps',
    'setTemplates',
    'setType',
    'unsetCancel',
    'unsetType'
  ]), {
    cancelCreating () {
      this.showModal = false
      this.resetCreate()
    },
    formatWhiteMsg (str) {
      let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
      let r2 = r1.replace(/\*\*/g, '</span>')

      return r2
    },
    openCreateForm (type) {
      let item = find(this.$store.state.data.managers, { name: type })

      this.setSteps(item.templates ? 3 : 2)
      this.setTemplates(item.templates)
      this.unsetCancel()

      this.showModal = true
    },
    preventCloseIfWorking (e) {
      if (this.isWorking) {
        e.preventDefault()
      }
    },
    sendOutput (out) {
      let json = this.isJson(out)
      let type = 'default'
      let msg = out

      if (json) {
        type = json['type'] ? json.type : type
        msg = json['message'] ? json.message : msg
      }

      // strings between two asterisks will be parsed white
      if (msg.indexOf('**') > -1) {
        msg = this.formatWhiteMsg(msg)
      }

      // a nice blinking cursor
      if (msg === '_CURSOR_') {
        this.output(`<span class="blink">_</span>`)
        return
      }

      this.output(`<span style="color:${consoleColors[type]}">${msg}</span>`)
    },
    setWorking (state) {
      this.isWorking = state
    }
  }),

  watch: {
    '$store.state.type' (type) {
      if (type !== '') {
        this.openCreateForm(type)
      }
    },
    '$store.state.cancel' (state) {
      if (state) {
        this.cancelCreating()
      }
    }
  }
}
</script>
