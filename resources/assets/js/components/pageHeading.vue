<template lang="html">
  <b-row>
    <b-col xl class="mt-2">
      <h2>Local Projects <span class="small text-muted">[ {{ location }} ]</span></h2>
    </b-col>
  </b-row>
</template>


<script>
  export default {
    props: {
      location: {
        required: true,
        type: String
      }
    }
  }
</script>
