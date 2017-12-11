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
    <v-nav/>
    <v-projects/>
    <v-footer/>
    <v-create-modal/>
    <v-info-modal/>
    <v-defaults-modal/>
  </section>
</template>

<script>
import vNav from './nav'
import vCreateModal from './Create'
import vProjects from './Projects'
import vFooter from './Footer'
import vInfoModal from './infoModal'
import vDefaultsModal from './defaultsModal'
import { mapActions } from 'vuex'

export default {
  components: {
    vNav,
    vCreateModal,
    vProjects,
    vFooter,
    vInfoModal,
    vDefaultsModal
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
