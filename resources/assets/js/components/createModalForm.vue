<template lang="html">
  <form @submit.stop="createProject">
    <div class="step1" v-show="step === 1">
      <b-form-group
        description="/^[a-zA-Z]\w+$/"
        label="Name *"
        :feedback="feedbacks.name"
        :state="states.name">
        <b-form-input
          id="name"
          ref="name"
          size="sm"
          autofocus
          :state="states.name"
          v-on:input="validateProjectName"
          v-model.trim="fields.name"></b-form-input>
      </b-form-group>

      <b-form-group
        description="/[a-zA-Z0-9 ]\w*$/"
        label="Description"
        :feedback="feedbacks.description"
        :state="states.description">
        <b-form-input
          id="description"
          ref="description"
          size="sm"
          :state="states.description"
          v-on:input="validateProjectDescription"
          v-model.trim="fields.description"></b-form-input>
      </b-form-group>
    </div>

    <div class="steps2and3" v-show="step > 1">
      <div class="step2" v-show="showSelectTemplate">
        <b-form-group
          description="Pick a Template"
          :feedback="feedbacks.template"
          :state="states.template">
          <b-form-select
            id="template"
            ref="template"
            size="sm"
            v-model="fields.template"
            @input="valiateTemplate"
            :state="states.template"
            :options="templates">
            <template slot="first">
              <option :value="false" disabled>-- Please select a template --</option>
            </template>
          </b-form-select>
        </b-form-group>
      </div>

      <div class="step3" v-show="showOptions">
        <div v-if="showOptions">
          <v-console/>

          <div v-show="!creating && !done">
            <template v-for="(option, i) in options">
              <p :key="i" class="option-box" v-if="!isNative(i) && option.type === 'confirm'">
                <b-form-checkbox
                  size="sm"
                  :id="i"
                  v-model="fields[i]"
                  :value="true"
                  :unchecked-value="false">{{ option.message }}</b-form-checkbox>
              </p>

              <b-form-group
                v-if="!isNative(i) && option.type === 'list'"
                v-show="option.when ? fields[option.when] : true"
                :description="feedbacks[i]"
                :key="i"
                :label="option.message">
                <b-form-select
                  size="sm"
                  :id="i"
                  v-model="fields[i]"
                  :options="selectOptions[i]"
                  @input="changeDescription(i, $event)"></b-form-select>
              </b-form-group>

              <b-form-group
                v-if="!isNative(i) && option.type === 'string'"
                :label="option.label ? option.label : option.message"
                :key=i
                :required="option.required">
                <b-form-input
                  size="sm"
                  :id="i"
                  v-model.trim="fields[i]"></b-form-input>
              </b-form-group>
            </template>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>

<script type="text/javascript">
/* global axios */
import vConsole from './pseudoConsole'
import forbidden from '../forbiddenFileNames'
import { find } from 'lodash'
import { mapActions } from 'vuex'

const defaultFields = require('../defaultFields')

