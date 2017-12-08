<template lang="html">
  <b-col class="text-center">
    <b-btn
      v-if="!done"
      size="sm"
      :disabled="step === 1 || working"
      :variant="step === 1 ? 'secondary' : 'primary'"
      @click="prevStep">
      <i class="fa fa-arrow-left"></i>
    </b-btn>

    <b-btn
      :variant="variant"
      disabled
      size="sm">{{ infoText }}</b-btn>

    <b-btn
      v-if="step !== steps"
      size="sm"
      :disabled="step === steps || !valid || working"
      :variant="step === steps || !valid ? 'secondary' : 'primary'"
      @click="nextStep">
      <i class="fa fa-arrow-right"></i>
    </b-btn>

    <b-btn
      v-if="step === steps && !done"
      size="sm"
      :disabled="working"
      :variant="working ? 'secondary' : 'primary'"
      @click="setCreating">
      Create
    </b-btn>
  </b-col>
</template>

<script type="text/javascript">
import { mapActions } from 'vuex'

export default {
  computed: {
    description () {
      if (this.step === 1) {
        return 'Enter details'
      }

      if (this.step === 2 && this.steps > 2) {
        return 'Select Template'
      }

      return 'Select Options'
    },
    done () {
      return this.$store.state.done
    },
    infoText () {
      if (this.$store.state.error) {
        return 'ERROR!'
      }
      return this.done
        ? 'Project Created!'
        : `Step ${this.step} of ${this.steps}: ${this.description}`
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
    variant () {
      if (this.$store.state.error) {
        return 'outline-danger'
      }
      return this.valid ? 'outline-success' : 'outline-secondary'
    },
    working () {
      return this.$store.state.working
    }
  },

  methods: mapActions([
    'nextStep',
    'prevStep',
    'setCreating'
  ])
}
</script>
