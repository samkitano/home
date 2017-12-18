<style lang="css" scoped>
  .console {
    color: green;
    background: black;
    padding: 0 .5em 1.5em .5em;
    max-height: 150px;
    height: 150px;
    overflow-y: auto;
    text-align: left
  }
  .console p {
    margin: 0;
    padding: 0;
    font-size: .8rem
  }
</style>

<template lang="html">
  <div ref="console" class="console my-2">
    <spinner/>
    <template v-for="(line, i) in console">
      <p v-html="line" :key="i"></p>
    </template>
  </div>
</template>

<script type="text/javascript">
import spinner from './_spinner'

export default {
  components: {
    spinner
  },

  computed: {
    console () {
      return this.$store.state.console
    }
  },

  watch: {
    console () {
      /**
       * One of those akward situations where we have to actually
       * slow down program execution on purpose.
       * We need this small timeout in order to allow our console to scroll down.
       * otherwise, console could be so fast that it will not do so.
       */
      setTimeout(() => {
        this.$refs.console.scrollTop = this.$refs.console.scrollHeight
      }, 10)
    }
  }
}
</script>
