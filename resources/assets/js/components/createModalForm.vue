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
      <h4
        class="text-info text-center"
        >{{ showSelectTemplate ? 'Select Template' : 'Choose Options' }}</h4
      >
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
        <div class="text-center" v-if="fetchingOptions">
          <i class="fa fa-2x fa-refresh fa-spin fa-fw text-primary"></i>
        </div>

        <div v-else>
          <template v-for="(option, i) in options">
            <p v-if="!isNative(i) && option.type === 'confirm'"><b-form-checkbox
              :id="i"
              stacked
              v-model="fields[i]"
              :value="true"
              :unchecked-value="false">{{ option.message }}
            </b-form-checkbox></p>

            <b-form-group
              v-if="!isNative(i) && option.type === 'list'"
              v-show="option.when ? fields[option.when] : true"
              :description="feedbacks[i]"
              :label="option.message">
              <b-form-select
                :id="i"
                v-model="fields[i]"
                :options="selectOptions[i]"
                @input="changeDescription(i, $event)">
              </b-form-select>
            </b-form-group>

            <b-form-group
              v-if="!isNative(i) && option.type === 'string'"
              :label="option.label ? option.label : option.message">
              <b-form-input
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
  const defaults = require('../defaultFields')

  import forbidden from '../forbiddenFileNames'
  import { find } from 'lodash'

  export default {
    beforeDestroy () {
      Bus.$off('resetForm', this.resetForm)
      Bus.$off('type', this.setType)
    },

    computed: {
      showOptions () {
        return this.step === 3 || (this.step === 2 && this.maxSteps === 2)
      },
      showSelectTemplate () {
        return this.maxSteps > 2 && this.step === 2
      },
      validDetails () {
        return this.states.name && this.states.description
      },
      validTemplate () {
        return this.states.template || this.maxSteps === 2
      }
    },

    created () {
      Bus.$on('resetForm', this.resetForm)
      Bus.$on('type', this.setType)
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
      changeDescription (i, val) {
        let choice = find(this.options[i].choices, { value: val })

        this.$set(this.feedbacks, i, choice.name)
      },
      fetchOptions () {
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
      getFieldDefVal (obj) {
        switch (obj.type) {
          case 'confirm':
            return obj.hasOwnProperty('default') ? obj.default : false
          case 'list':
            return obj.hasOwnProperty('choices') ? obj.choices[0].value : false
          case 'string':
            return obj.hasOwnProperty('default') ? obj.default : ''
          default:
            return obj.hasOwnProperty('default') ? obj.default : false
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
      isNative (option) {
        return this.inArray(option, this.nativeOptions)
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
        for (let f in defaults.fields) {
          this.fields[f] = defaults.fields[f]
        }

        for (let f in defaults.states) {
          this.states[f] = defaults.states[f]
        }

        for (let f in defaults.feedbacks) {
          this.feedbacks[f] = defaults.feedbacks[f]
        }

        for (let f in this.fields) {
          let found = find(f, defaults.fields)

          if (!found && !this.isNative(f)) {
            delete this.fields[f]
          }
        }

        this.options = {}
        this.nativeOptions = []
        this.selectOptions = {}
      },
      setOptionField (field) {
        this.$set(this.fields, field, this.getFieldDefVal(this.options[field]))
      },
      setType (type) {
        this.fields.type = type
      },
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
      maxSteps: {
        required: true,
        type: Number
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
      step (step) {
        if (this.showSelectTemplate) {
          Bus.$emit('valid', (this.validTemplate && this.validDetails))
        } else {
          Bus.$emit('valid', (this.validDetails))
        }
      },
      validDetails (state) {
        Bus.$emit('valid', state)
      },
      validTemplate(state) {
        Bus.$emit('valid', (state && this.validDetails))
      },
      showOptions(state) {
        if (state) {
          this.fetchOptions()
        }
      }
    }
  }
</script>
