<template lang="html">
  <b-modal
    id="info"
    ref="info"
    size="lg"
    :title="modalTitle"
    ok-only>
    <pre>{{ modalInfo }}</pre>
  </b-modal>
</template>

<script type="text/javascript">
export default {
  data () {
    return {
      modalTitle: '',
      modalInfo: ''
    }
  },

  watch: {
    '$store.state.infoModal' (data) {
      this.modalTitle = data.title
      this.modalInfo = data.info
    }
  }
}
</script>
