<style lang="css" scoped>
  .c {
    text-transform: capitalize
  }
</style>

<template lang="html">
  <b-media-body class="ml-3">
    <h4 :class="titleClass(site.description)"
      >{{ site.description ? site.description : 'No description' }}</h4
    >

    <p class="p-0 m-0 c"><strong>Type:</strong> {{ site.type }}</p>

    <p class="p-0 m-0"><strong
      >Url:</strong> <a target="_blank" :href="site.url">{{ site.url }}</a></p
    >
    <p class="p-0 m-0"><strong>Path:</strong> {{ site.path }}</p>

    <p :class="pClass(site.author)"><strong
      >Author:</strong> {{ site.author ? site.author : 'No author' }}</p
    >

    <p :class="pClass(site.version)"><strong>Version:</strong
      > {{ site.version ? site.version : 'No version' }}</p
    >

    <p :class="pClass(site.license)"><strong>License:</strong
      > {{ site.license ? site.license : 'No license' }}</p
    >

    <p class="p-0 m-0" v-if="site.storagePermissions">
      <strong>Storage:</strong> {{ site.storagePermissions }}
      <span v-if="site.storagePermissions === '0755'"
        ><i class="fas fa-check text-success"></i></span
      >
      <span v-else><i class="fas fa-times text-danger"></i
        > <a @click.prevent="fixPermissions(site.path)" title="Set to 0755" href="#">Fix</a></span
      >
    </p>
  </b-media-body>
</template>

<script type="text/javascript">
export default {
  methods: {
    pClass (txt) {
      return txt ? 'p-0 m-0' : 'p-0 m-0 text-muted'
    },
    titleClass (description) {
      return description ? 'mt-0' : 'mt-0 text-muted'
    }
  },

  props: {
    site: {
      required: true,
      type: Object
    }
  }
}
</script>
