<template lang="html">
  <b-modal
    id="project"
    ref="project"
    size="lg"
    v-model="showModal"
    @hide="preventCloseIfWorking"
    @hidden="cancelCreating">

    <div slot="modal-header" class="w-100">
      <v-form-head :maxSteps="maxSteps" :step="formStep" :projectType="type"/>
    </div>

    <v-form
      :defaults="defaults"
      :templates="templates"
      :maxSteps="maxSteps"
      :step="formStep"
      :output="output"
      :sites="sites"/>

    <div slot="modal-footer" class="w-100">
      <v-form-footer :maxSteps="maxSteps" :step="formStep"/>
    </div>
  </b-modal>
</template>


<script type="text/javascript">
  const consoleColors = {
    info: 'cyan',
    error: 'red',
    warning: 'yellow',
    default: 'green'
  }

  import vForm from './createModalForm'
  import vFormHead from './createModalHead'
  import vFormFooter from './createModalFooter'
  import { find } from 'lodash'

  export default {
    beforeDestroy () {
      Bus.$off('type', this.startCreating)
      Bus.$off('cancel', this.cancelCreating)
      Bus.$off('next', this.nextStep)
      Bus.$off('prev', this.prevStep)
      Bus.$off('working', this.setWorking)
      Bus.$off('clearConsole', this.clearConsole)
    },

    components: {
      vForm,
      vFormHead,
      vFormFooter
    },

    computed: {
      /**
       * Compute necessary steps to complete creation process.
       * Up to 3: Details, Template and Options
       * Details: required for all projects
       * Templete: required for some projects
       * Options: includes option to set
       * console verbosity. Depends on type/template choice.
       *
       * @returns {number}
       */
      maxSteps () {
        return this.ntemplates ? 3 : 2
      }
    },

    created () {
      Echo
        .channel('console')
        .listen('ConsoleMessageEvent', (e) => {
          if (e.message) {
            this.sendOutput(e.message)
          }
        })

      Bus.$on('type', this.startCreating)
      Bus.$on('cancel', this.cancelCreating)
      Bus.$on('next', this.nextStep)
      Bus.$on('prev', this.prevStep)
      Bus.$on('working', this.setWorking)
      Bus.$on('clearConsole', this.clearConsole)
    },

    data () {
      return {
        ntemplates: 0,
        templates: [],
        formStep: 1,
        isWorking: false,
        output: [],
        showModal: false,
        type: ''
      }
    },

    methods: {
      /**
       * Close and Reset modal
       */
      cancelCreating () {
        Bus.$emit('resetForm', true)
        this.showModal = false
        this.type = ''
        this.formStep = 1
        this.clearConsole()
      },
      /**
       * Clear pseudo-console
       */
      clearConsole () {
        this.output = []
      },
      /**
       * Format a console message
       * @param {string} str
       * @returns {string}
       */
      formatWhiteMsg (str) {
        let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
        let r2 = r1.replace(/\*\*/g, '</span>')

        return r2
      },
      /**
       * Move to next step
       * @param {number} step
       */
      nextStep (step) {
        this.formStep++
      },
      /**
       * Move to previous step
       * @param {number} step
       */
      prevStep (step) {
        this.formStep--
      },
      /**
       * Prevents modal from closing
       * if app is working
       * @param {object} e
       */
      preventCloseIfWorking (e) {
        if (this.isWorking) {
          e.preventDefault()
        }
      },
      /**
       * Send output to console
       * @param {string} out
       */
      sendOutput (out) {
        let json = this.isJson(out)
        let type = 'default'
        let msg = out

        if (json) {
          type = json['type'] ? json.type : type
          msg = json['message'] ? json.message : msg
        }

        // strings between two asterisks will be parsed white
        if (msg.indexOf('**' ) > -1) {
          msg = this.formatWhiteMsg(msg)
        }

        // a nice blinking cursor
        if (msg === '_CURSOR_') {
          this.output.push(`<span class="blink">_</span>`)
          return
        }

        this.output.push(`<span style="color:${consoleColors[type]}">${msg}</span>`)
      },
      /**
       * Set isWorking hook
       * @param {boolean} state
       */
      setWorking (state) {
        this.isWorking = state
      },
      /**
       * Start creating a new project based on Type
       * @param {string} type
       */
      startCreating (type) {
        let item = find(this.items, { name: type })

        this.type = type
        this.templates = item.templates
        this.ntemplates = item.templates.length
        this.showModal = true
      }
    },

    props: {
      defaults: {
        required: true,
        type: Object
      },
      items: {
        required: true,
        type: Array
      },
      sites: {
        required: true,
        type: Array
      },
      show: {
        type: Boolean,
        default: true
      }
    }
  }
</script>
