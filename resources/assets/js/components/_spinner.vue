<style lang="css" scoped>
  .loading {
    position: absolute;
    top: 5rem;
    right: 2rem
  }
  .inner {
    position: relative;
    padding: 0
  }
</style>

<template lang="html">
  <div class="loading">
    <transition name="fade">
      <div class="inner" v-show="working">
        <i class="fas fa-circle-notch fa-spin fa-fw fa-2x text-secondary"></i>
      </div>
    </transition>
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
