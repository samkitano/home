<style lang="css" scoped>
  .loading {
    position: absolute;
    right: 2rem
  }
</style>

<template lang="html">
  <div class="loading" v-show="working">
    <i class="fas fa-circle-notch fa-spin fa-fw"></i>
  </div>
</template>

<script type="text/javascript">
export default {
  computed: {
    working () {
      return this.$store.state.working
    }
  }
}
</script>
