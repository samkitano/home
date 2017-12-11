<style lang="css" scoped>
  .vue {
    color: #4fc08d
  }
  .laravel {
    color: #e74430
  }
  .c {
    text-transform: capitalize
  }
  .main {
    padding-top: 80px
  }
</style>

<template lang="html">
  <main>
    <b-container class="main">
      <b-row>
        <b-col>
        <b-card class="mb-4" v-for="(site, i) in sites" :key="i" >
          <h4 class="text-info" slot="header"><i class="far fa-folder"></i> {{ site.folder }}</h4>

          <b-media no-body>
            <b-media-aside vertical-align="center">
              <i :class="brand(site.icon, site.type)"></i>
            </b-media-aside>

            <b-media-body class="ml-3">
              <h4 :class="titleClass(site.description)">{{ site.description ? site.description : 'No description' }}</h4>
              <p class="p-0 m-0 c"><strong>Type:</strong> {{ site.type }}</p>
              <p class="p-0 m-0"><strong>Url:</strong> <a target="_blank" :href="site.url">{{ site.url }}</a></p>
              <p class="p-0 m-0"><strong>Path:</strong> {{ site.path }}</p>
              <p :class="pClass(site.author)"><strong>Author:</strong> {{ site.author ? site.author : 'No author' }}</p>
              <p :class="pClass(site.version)"><strong>Version:</strong> {{ site.version ? site.version : 'No version' }}</p>
              <p :class="pClass(site.license)"><strong>License:</strong> {{ site.license ? site.license : 'No license' }}</p>
              <p class="p-0 m-0" v-if="site.storagePermissions">
                <strong>Storage:</strong> {{ site.storagePermissions }}
                <span v-if="site.storagePermissions === '0755'"><i class="fas fa-check text-success"></i></span>
                <span v-else><i class="fas fa-times text-danger"></i> <a @click.prevent="fixPermissions(site.path)" title="Set to 0755" href="#">Fix</a></span>
                </p>
            </b-media-body>
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

import { mapActions } from 'vuex'

export default {
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
    },
    pClass (txt) {
      return txt ? 'p-0 m-0' : 'p-0 m-0 text-muted'
    },
    titleClass (description) {
      return description ? 'mt-0' : 'mt-0 text-muted'
    }
  })
}
</script>
