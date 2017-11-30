<template lang="html">
  <div>
    <b-btn
      size="sm"
      class="float-right"
      variant="primary"
      :disabled="step !== maxSteps || isWorking"
      v-show="!done"
      @click="emit('create', true)">Create</b-btn>

    <b-btn
      size="sm"
      :disabled="isWorking"
      @click="emit('cancel', true)">{{ done ? 'Close' : 'Cancel' }}</b-btn>
  </div>
</template>


<script type="text/javascript">
  export default {
    beforeDetroy () {
      Bus.$off('working', this.setWorking)
      Bus.$off('done', this.setDone)
    },

    created () {
      Bus.$on('working', this.setWorking)
      Bus.$on('done', this.setDone)
    },

    data () {
      return {
        done: false,
        isWorking: false
      }
    },

    methods: {
      /**
       * Component Emitter
       * @param {string} what
       * @param {boolean|string|number} val
       */
      emit (what, val) {
        Bus.$emit(what, val)
      },
      /**
       * Set done hook
       * @param {boolean} state
       */
      setDone (state) {
        this.done = state
      },
      /**
       * Set isWorking hook
       * @param {boolean} state
       */
      setWorking (state) {
        this.isWorking = state
      }
    },

    props: {
      maxSteps: {
        required: true,
        type: Number
      },
      step: {
        required: true,
        type: Number
      }
    }
  }
</script>
