<template lang="html">
  <b-modal
    id="create"
    ref="create"
    size="lg"
    hide-footer
    hide-header
    @hide="onHide"
    @hidden="onHidden"
    @show="onShow"
    @shown="onShown">
    <b-form
      @reset="onReset"
      @submit="onSubmit">
      <div class="w-100">
        <h3 class="text-center mb-3">
          New <span :class="this.$store.state.type.toLowerCase()">{{ type }}</span> project
        </h3>
      </div>

      <v-console/>

      <div class="Template-selection" v-show="showSelectTemplate">
        <b-form-group description="Pick a Template">
          <b-form-select
            id="template"
            ref="template"
            size="sm"
            :options="templates"
            v-model="fields.template"
            @input="setTemplateOptions">
            <template slot="first">
              <option :value="null" disabled>-- Select a Template --</option>
            </template>
          </b-form-select>
        </b-form-group>
      </div>

      <div class="Template-options" v-show="templateOptions">
        <div v-show="!creating && !done">
          <template v-for="(option, i) in templateOptions">
            <p
              class="option-box"
              :key="i"
              v-if="option.type === 'confirm'">
              <b-form-checkbox
                size="sm"
                :disabled="working"
                :id="i"
                :value="true"
                :unchecked-value="false"
                v-model="fields[i]">
                {{ option.message }}
              </b-form-checkbox>
            </p>

            <b-form-group
              :description="feedbacks[i]"
              :key="i"
              :label="option.message"
              v-if="option.type === 'list'"
              v-show="option.when ? fields[option.when] : true">
              <b-form-select
                size="sm"
                :disabled="working"
                :id="i"
                :options="selectOptions[i]"
                v-model="fields[i]"
                @input="changeDescription(i, $event)"
              ></b-form-select>
            </b-form-group>

            <b-form-group
              v-if="option.type === 'string'"
              :invalid-feedback="feedbacks[i]"
              :key="i"
              :label="getLabel(i)"
              :state="states[i]">
              <b-form-input
                size="sm"
                :disabled="working"
                :id="i"
                :required="option.required"
                :state="states[i]"
                v-model.trim="fields[i]"
              ></b-form-input>
            </b-form-group>
          </template>
        </div>
      </div>

      <b-btn
        type="reset"
        size="sm"
        :block="error || done"
        :disabled="working"
        @click="closeCreateForm">
        {{ done || error ? 'Close' : 'Cancel' }}
      </b-btn>

      <b-btn
        type="submit"
        size="sm"
        class="float-right"
        variant="primary"
        :disabled="working || !hasTemplate"
        v-show="!error && !done">
        Create
      </b-btn>
    </b-form>
  </b-modal>
</template>

<script type="text/javascript">
/* global Echo, Vue */
import vConsole from './../pseudoConsole'
import forbidden from '../../forbiddenFileNames'

import { mapActions } from 'vuex'
import { find } from 'lodash'

