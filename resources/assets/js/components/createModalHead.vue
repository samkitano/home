<style lang="css" scoped>
.steps {
  background-color: #17a2b8;
  color: #fff
}
.middle {
  vertical-align: middle;
}
.loading {
  position: absolute;
  right: 2rem
}
</style>


<template lang="html">
  <b-container fluid>
    <b-row>
      <b-col>
        <h3 class="text-center mb-3">New <span :class="textStyle">{{ projectType }}</span> project</h3>
      </b-col>
    </b-row>

    <b-row>
      <b-col class="text-center">
        <b-btn
          size="sm"
          :disabled="step === 1"
          :variant="step === 1 ? 'secondary' : 'primary'"
          @click="emit('prev', step)">
          <i class="fa fa-arrow-left"></i>
        </b-btn>

        <b-btn
          :variant="valid ? 'outline-success' : 'outline-secondary'"
          disabled
          size="sm">Step {{ step }} of {{ maxSteps }}: {{ description }}</b-btn>

        <b-btn
          size="sm"
          :disabled="step === maxSteps || !valid"
          :variant="step === maxSteps || !valid ? 'secondary' : 'primary'"
          @click="emit('next', step)">
          <i class="fa fa-arrow-right"></i>
        </b-btn>
      </b-col>

      <div class="loading" cols="1">
        <i v-if="isWorking" class="fa middle fa-spinner fa-spin fa-fw"></i>
        <i v-else class="fa middle fa-sun-o"></i>
      </div>
    </b-row>
  </b-container>
</template>

<script type="text/javascript">
  export default {
    beforeDetroy () {
      Bus.$off('valid', this.setValid)
      Bus.$off('working', this.setWorking)
    },

    created () {
      Bus.$on('valid', this.setValid)
      Bus.$on('working', this.setWorking)
    },
    computed: {
      description () {
        if (this.step === 1) {
          return 'Enter details'
        }

        if (this.step === 2 && this.maxSteps > 2) {
          return 'Select Template'
        }

        return 'Options'
      },
      textStyle () {
        switch (this.projectType) {
          case 'Laravel':
            return 'text-danger'
          case 'Vue':
            return 'text-success'
          default:
            return 'text-default'
        }
      }
    },

    data () {
      return {
        isWorking: false,
        valid: false
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
      projectType: {
        required: true,
        type: String
      },
      step: {
        required: true,
        type: Number
      },
      maxSteps: {
        required: true,
        type: Number
      }
    }
  }
</script>
