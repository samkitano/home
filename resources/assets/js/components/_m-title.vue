<template lang="html">
  <b-row>
    <b-col>
      <h3 class="text-center mb-3">
        New <span :class="textStyle">{{ $store.state.type }}</span> project
      </h3>
    </b-col>
  </b-row>
</template>

<script type="text/javascript">
export default {
  computed: {
    textStyle () {
      switch (this.$store.state.type) {
        case 'Laravel':
          return 'text-danger'
        case 'Vue':
          return 'text-success'
        default:
          return 'text-default'
      }
    }
  }
}
</script>
