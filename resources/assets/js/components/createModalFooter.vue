<template lang="html">
  <div>
    <div v-if="!isWorking">
      <b-btn size="sm"
        :disabled="step !== maxSteps"
        class="float-right ml-1"
        variant="primary"
        @click="emit('create', true)">Create</b-btn>

      <b-btn
        size="sm"
        variant="info"
        class="float-right ml-1"
        :disabled="step === maxSteps || !valid"
        @click="emit('next', step)">
        <i class="fa fa-arrow-right"></i>
      </b-btn>

      <b-btn
        size="sm"
        variant="info"
        class="float-right"
        :disabled="step === 1"
        @click="emit('prev', step)">
        <i class="fa fa-arrow-left"></i>
      </b-btn>

      <b-btn
        size="sm"
        v-show="!done"
        @click="emit('cancel', true)">Cancel</b-btn>
    </div>

    <div v-else>
      <b-btn
        size="sm"
        v-show="done"
        @click="emit('cancel', true)">Close</b-btn>
    </div>
  </div>
</template>


<script type="text/javascript">
  export default {
    beforeDetroy () {
      Bus.$off('valid', this.setValid)
      Bus.$off('working', this.setWorking)
      Bus.$off('done', this.setDone)
    },

    created () {
      Bus.$on('valid', this.setValid)
      Bus.$on('working', this.setWorking)
      Bus.$on('done', this.setDone)
    },

    data () {
      return {
        done: false,
        valid: false,
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
       * Set valid hook
       * @param {boolean} state
       */
      setValid (state) {
        this.valid = state
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
