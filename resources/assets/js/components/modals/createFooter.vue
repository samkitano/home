<template lang="html">
  <b-btn
    block
    size="sm"
    :disabled="working"
    @click="cancel">{{ done ? 'Close' : 'Cancel' }}</b-btn>
</template>

<script type="text/javascript">
import { mapActions } from 'vuex'

export default {
  computed: {
    done () {
      return this.$store.state.done
    },
    working () {
      return this.$store.state.working
    }
  },

  methods: mapActions([
    'cancel'
  ])
}
</script>
