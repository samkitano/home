<style lang="css" scoped>
  .steps {
    background-color: #17a2b8;
    color: #fff
  }
</style>

<template lang="html">
  <div class="w-100">
    <m-title/>
    <m-buttons/>
    <spinner/>
  </div>
</template>

<script type="text/javascript">
import mButtons from './_m-buttons'
import mTitle from './_m-title'
import spinner from './_spinner'

export default {
  components: {
    mButtons,
    mTitle,
    spinner
  }
}
</script>
