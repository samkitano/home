<style lang="css" scoped>
  .main {
    padding-top: 80px
  }
  .no-projects {
    height: 78vh;
    display: flex;
    justify-content: center;
    align-items: center
  }
</style>

<template lang="html">
  <main>
    <b-container class="main">
      <b-row>
        <b-col>
          <div class="no-projects" v-if="!sites.length">
            <h1>No Projects Found</h1>
          </div>

          <b-card class="mb-4" v-for="(site, i) in sites" :key="i" >
            <h4 class="text-info" slot="header">
              <i class="far fa-folder"></i> {{ site.folder }}
            </h4>

            <b-media no-body>
              <b-media-aside vertical-align="center">
                <i :class="brand(site.icon, site.type)"></i>
              </b-media-aside>

              <project :site="site"/>
            </b-media>

            <div slot="footer">
              <b-btn
                size="sm"
                variant="outline-secondary"
                v-if="site.composer"
                v-b-modal.info
                @click="fillModal('composer.json', site.composer)">
                composer.json
              </b-btn>

              <b-btn
                size="sm"
                variant="outline-secondary"
                v-if="site.package"
                v-b-modal.info
                @click="fillModal('package.json', site.package)">
                package.json
              </b-btn>

              <b-btn
                size="sm"
                style="float:right"
                variant="danger"
                @click="deleteProject(site.folder)">
                <i class="far fa-trash-alt"></i>
              </b-btn>
            </div>
          </b-card>
        </b-col>
      </b-row>
    </b-container>
  </main>
</template>

<script type="text/javascript">
/* global axios */
import project from './project'
import { mapActions } from 'vuex'

export default {
  components: {
    project
  },

  computed: {
    sites () {
      return this.$store.state.data.sites
    }
  },

  methods: Object.assign({}, mapActions([
    'setInfoModal'
  ]), {
    brand (icon, type) {
      return `${icon} fa-4x ${type}`
    },
    fixPermissions (path) {
      let payload = {
        _method: 'POST',
        path
      }

      axios
        .post('/fix', payload)
        .then((r) => {
          this.$swal('SUCCESS', r.data.message, 'success')
        })
        .catch((e) => {
          this.$swal('ERROR', e.response.data.message, 'error')
        })
    },
    deleteProject (project) {
      this.$swal('Feature not yet implemented')
    },
    fillModal (title, info) {
      this.setInfoModal({ title, info })
    }
  })
}
</script>
