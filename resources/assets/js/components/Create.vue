<template lang="html">
  <div>
    <v-create-btn :items="items"/>

    <!-- <v-create-modal/> -->
    <b-modal
      id="project"
      ref="project"
      size="lg"
      v-model="showModal"
      @hidden="cancelProject">
      <div slot="modal-header">
        <h3 v-if="!isWorking">Create a new {{ fields.type }} project</h3>
      </div>

      <form @submit.stop="createProject">
        <b-form-group
          description="/^[a-zA-Z]\w+$/ Project name will be camelized in .json files."
          label="Project Name *"
          :feedback="feedbacks.name" 
          :state="states.name"
        >
          <b-form-input
            id="name"
            ref="name"
            autofocus
            :state="states.name"
            v-on:input="validateProjectName"
            v-on:change="checkDirExists"
            v-model.trim="fields.name"></b-form-input>
        </b-form-group>

        <b-form-group
          description="/^[a-zA-Z]\w+$/"
          label="Project Description"
          :feedback="feedbacks.description" 
          :state="states.description"
        >
          <b-form-input
            id="description"
            ref="description"
            :state="states.description"
            v-on:input="validateProjectDescription"
            v-model.trim="fields.description"></b-form-input>
        </b-form-group>
      </form>

      <v-console :output="output"/>
    </b-modal>
  </div>
</template>


<script type="text/javascript">
  const consoleColors = {
    info: 'green',
    success: 'cyan',
    error: 'red',
    warning: 'yellow'
  }
  
  import forbidden from '../forbiddenFileNames'
  import vConsole from './pseudoConsole'
  import vCreateBtn from './createBtn'
  import { find } from 'lodash'

  export default {
    beforeDestroy () {
      Bus.$off('type', this.startProject)
    },

    components: {
      vConsole,
      vCreateBtn
    },

    created () {
      Echo
        .channel('console')
        .listen('ConsoleMessageEvent', (e) => {
          if (e.message) {
            this.sendOutput(e.message)
          }
        })

      Bus.$on('type', this.startProject)
    },

    data () {
      return {
        feedbacks: {
          description: '',
          name: '',
        },
        fields: {
          description: '',
          name: '',
          type: ''
        },
        forbidden,
        output: [],
        showModal: false,
        states: {
          description: '',
          name: '',
        }
      }
    },

    methods: {
      cancelProject () {
        this.showModal = false
      },
      checkDirExists () {
        let found = find(this.sites, { folder: this.fields.name })

        if (found) {
          this.setInvalidProject()
        }

        return found
      },
      formatMessage (str) {
        let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
        let r2 = r1.replace(/\*\*/g, '</span>')

        return r2
      },
      inArray (str, arr) {
        return arr.indexOf(str) > -1
      },
      isJson (str) {
        try {
          let j = JSON.parse(str)

          if (j && typeof j === "object") {
            return j
          }
        } catch (e) {}

        return false
      },
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
      setInvalidProject () {
        this.states.name = false
        this.feedbacks.name = `Project '${this.fields.name}' already exists!`
        this.$refs.name.focus()
      },
      startProject (t) {
        this.fields.type = t
        this.showModal = true
      },
      validateProject () {
        if (this.fields.name === '') {
          this.states.name = false
          this.feedbacks.name = 'A name is required!'
        } else {
          if (this.validateProjectName()) {
            this.states.name = true
          }
        }

        return this.states.name
      },
      validateProjectName () {
        if (this.checkDirExists()) {
          return false
        }

        if (!this.fields.name.match(/^[a-zA-Z]\w+$/) 
          || this.inArray(this.fields.name.toUpperCase(), this.forbidden)) {
          this.states.name = false
          this.feedbacks.name = 'Invalid Name!'
          return false
        } else {
          this.states.name = true
          this.feedbacks.name = ''
          return true
        }
      },
      validateProjectDescription () {
        if (!this.fields.description.match(/^[a-zA-Z]\w+$/) || this.inArray(this.fields.description, this.forbidden)) {
          this.states.description = false
          this.feedbacks.description = 'Invalid Description!'
          return false
        } else {
          this.states.description = true
          this.feedbacks.description = ''
          return true
        }
      }
    },

    props: {
        items: {
            required: true,
            type: Object
        },
        show: {
            type: Boolean,
            default: true
        }
    }
  }
</script>
