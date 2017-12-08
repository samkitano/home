<template lang="html">
  <div>
    <b-navbar fixed="top" toggleable="md" type="dark" variant="info">
      <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>
      <b-navbar-brand>Local Projects</b-navbar-brand>
      <b-collapse is-nav id="nav_collapse">
        <b-navbar-nav>
          <b-nav-item-dropdown text="Create">
            <template v-for="(item, i) in items">
              <b-dd-item-button
              :key="i"
              @click="setType(item.name)">
                {{ item.name }}
              </b-dd-item-button>
            </template>
          </b-nav-item-dropdown>
        </b-navbar-nav>

        <!-- Right aligned nav items -->
        <b-navbar-nav class="ml-auto">
          <b-nav-item right href="#"><i class="fa fa-cog"></i></b-nav-item>
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
  </div>
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
