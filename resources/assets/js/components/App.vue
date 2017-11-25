<template>
  <div>
    <b-container>
      <v-head :location="location"/>
      <v-create :items="managers" :show="!output.length"/>
      <v-list :sites="sites"/>
    </b-container>

    <v-footer :tools="tools"/>
  </div>
</template>


<script>
  
  import vHead from './pageHeading'
  import vCreate from './Create'
  import vList from './List'
  import vFooter from './Footer'

  export default {
    components: {
      vHead,
      vCreate,
      vList,
      vFooter
    },

    computed: {
      isWorking () {
        return this.output.length > 0
      },

      verboseText () {
        return this.verbose ? 'Verbose' : 'Quiet'
      }
    },

    data () {
      let i = JSON.parse(this.items)

      return {
        composerJson: '',
        done: false,
        eslintOptions: [
          {value: 'standard', text: 'Standard'},
          {value: 'airbnb', text: 'Airbnb'},
          {value: 'none', text: 'None'}
        ],
        feedbacks: {
          e2e: '',
          eslint: '',
          eslintConfig: '',
          description: '',
          name: '',
          type: '',
          router: '',
          runNpm: '',
          sass: '',
          build: '',
          unit: '',
          template: ''
        },
        fields: {
          e2e: 0,
          eslint: 0,
          eslintConfig: 'standard',
          description: '',
          name: '',
          type: 'Laravel',
          router: 1,
          runNpm: 1,
          sass: 1,
          build: 1,
          unit: 0,
          template: 'webpack',
        },
        // forbidden,
        location: i.location,
        managers: i.managers,
        projectOptions: ['Laravel', 'Vue', 'Nuxt', 'Html', 'Empty Project'],
        //output: [],
        packageJson: '',
        showCreateProject: false,
        sites: i.sites,
        states: {
          e2e: '',
          eslint: '',
          eslintConfig: '',
          description: '',
          name: '',
          type: true,
          router: '',
          runNpm: '',
          sass: '',
          build: '',
          template: '',
          unit: ''
        },
        tools: i.tools,
        verbose: false,
        vueTplOptions: ['webpack', 'webpack-simple', 'browserify', 'browserify-simple', 'pwa', 'simple']
      }
    },

    methods: {
      cancelProject () {
        this.resetFields()
        this.resetStates()

        this.output = []
        this.showCreateProject = false
        this.done = false
      },


      checkWorking (e) {
        if (this.isWorking) {
          e.preventDefault()
        }
      },

      createProject (e) {
        e.preventDefault()

        if (!this.validateProject()) {
          return false
        }

        axios
          // firstly, we check if project can be created at all
          // TODO: get vue config options from meta
          .get(`can-create-project/${this.fields.name}`)
          .then((r) => { // then either we start creating project for real...
            this.output.push(r.data.message)
            this.startCreating()
          })
          .catch((e) => { // ...or miserably fail
            // TODO: send ProjectManagerExceptions to console. Swal other errors
            if (e.response.data.message === `Project '${this.fields.name}' already exists!`) {
              this.setInvalidProject()
            } else {
              this.$swal('ERROR', e.response.data.message, 'error')
            }
          })
      },


      resetFields () {
        this.fields.e2e = 0
        this.fields.eslint = 0
        this.fields.eslintConfig = 'standard'
        this.fields.description = ''
        this.fields.name = ''
        this.fields.type = 'Laravel'
        this.fields.router = 1
        this.fields.runNpm = 1
        this.fields.sass = 1
        this.fields.build = 1
        this.fields.unit = 0
        this.fields.template = 'webpack'
      },

      resetStates () {
        this.states.e2e = ''
        this.states.eslint = ''
        this.states.eslintConfig = ''
        this.states.type = '',
        this.states.name = ''
        this.states.description = ''
        this.states.template = ''
        this.states.sass = ''
        this.states.build = ''
        this.states.router = ''
        this.states.unit = ''

        this.feedbacks.e2e = ''
        this.feedbacks.eslint = ''
        this.feedbacks.eslintConfig = ''
        this.feedbacks.type = ''
        this.feedbacks.name = ''
        this.feedbacks.description = ''
        this.feedbacks.template = ''
        this.feedbacks.sass = ''
        this.feedbacks.build = ''
        this.feedbacks.router = ''
        this.feedbacks.unit = ''
      },


      startCreating () {
        let payload = {
          _method: 'POST',
        }

        if (this.verbose) {
          payload._verbose = true
        }

        this.sendOutput('This may take a while! Please Wait...')
        this.sendOutput(JSON.stringify({message: 'DO NOT CLOSE THIS MODAL!', type: 'warning'}))
        this.sendOutput(JSON.stringify({message: 'WAIT FOR THE [CLOSE] BUTTON TO APPEAR', type: 'warning'}))

        Object.assign(payload, this.fields)

        axios
          .post('/', payload)
          .then((r) => {
            this.sites.push(r.data.site)
            this.done = true
            this.sendOutput(' ')
          })
          .catch((e) => {
            this.done = true
            this.sendOutput(JSON.stringify({message: e.response.data.message, type: 'error'}))
            this.sendOutput(' ')
          })
      }
    },

    props: {
      items: {
        required: true,
        type: String
      }
    }
  }
</script>
