<template lang="html">
  <div>
    <v-console/>

    <div class="step1" v-show="showSelectTemplate">
      <b-form-group
        description="Pick a Template"
        :feedback="feedbacks.template"
        :state="states.template">
        <b-form-select
          id="template"
          ref="template"
          size="sm"
          v-model="fields.template"
          :state="states.template"
          :options="templates">
          <template slot="first">
            <option :value="false" disabled>-- Please select a template --</option>
          </template>
        </b-form-select>
      </b-form-group>
    </div>

    <div class="step2" v-show="showOptions">
      <div v-if="showOptions">
        <div v-show="!creating && !done">
          <template v-for="(option, i) in templateOptions">
            <p :key="i" class="option-box" v-if="option.type === 'confirm'">
              <b-form-checkbox
                size="sm"
                :id="i"
                v-model="fields[i]"
                :value="true"
                :unchecked-value="false">{{ option.message }}</b-form-checkbox>
            </p>

            <b-form-group
              v-if="option.type === 'list'"
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
              v-if="option.type === 'string'"
              :label="option.label ? option.label : option.message"
              :invalid-feedback="feedbacks[i]"
              :state="states[i]"
              :key="i">
              <b-form-input
                size="sm"
                :id="i"
                :state="states[i]"
                :required="option.required"
                v-model.trim="fields[i]"></b-form-input>
            </b-form-group>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script type="text/javascript">
/* global axios */
import vConsole from './../pseudoConsole'
import forbidden from '../../forbiddenFileNames'
import { find } from 'lodash'
import { mapActions } from 'vuex'

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
      return this.step === this.steps
    },
    showSelectTemplate () {
      return this.steps > 1 && this.step === 1
    },
    step () {
      return this.$store.state.step
    },
    steps () {
      return this.$store.state.steps
    },
    templateOptions () {
      return this.$store.state.templateOptions
    },
    templates () {
      return this.$store.state.templates
    },
    validTemplate () {
      return this.fields.template || this.steps === 1
    }
  },

  data () {
    return {
      feedbacks: this.$store.state.feedbacks,
      fields: this.$store.state.fields,
      forbidden,
      selectOptions: {},
      states: {}
    }
  },

  methods: Object.assign({}, mapActions([
    'clearConsole',
    'finishCreating',
    'updateSites',
    'output',
    'popConsole',
    'setFields',
    'setTemplateOptions',
    'setDone',
    'setError',
    'setTemplate',
    'setValid',
    'setWorking',
    'startCreating',
    'unsetCreating',
    'unsetResetting',
    'unsetWorking'
  ]), {
    changeDescription (i, val) {
      let choice = find(this.templateOptions[i].choices, { value: val })

      this.$set(this.feedbacks, i, choice.name)
    },
    create () {
      this.startCreating()
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
          this.finishCreating(r.data.site)
        })
        .catch((e) => {
          this.manageErrorResponse(e.response)
        })
    },
    fetchOptions () {
      this.setWorking()
      this.output(`Please wait. Getting Options for template ${this.fields.template}...`)

      axios
        .get(`/options/${this.$store.state.type}/${this.fields.template}`)
        .then((r) => {
          this.setTemplate(this.fields.template)
          this.setTemplateOptions(r.data.options)
        })
        .catch((e) => {
          this.manageErrorResponse(e.response)
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
      let obj = this.templateOptions[field]

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
    manageErrorResponse (res) {
      this.setError()
      this.output(`<span style="color:red">${res.data.message}</span>`)
      this.output(' ')
      this.unsetCreating()
      this.unsetWorking()

      this.$swal(
        {
          title: res.data.exception ? res.data.exception : 'ERROR!',
          html: this.formatDataMessage(res.data),
          type: 'error'
        }
      )
    },
    renderOptions () {
      let options = this.templateOptions

      for (let item in options) {
        this.setOptionField(item)

        if (options[item].type === 'list') {
          this.selectOptions[item] = this.getOptionsForItem(options[item].choices)
          this.$set(this.feedbacks, item, options[item].choices[0].name)
        }
      }
      this.setFields(this.fields)
    },
    resetForm () {
      this.fields = {}
      this.states = {}
      this.feedbacks = {}
      this.selectOptions = {}

      for (let f in this.$store.state.fields) {
        this.$set(this.fields, f)
      }

      for (let f in this.$store.state.states) {
        this.$set(this.states, f)
      }

      for (let f in this.$store.state.feedbacks) {
        this.$set(this.feedbacks, f)
      }

      this.unsetResetting()
    },
    resetOptions () {
      this.selectOptions = {}
    },
    setOptionField (fieldName) {
      this.$set(this.fields, fieldName, this.getFieldDefVal(fieldName))
    },
    valiateTemplate () {
      if (!this.fields.template) {
        this.states.template = false
        this.feedbacks.template = 'You must select a template!'
        return false
      }

      this.states.template = true
      this.feedbacks.template = 'Good choice!'
      this.popConsole()
      this.output(this.fields.type + ' Template: <span style="color:white">' + this.fields.template + '</span>')
      this.output('<span style="color:cyan">Hit Next [->] to continue</span>')
      this.output(`<span class="blink">_</span>`)

      return true
    }
  }),

  watch: {
    validTemplate (state) {
      this.setValid(state)
    },
    showOptions (state) {
      if (state && this.fields.template !== this.$store.state.template) {
        this.resetOptions()
        this.popConsole()
        this.fetchOptions()
      }
    },
    // '$store.state.defaultTemplate' (tpl) {
    //   this.fields.template = tpl
    // },
    // '$store.state.type' (type) {
    //   this.fields.type = type
    // },
    '$store.state.creating' (state) {
      if (state) {
        this.create()
      }
    },
    '$store.state.templateOptions' (obj) {
      this.renderOptions()
    },
    '$store.state.resetting' (state) {
      if (state) {
        this.resetForm()
      }
    }
  }
}
</script>
