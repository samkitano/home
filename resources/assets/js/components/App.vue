<style>
.form-control-sm.custom-select {
  padding: 0.25rem 0.5rem /* bootstrap select sm fix */
}
</style>


<template>
  <section>
    <b-container>
      <header>
        <v-head :location="location"/>
        <v-create-btn :items="managers"/>
      </header>
      
      <main>
        <v-list :sites="sites"/>
      </main>
    </b-container>

    <v-footer :tools="tools"/>
    <v-create :items="managers" :sites="sites"/>
    <v-info-modal/>
  </section>
</template>


<script>
  import vHead from './pageHeading'
  import vCreateBtn from './createBtn'
  import vCreate from './Create'
  import vList from './List'
  import vFooter from './Footer'
  import vInfoModal from './infoModal'

  export default {
    components: {
      vHead,
      vCreateBtn,
      vCreate,
      vList,
      vFooter,
      vInfoModal
    },

    data () {
      // All initial data was saved as a window object in index.blade.php
      let i = JSON.parse(this.items)

      return {
        location: i.location,
        managers: i.managers,
        sites: i.sites,
        tools: i.tools
      }
    },

    // methods: {
    //   cancelProject () {
    //     this.resetFields()
    //     this.resetStates()

    //     this.output = []
    //     this.showCreateProject = false
    //     this.done = false
    //   },
    //   checkWorking (e) {
    //     if (this.isWorking) {
    //       e.preventDefault()
    //     }
    //   },
    //   createProject (e) {
    //     e.preventDefault()

    //     if (!this.validateProject()) {
    //       return false
    //     }

    //     axios
    //       // firstly, we check if project can be created at all
    //       // TODO: get vue config options from meta
    //       .get(`can-create-project/${this.fields.name}`)
    //       .then((r) => { // then either we start creating project for real...
    //         this.output.push(r.data.message)
    //         this.startCreating()
    //       })
    //       .catch((e) => { // ...or miserably fail
    //         // TODO: send ProjectManagerExceptions to console. Swal other errors
    //         if (e.response.data.message === `Project '${this.fields.name}' already exists!`) {
    //           this.setInvalidProject()
    //         } else {
    //           this.$swal('ERROR', e.response.data.message, 'error')
    //         }
    //       })
    //   },
    //   startCreating () {
    //     let payload = {
    //       _method: 'POST',
    //     }

    //     if (this.verbose) {
    //       payload._verbose = true
    //     }

    //     this.sendOutput('This may take a while! Please Wait...')
    //     this.sendOutput(JSON.stringify({message: 'DO NOT CLOSE THIS MODAL!', type: 'warning'}))
    //     this.sendOutput(JSON.stringify({message: 'WAIT FOR THE [CLOSE] BUTTON TO APPEAR', type: 'warning'}))

    //     Object.assign(payload, this.fields)

    //     axios
    //       .post('/', payload)
    //       .then((r) => {
    //         this.sites.push(r.data.site)
    //         this.done = true
    //         this.sendOutput(' ')
    //       })
    //       .catch((e) => {
    //         this.done = true
    //         this.sendOutput(JSON.stringify({message: e.response.data.message, type: 'error'}))
    //         this.sendOutput(' ')
    //       })
    //   }
    // },

    props: {
      items: {
        required: true,
        type: String
      }
    }
  }
</script>
