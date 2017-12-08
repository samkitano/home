<style lang="css" scoped>
  .loading {
    position: absolute;
    right: 2rem
  }
</style>

<template lang="html">
  <div class="loading" cols="1">
    <i :class="iClass"></i>
  </div>
</template>

<script type="text/javascript">
export default {
  computed: {
    iClass () {
      return this.$store.state.working
        ? 'fa middle fa-spinner fa-spin fa-fw'
        : 'fa middle fa-sun-o'
    }
  }
}
</script>
