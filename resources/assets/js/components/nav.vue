<template lang="html">
  <header>
    <b-navbar fixed="top" toggleable="md" type="dark" variant="dark">
      <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

      <b-navbar-brand>Local Projects <b-badge variant="secondary">{{ $store.state.data.sites.length }}</b-badge></b-navbar-brand>

      <b-collapse is-nav id="nav_collapse">
        <b-navbar-nav style="border-left:1px solid #444">
          <b-nav-item-dropdown text="Create">
            <template v-for="(item, i) in items">
              <b-dd-item-button
              :key="i"
              @click="setType(item.name)">
                {{ item.name }}
              </b-dd-item-button>
            </template>
          </b-nav-item-dropdown>

          <b-nav-item-dropdown text="Util">
            <template v-for="(tool, i) in $store.state.data.tools">
              <b-dd-item
                :key="i"
                target="_blank"
                :title="tool.desc"
                :href="tool.url">{{ tool.name}}</b-dd-item>
            </template>
          </b-nav-item-dropdown>
        </b-navbar-nav>

        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown no-caret right>
            <template slot="button-content">
                <i class="fas fa-cog"></i>
            </template>

            <b-dd-item-button
              size="sm"
              v-b-modal.defaultsModal>API Defaults</b-dd-item-button>
          </b-nav-item-dropdown>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
  </header>
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
