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
        <v-head/>
        <v-create-btn/>
      </header>

      <main>
        <v-list/>
      </main>
    </b-container>

    <v-footer/>
    <v-create/>
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
import { mapActions } from 'vuex'

export default {
  components: {
    vHead,
    vCreateBtn,
    vCreate,
    vList,
    vFooter,
    vInfoModal
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
