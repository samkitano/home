<template lang="html">
  <b-modal
    id="project"
    ref="project"
    size="lg"
    v-model="showModal"
    @hide="preventCloseIfCreating"
    @hidden="cancelCreating">

    <div slot="modal-header" class="w-100">
      <v-form-head/>
    </div>

    <v-form-body/>

    <div slot="modal-footer" class="w-100">
      <v-form-footer/>
    </div>
  </b-modal>
</template>

<script type="text/javascript">
/* global Echo */
import vFormHead from './createHead'
import vFormBody from './createForm'
import vFormFooter from './createFooter'

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
    vFormHead,
    vFormBody,
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
    'nextStep',
    'output',
    'prevStep',
    'resetCreate',
    'setSteps',
    'setTemplates',
    'setType',
    'unsetCancel'
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

      this.setSteps(item.templates.length ? 3 : 2)
      this.setTemplates(item.templates)
      this.unsetCancel()

      this.showModal = true
    },
    preventCloseIfCreating (e) {
      if (this.$store.state.creating) {
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
