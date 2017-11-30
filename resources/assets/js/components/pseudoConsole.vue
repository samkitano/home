<style lang="css" scoped>
  .output {
    color: green;
    background: black;
    padding: 0 .5em 1.5em .5em;
    max-height: 150px;
    height: 150px;
    overflow-y: auto;
    text-align: left
  }

  .output p {
    margin: 0;
    padding: 0;
    font-size: .8rem
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
        setTimeout(() => {
          this.$refs.output.scrollTop = this.$refs.output.scrollHeight
        }, 10)
      }
    }
  }
</script>