export default {
  components: {
    vConsole
  },

  computed: {
    creating () {
      return this.$store.state.creating
    },
    defaults () {
      return this.$store.state.data.defaults
    },
    done () {
      return this.$store.state.done
    },
    error () {
      return this.$store.state.error
    },
    hasTemplate () {
      return (this.showSelectTemplate && this.fields.template) || !this.showSelectTemplate
    },
    sites () {
      return this.$store.state.data.sites
    },
    showSelectTemplate () {
      return this.templates.length > 0
    },
    templateOptions () {
      return this.$store.state.templateOptions
    },
    templates () {
      return this.$store.state.templates
    },
    type () {
      return this.$store.state.type
    },
    working () {
      return this.$store.state.working
    }
  },

  created () {
    Echo
      .channel('console')
      .listen('ConsoleMessageEvent', (e) => {
        if (e.message) {
          this.writeToConsole(e.message)
        }
      })
  },

  data () {
    return {
      feedbacks: {},
      fields: {
        template: null
      },
      forbidden,
      states: {}
    }
  },

  methods: Object.assign({}, mapActions([
    'closeCreateForm',
    'resetTemplateOptions',
    'setTemplateOptions',
    'unsetDone',
    'unsetError',
    'writeToConsole'
  ]), {
    changeDescription (i, val) {
      let choice = find(this.templateOptions[i].choices, { value: val })

      Vue.set(this.feedbacks, i, choice.name)
    },
    isInvalid () {
      /**
       * Check required fields
       * kind of redundant, since html won't let post anyway
       * @TODO: scroll to invalid field
       */
      for (let f in this.templateOptions) {
        if (this.templateOptions[f].required && !this.fields[f]) {
          this.states[f] = false
          this.feedbacks[f] = `${f} is required!`
          return true
        }
      }

      if (!this.fields.name) return false

      let found = find(this.sites, { folder: this.fields.name })

      if (found) {
        this.states.name = false
        this.feedbacks.name = 'Project already exists!'
        return true
      }

      let vName = this.fields.name.match(/^[a-zA-Z]\w+$/)

      if (!vName) {
        this.states.name = false
        this.feedbacks.name = 'Please enter a valid project name: /^[a-zA-Z]\w+$/' // eslint-disable-line no-useless-escape
        return true
      }

      let forb = this.inArray(this.fields.name.toUpperCase(), this.forbidden)

      if (forb) {
        this.states.name = false
        this.feedbacks.name = 'This name is forbidden!'
        return true
      }

      return false
    },
    onHidden (e) {
      this.resetTemplateOptions()
      this.fields = {}
      this.states = {}
      this.feedbacks = {}
      this.unsetError()
      this.unsetDone()
      this.done = false
      Vue.set(this.fields, 'template', null)
    },
    onSubmit (e) {
      e.preventDefault()
      if (this.isInvalid()) {
        return false
      }
    },
    onReset (e) {
      // this.showModal = false
    },
    onHide (e) {
      if (this.creating) {
        e.preventDefault()
      }
    },
    onShow (e) {
      // Vue.set(this.fields, 'template', null)
    },
    onShown () {
      setTimeout(() => {
        this.writeToConsole(`Awaiting ${this.showSelectTemplate ? 'template' : 'data'}`)
        this.writeToConsole('_CURSOR_')
      }, 600)
    },
    selectOptions: (i) => this.$store.state.templateOptions[i],
    getLabel (i) {
      let opt = this.templateOptions[i]
      let required = opt.required ? '<sup> *</sup>' : ''

      return opt.label ? opt.label + required : opt.message + required
    },
    getChoices (i) {
      let choices = this.templateOptions[i].choices
      let t = []

      for (let choice in choices) {
        t.push({ text: choices[choice].short, value: choices[choice].value })
      }

      return t
    },
    getFieldDefVal (field) {
      let obj = this.templateOptions[field]

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
    setData (options) {
      for (let item in options) {
        Vue.set(this.fields, item, this.getFieldDefVal(item))

        if (options[item].type === 'list') {
          this.selectOptions[item] = this.getChoices(item)
          Vue.set(this.feedbacks, item, options[item].choices[0].name)
        }

        if (options[item].required) {
          Vue.set(this.feedbacks, item, '')
          Vue.set(this.states, item, '')
        }
      }

      // let's give it some time for DOM to update
      setTimeout(() => {
        let el = this.$refs.create.$el.getElementsByTagName('input')[0]
        if (el) el.focus()
      }, 200)
    }
  }),

  watch: {
    '$store.state.showCreateModal' (show) {
      show ? this.$refs.create.show() : this.$refs.create.hide()
    },
    '$store.state.templateOptions' (options) {
      if (options) this.setData(options)
    },
    '$store.state.type' (type) {
      if (!this.showSelectTemplate && type) {
        this.setTemplateOptions()
      }
    }
  }
}
</script>
