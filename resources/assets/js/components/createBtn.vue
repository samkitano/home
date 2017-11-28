<template lang="html">
  <b-row>
    <b-col xl class="mb-3">
      <b-dropdown size="sm" class="my-2" variant="primary">
        <template slot="button-content">
          <i class="fa fa-plus"></i> Create Project
        </template>

        <template v-for="(item, index) in items">
          <b-dd-item-button @click="emit(item.name)">{{ item.name }}</b-dd-item-button>
        </template>
      </b-dropdown>
    </b-col>
  </b-row>
</template>


<script type="text/javascript">
  export default {
    methods: {
      emit (t) {
        Bus.$emit('type', t)
      }
    },

    props: {
      items: {
        required: true,
        type: Array
      }
    }
  }
</script>
