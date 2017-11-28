<template lang="html">
  <div>
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

<!--     <b-btn
      :pressed.sync="verbose"
      size="sm"
      class="float-right mr-1"
      variant="info">{{ verboseText }}</b-btn>
 -->
    <!-- <b-btn size="sm"
      v-show="done"
      @click="cancelProject">Close</b-btn> -->

    <b-btn
      size="sm"
      @click="emit('cancel', true)">Cancel</b-btn>
  </div>
</template>


<script type="text/javascript">
  export default {
    beforeDetroy () {
      Bus.$off('valid', this.setValid)
    },

    computed: {
      isWorking () {
        return this.output.length > 0
      },
      verboseText () {
        return this.verbose ? 'Verbose' : 'Quiet'
      }
    },

    created () {
      Bus.$on('valid', this.setValid)
    },

    data () {
      return {
        done: false,
        valid: false,
        verbose: true
      }
    },

    methods: {
      emit (what, val) {
        Bus.$emit(what, val)
      },
      setValid (val) {
        this.valid = val
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