export default {
  components: {
    vConsole
  },

  computed: {
    creating () {
      return this.$store.state.creating
    },
    done () {
      return this.$store.state.done
    },
    showOptions () {
      return this.step === 3 || (this.step === 2 && this.maxSteps === 2)
    },
    showSelectTemplate () {
      return this.steps > 2 && this.step === 2
    },
    step () {
      return this.$store.state.step
    },
    steps () {
      return this.$store.state.steps
    },
    templates () {
      return this.$store.state.templates
    },
    validDetails () {
      return this.states.name && this.states.description
    },
    validTemplate () {
      return this.states.template || this.maxSteps === 2
    }
  },

  data () {
    return {
      feedbacks: {
        description: '',
        name: 'Please enter a valid project name!',
        template: ''
      },
      fetchingOptions: false,
      fields: {
        description: '',
        name: '',
        template: false,
        type: this.$store.state.type
      },
      forbidden,
      nativeOptions: [],
      options: {},
      selectOptions: {},
      states: {
        description: true,
        name: false,
        template: false
      }
    }
  },

  methods: Object.assign({}, mapActions([
    'clearConsole',
    'updateSites',
    'output',
    'popConsole',
    'setCreating',
    'setDone',
    'setError',
    'setValid',
    'setWorking',
    'unsetCreating',
    'unsetWorking'
  ]), {
    changeDescription (i, val) {
      let choice = find(this.options[i].choices, { value: val })

      this.$set(this.feedbacks, i, choice.name)
    },
    create () {
      this.setCreating()
      this.output('<span style="color:cyan">STARTING</span>')

      for (let item in this.fields) {
        this.output(`${item}: ${this.fields[item]}`)
      }

      let payload = {
        _method: 'POST'
      }

      Object.assign(payload, this.fields)

      axios
        .post(`/`, payload)
        .then((r) => {
          this.updateSites(r.data.site)
          this.setDone()
          this.output(' ')
        })
        .catch((e) => {
          this.manageErrorResponse(e.response)
        })
    },
    fetchOptions () {
      this.resetOptions()
      this.clearConsole()

      this.fetchingOptions = true

      axios
        .get(`/options/${this.$store.state.type}/${this.fields.template}`)
        .then((r) => {
          this.options = r.data.options
          this.fetchingOptions = false
          this.renderOptions()
        })
        .catch((e) => {
          this.manageErrorResponse(e.response)
          this.fetchingOptions = false
        })
    },
    formatDataMessage (res) {
      return `<ul style="text-align:left;font-size:.8em;font-family:'SFMono-Regular', Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">
                <li><strong>Message:</strong> ${res.message}</li>
                <li><strong>File:</strong> ${res.file}</li>
                <li><strong>Line:</strong> ${res.line}</li>
              </ul>`
    },
    getFieldDefVal (field) {
      let obj = this.options[field]

      switch (obj.type) {
        case 'confirm':
          return obj['default'] ? obj.default : false
        case 'list':
          return obj['choices'] ? obj.choices[0].value : false
        case 'string':
          if (this.$store.state.data.defaults[field]) {
            return this.$store.state.data.defaults[field]
          }
          return obj['default'] ? obj.default : ''
        default:
          return obj['default'] ? obj.default : false
      }
    },
    getOptionsForItem (choices) {
      let t = []

      for (let choice in choices) {
        t.push({ text: choices[choice].short, value: choices[choice].value })
      }

      return t
    },
    hasField (fieldName) {
      return this.fields.hasOwnProperty(fieldName)
    },
    isNative (optionName) {
      return this.inArray(optionName, this.nativeOptions)
    },
    manageErrorResponse (res) {
      this.unsetWorking()
      this.output(JSON.stringify({ message: res.data.message, type: 'error' }))
      this.output(' ')
      this.unsetCreating()
      this.setError()

      this.$swal(
        {
          title: res.data.exception ? res.data.exception : 'ERROR!',
          html: this.formatDataMessage(res.data),
          type: 'error'
        }
      )
    },
    renderOptions () {
      for (let item in this.options) {
        if (this.hasField(item)) {
          this.nativeOptions.push(item)
          continue
        }

        this.setOptionField(item)

        if (this.options[item].type === 'list') {
          this.selectOptions[item] = this.getOptionsForItem(this.options[item].choices)
          this.$set(this.feedbacks, item, this.options[item].choices[0].name)
        }
      }
    },
    resetForm () {
      for (let f in defaultFields.fields) {
        this.fields[f] = defaultFields.fields[f]
      }

      for (let f in defaultFields.states) {
        this.states[f] = defaultFields.states[f]
      }

      for (let f in defaultFields.feedbacks) {
        this.feedbacks[f] = defaultFields.feedbacks[f]
      }

      this.resetOptions()
    },
    resetOptions () {
      this.nativeOptions = []
      this.selectOptions = {}
      this.options = {}

      for (let f in this.fields) {
        let found = defaultFields.fields.hasOwnProperty(f)

        if (!found && !this.isNative(f)) {
          delete this.fields[f]
        }
      }
    },
    setOptionField (fieldName) {
      this.$set(this.fields, fieldName, this.getFieldDefVal(fieldName))
    },
    validateProjectName () {
      let found = find(this.$store.state.data.sites, { folder: this.fields.name })

      if (found) {
        this.states.name = false
        this.feedbacks.name = 'Project already exists!'

        return false
      }

      let vName = this.fields.name.match(/^[a-zA-Z]\w+$/)
      let forb = this.inArray(this.fields.name.toUpperCase(), this.forbidden)

      if (!vName || forb) {
        this.states.name = false
        this.feedbacks.name = 'Please enter a valid project name!'

        return false
      } else {
        this.states.name = true
        this.feedbacks.name = ''

        return true
      }
    },
    validateProjectDescription () {
      if (this.fields.description === '') {
        return true
      }

      if (!this.fields.description.match(/[a-zA-Z0-9 ]\w*$/)) {
        this.states.description = false
        this.feedbacks.description = 'Invalid Description!'

        return false
      } else {
        this.states.description = true
        this.feedbacks.description = ''

        return true
      }
    },
    valiateTemplate () {
      if (!this.fields.template) {
        this.states.template = false
        this.feedbacks.template = 'You must select a template!'
        return false
      }

      this.states.template = true
      this.feedbacks.template = ''
      return true
    }
  }),

  watch: {
    fetchingOptions (state) {
      state ? this.setWorking() : this.unsetWorking()
    },
    step (step) {
      if (this.showSelectTemplate) {
        this.setValid(this.validTemplate && this.validDetails)
      } else {
        this.setValid(this.validDetails)
      }
    },
    validDetails (state) {
      this.setValid(state)
    },
    validTemplate (state) {
      this.setValid(state && this.validDetails)
    },
    showOptions (state) {
      if (state) {
        this.fetchOptions()
      }
    },
    '$store.state.cancel' (state) {
      if (state) {
        this.resetForm()
      }
    },
    '$store.state.type' (type) {
      this.fields.type = type
    },
    '$store.state.creating' (state) {
      if (state) {
        this.create()
      }
    }
  }
}
</script>
