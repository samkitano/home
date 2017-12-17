<template lang="html">
  <b-col class="text-center">
    <b-btn
      v-if="!done && !creating"
      size="sm"
      :disabled="step === 1 || working"
      :variant="step === 1 ? 'secondary' : 'primary'"
      @click="prevStep">
      <i class="fas fa-arrow-left"></i>
    </b-btn>

    <b-btn
      :variant="infoVariant"
      disabled
      size="sm">{{ infoText }}</b-btn>

    <b-btn
      v-show="step !== steps"
      size="sm"
      :disabled="step === steps || !valid || working"
      :variant="step === steps || !valid ? 'secondary' : 'primary'"
      @click="nextStep">
      <i class="fas fa-arrow-right"></i>
    </b-btn>

    <b-btn
      v-show="step === steps && (!done && !error)"
      size="sm"
      type="submit"
      :disabled="working"
      :variant="working ? 'secondary' : 'primary'">
      Create
    </b-btn>
  </b-col>
</template>

<script type="text/javascript">
import { mapActions } from 'vuex'

export default {
  computed: {
    creating () {
      return this.$store.state.creating
    },
    description () {
      return this.step === 1 && this.steps > 1 ? 'Select Template' : 'Details'
    },
    done () {
      return this.$store.state.done
    },
    error () {
      return this.$store.state.error
    },
    infoText () {
      if (this.$store.state.error) {
        return 'ERROR!'
      }

      return this.done
        ? 'CREATED!'
        : `Step ${this.step} of ${this.steps}: ${this.description}`
    },
    infoVariant () {
      if (this.error) {
        return 'outline-danger'
      }

      return 'outline-' + (this.valid ? 'success' : 'secondary')
    },
    step () {
      return this.$store.state.step
    },
    steps () {
      return this.$store.state.steps
    },
    projectType () {
      return this.$store.state.type
    },
    valid () {
      return this.$store.state.valid
    },
    working () {
      return this.$store.state.working
    }
  },

  methods: mapActions([
    'nextStep',
    'prevStep'
  ])
}
</script>
