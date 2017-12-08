<style lang="css" scoped>
  .steps {
    background-color: #17a2b8;
    color: #fff
  }
  .middle {
    vertical-align: middle;
  }
</style>

<template lang="html">
  <b-container fluid>
    <m-title/>

    <b-row>
      <m-buttons/>
      <spinner/>
    </b-row>
  </b-container>
</template>

<script type="text/javascript">
import mButtons from './_m-buttons'
import mTitle from './_m-title'
import spinner from './_loading'

export default {
  components: {
    mButtons,
    mTitle,
    spinner
  }
}
</script>
