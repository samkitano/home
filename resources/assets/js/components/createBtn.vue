<template lang="html">
  <b-row>
    <b-col xl class="mb-3">
      <b-dropdown size="sm" class="my-2" variant="primary">
        <template slot="button-content">
          <i class="fa fa-plus"></i> Create Project
        </template>

        <template v-for="(item, i) in items">
          <b-dd-item-button :key="i" @click="setType(item.name)">{{ item.name }}</b-dd-item-button>
        </template>
      </b-dropdown>
    </b-col>
  </b-row>
</template>

<script type="text/javascript">
import { mapActions } from 'vuex'

export default {
  computed: {
    items () {
      return this.$store.state.data.managers
    }
  },

  methods: mapActions([
    'setType'
  ])
}
</script>
