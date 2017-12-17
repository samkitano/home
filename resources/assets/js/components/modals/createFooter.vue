<template lang="html">
    <b-btn
      block
      type="reset"
      size="sm"
      :disabled="working">
      {{ done || error ? 'Close' : 'Cancel' }}
    </b-btn>
</template>

<script type="text/javascript">
export default {
  computed: {
    done () {
      return this.$store.state.done
    },
    error () {
      return this.$store.state.error
    },
    working () {
      return this.$store.state.working
    }
  }
}
</script>
