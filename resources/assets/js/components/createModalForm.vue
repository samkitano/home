<template lang="html">
  <form @submit.stop="createProject">
    <div class="step1" v-show="step === 1">
      <h4 class="text-info text-center">Enter project details</h4>

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
      <h4 class="text-info text-center" v-if="showSelectTemplate">Select Template</h4>

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
          <v-console :output="output"/>
          <h4 class="text-info text-center" v-if="options.length">Options
<!--             <span class="text-center" v-if="fetchingOptions">
              <i class="fa fa-spinner fa-spin fa-fw text-primary"></i>
            </span>
 -->          </h4>

          <template v-for="(option, i) in options">
            <p class="option-box" v-if="!isNative(i) && option.type === 'confirm'">
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
  </form>
</template>


<script type="text/javascript">
  const defaultFields = require('../defaultFields')

  import vConsole from './pseudoConsole'
  import forbidden from '../forbiddenFileNames'
  import { find } from 'lodash'

  export default {
    beforeDestroy () {
      Bus.$off('resetForm', this.resetForm)
      Bus.$off('type', this.setType)
      Bus.$off('working', this.setWorking)
    },

    components: {
      vConsole
    },

    computed: {
      /**
       * When to show options
       * @returns {boolean}
       */
      showOptions () {
        return this.step === 3 || (this.step === 2 && this.maxSteps === 2)
      },
      /**
       * When to show template selection
       * @returns {boolean}
       */
      showSelectTemplate () {
        return this.maxSteps > 2 && this.step === 2
      },
      /**
       * Details validity (name and description)
       * @returns {boolean}
       */
      validDetails () {
        return this.states.name && this.states.description
      },
      /**
       * Template is selected
       * @returns {boolean}
       */
      validTemplate () {
        return this.states.template || this.maxSteps === 2
      }
    },

    created () {
      Bus.$on('resetForm', this.resetForm)
      Bus.$on('type', this.setType)
      Bus.$on('working', this.setWorking)
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
          type: ''
        },
        forbidden,
        isWorking: false,
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

    methods: {
      /**
       * Change field description (stored in 'feedbacks')
       * @param {string} i
       * @param {string} val
       */
      changeDescription (i, val) {
        let choice = find(this.options[i].choices, { value: val })

        this.$set(this.feedbacks, i, choice.name)
      },
      /**
       * Fetch template options from API
       */
      fetchOptions () {
        this.resetOptions()
        Bus.$emit('clearConsole', true)

        let t = this.fields.type
        let tpl = this.fields.template

        this.fetchingOptions = true

        axios
          .get(`/options/${t}/${tpl}`)
          .then((r) => {
            this.options = r.data.options
            this.fetchingOptions = false
            this.renderOptions()
          })
          .catch((e) => {
            console.log(e)
            this.fetchingOptions = false
          })
      },
      /**
       * Get field default value
       * @param {string} field
       * @returns {string|boolean|number}
       */
      getFieldDefVal (field) {
        let obj = this.options[field]

        switch (obj.type) {
          case 'confirm':
            return obj['default'] ? obj.default : false
          case 'list':
            return obj['choices'] ? obj.choices[0].value : false
          case 'string':
            if (this.defaults[field]) {
              return this.defaults[field]
            }

            return obj['default'] ? obj.default : ''
          default:
            return obj['default'] ? obj.default : false
        }
      },
      /**
       * Transform select choices for vue-bootstrap compliance
       * @param {object} choices
       * @returns {array}
       */
      getOptionsForItem (choices) {
        let t = []

        for (let choice in choices) {
          t.push({ text: choices[choice].short, value: choices[choice].value })
        }

        return t
      },
      /**
       * Check if field is set in data hook
       * @param {string} fieldName
       * @returns {boolean}
       */
      hasField (fieldName) {
        return this.fields.hasOwnProperty(fieldName)
      },
      /**
       * Check if fetched template option is a native one,
       * such as name and description
       * @param {string} optionName
       * @returns {boolean}
       */
      isNative (optionName) {
        return this.inArray(optionName, this.nativeOptions)
      },
      /**
       * Process fetched template options
       */
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
      /**
       * Cancels Form.
       * Restores defaults.
       */
      resetForm () {
        Bus.$emit('clearConsole', true)

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
      /**
       * Reset Form Options
       *
       * Needed in case we decide to go wild,
       * picking templates back and forth
       * just for the fun.
       */
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
      /**
       * Set fetched option field in data hook
       * @param {string} fieldName
       */
      setOptionField (fieldName) {
        this.$set(this.fields, fieldName, this.getFieldDefVal(fieldName))
      },
      /**
       * Set current project type
       * @param {string} type
       */
      setType (type) {
        this.fields.type = type
      },
      /**
       * Set isWorking hook
       * @param {boolean} state
       */
      setWorking (state) {
        this.isWorking = state
      },
      /**
       * Project's Name Validation
       * @returns {boolean}
       */
      validateProjectName () {
        let found = find(this.sites, { folder: this.fields.name })

        if (found) {
          this.states.name = false
          this.feedbacks.name = 'Project already exists!'

          return false
        }

        if (!this.fields.name.match(/^[a-zA-Z]\w+$/) 
          || this.inArray(this.fields.name.toUpperCase(), this.forbidden)) {
          this.states.name = false
          this.feedbacks.name = 'Please enter a valid project name!'

          return false
        } else {
          this.states.name = true
          this.feedbacks.name = ''

          return true
        }
      },
      /**
       * Project's Description Validation
       * @returns {boolean}
       */
      validateProjectDescription () {
        if (this.fields.description === '') {
          return true
        }

        if (!this.fields.description.match(/[a-zA-Z0-9 ]\w*$/)
          || this.inArray(this.fields.description, this.forbidden)) {
          this.states.description = false
          this.feedbacks.description = 'Invalid Description!'

          return false
        } else {
          this.states.description = true
          this.feedbacks.description = ''

          return true
        }
      },
      /**
       * Template selection validation
       * @returns {boolean}
       */
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
    },

    props: {
      /**
       * Some defaults for common fields
       * @prop defaults
       * @type {object}
       */
      defaults: {
        required: true,
        type: Object
      },
      maxSteps: {
        required: true,
        type: Number
      },
      output: {
        required: true,
        type: Array
      },
      sites: {
        required: true,
        type: Array
      },
      step: {
        required: true,
        type: Number
      },
      templates: {
        required: true,
        type: Array
      }
    },

    watch: {
      /**
       * Let the whole app know when we are busy doing stuff
       * @param {boolean} state
       */
      fetchingOptions (state) {
        Bus.$emit('working', state)
      },
      /**
       * Emits authorization to change steps
       * @param { Number } step
       */
      step (step) {
        if (this.showSelectTemplate) {
          Bus.$emit('valid', (this.validTemplate && this.validDetails))
        } else {
          Bus.$emit('valid', (this.validDetails))
        }
      },
      /**
       * Emits authorization to change to step 2
       * @param {boolean} state
       */
      validDetails (state) {
        Bus.$emit('valid', state)
      },
      /**
       * Emits authorization to change to step 3
       * @param {boolean} state
       */
      validTemplate(state) {
        Bus.$emit('valid', (state && this.validDetails))
      },
      /**
       * Fetch template options when showOption changes
       * @param {boolean} state
       */
      showOptions(state) {
        if (state) {
          this.fetchOptions()
        }
      }
    }
  }
</script>
