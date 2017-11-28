<template lang="html">
  <b-modal
    id="project"
    ref="project"
    size="lg"
    v-model="showModal"
    @hidden="cancelCreating">

    <div slot="modal-header" class="w-100">
      <v-form-head :maxSteps="maxSteps" :step="formStep" :projectType="type"/>
    </div>

    <v-form :templates="templates" :maxSteps="maxSteps" :step="formStep" :sites="sites"/>

    <div slot="modal-footer" class="w-100">
      <v-form-footer :maxSteps="maxSteps" :step="formStep"/>
    </div>

    <v-console :output="output"/>
  </b-modal>
</template>


<script type="text/javascript">
  const consoleColors = {
    info: 'green',
    success: 'cyan',
    error: 'red',
    warning: 'yellow'
  }
  
  import vConsole from './pseudoConsole'
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
    },

    components: {
      vConsole,
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
       * @return { Number }
       */
      maxSteps () {
        return this.ntemplates ? 3 : 2
      }
    },

    created () {
      // TODO move listener to console
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
    },

    data () {
      return {
        ntemplates: 0,
        templates: [],
        formStep: 1,
        output: [],
        showModal: false,
        type: '',
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
      },
      /**
       * Format a console message
       *
       * @param { String } str
       * @return { String }
       */
      formatMessage (str) {
        let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
        let r2 = r1.replace(/\*\*/g, '</span>')

        return r2
      },
      /**
       * Move to next step
       *
       * @param { Number } step
       */
      nextStep (step) {
        this.formStep++
      },
      /**
       * Move to previous step
       *
       * @param { Number } step
       */
      prevStep (step) {
        this.formStep--
      },
      /**
       * Send output to console
       *
       * @param { String } out
       */
      sendOutput (out) {
        let json = this.isJson(out)
        let type = 'info'
        let msg = out

        if (json) {
          type = json.hasOwnProperty('type') ? json.type : type
          msg = json.hasOwnProperty('message') ? json.message : msg
        }

        if (msg.indexOf('**' ) > -1) {
          msg = this.formatMessage(msg)
        }

        this.output.push(`<span style="color:${consoleColors[type]}">${msg}</span>`)
      },
      /**
       * Start creating a new project based on Type
       *
       * @param { String } type
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
