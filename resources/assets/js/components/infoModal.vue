<template lang="html">
  <b-modal
        id="info" 
        ref="info"
        size="lg"
        :title="modalTitle"
        ok-only><pre>{{ modalInfo }}</pre>
  </b-modal>
</template>

<script type="text/javascript">
  export default {
    beforeDestroy () {
      Bus.$off('populateModal', this.fillModal)
    },

    created () {
      Bus.$on('populateModal', this.fillModal)
    },

    data () {
      return {
        modalTitle: '',
        modalInfo: ''
      }
    },

    methods: {
      fillModal (data) {
        this.modalTitle = data.title
        this.modalInfo = data.info
      }
    }
  }
</script>
