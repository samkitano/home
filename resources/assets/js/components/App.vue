<style lang="css">
  .form-control-sm.custom-select {
    padding: 0.25rem 0.5rem /* bootstrap select sm fix */
  }
  .option-box {
    margin-bottom: 0
  }
  .blink {
    color: white;
    animation: blinker .8s linear infinite
  }
  @keyframes blinker {  
    50% { opacity: 0 }
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
    <v-create :defaults="defaults" :items="managers" :sites="sites"/>
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
        tools: i.tools,
        defaults: i.defaults
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
