<template lang="html">
  <b-row>
    <b-col>
      <h3 class="text-center mb-3">
        New <span :class="this.$store.state.type.toLowerCase()">{{ $store.state.type }}</span> project
      </h3>
    </b-col>
  </b-row>
</template>
