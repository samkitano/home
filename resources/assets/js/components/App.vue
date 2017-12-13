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
  .vue {
    color: #4fc08d
  }
  .laravel {
    color: #e74430
  }
  .nuxt {
    color: #35495e
  }
  .inverted {
    transform: rotate(180deg);
  }
</style>

<template>
  <section>
    <top-bar/>
    <projects/>
    <v-footer/>
    <create-modal/>
    <dot-json-modal/>
    <defaults-modal/>
  </section>
</template>

<script>
import topBar from './nav'
import createModal from './modals/Create'
import projects from './allprojects'
import vFooter from './footer'
import dotJsonModal from './modals/DotJson'
import defaultsModal from './modals/Defaults'
import { mapActions } from 'vuex'

export default {
  components: {
    topBar,
    createModal,
    projects,
    vFooter,
    dotJsonModal,
    defaultsModal
  },

  created () {
    this.setData()
  },

  methods: Object.assign({}, mapActions([
    'setProjectsData'
  ]), {
    setData () {
      this.setProjectsData(JSON.parse(this.items))
    }
  }),

  props: {
    items: {
      required: true,
      type: String
    }
  }
}
</script>
