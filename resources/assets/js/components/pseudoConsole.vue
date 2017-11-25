<style lang="css" scoped>
  .output {
    color: green;
    background: black;
    padding: 0 .5em 1.5em .5em;
    max-height: 300px;
    height: 300px;
    overflow-y: auto
  }

  .output p {
    margin: 0;
    padding: 0
  }
</style>


<template lang="html">
  <div ref="output" class="output" v-show="output.length">
    <template v-for="(line, i) in output">
      <p v-html="line" :key="i"></p>
    </template>
  </div>
</template>

<script type="text/javascript">
  export default {
    props: {
      output: {
        required: true,
        type: Array
      }
    },

    watch: {
      output () {
        this.$refs.output.scrollTop = this.$refs.output.scrollHeight
      }
    }
  }
</script>
