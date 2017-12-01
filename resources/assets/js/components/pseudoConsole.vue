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
        /**
         * One of those akward situations where we have to actually
         * slow down program execution on purpose.
         * We need this small timeout in order to allow our console to scroll down.
         * otherwise, output could be so fast that it will not do so.
         */
        setTimeout(() => {
          this.$refs.output.scrollTop = this.$refs.output.scrollHeight
        }, 10)
      }
    }
  }
</script>
