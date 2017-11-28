<template lang="html">
  <div>
    <h3 class="text-center">New <span :class="textStyle">{{ projectType }}</span> project</h3>
    <h4 class="text-center">Step {{ step }} of {{ maxSteps }}</h4>
  </div>
</template>

<script type="text/javascript">
  export default {
    computed: {
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
