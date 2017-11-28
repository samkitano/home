<style lang="css" scoped>    
  .card {
    background-color: rgba(0, 0, 0, 0.03)
  }

  .card-img-top {
    padding: 1em 0
  }

  .card-text {
    padding: 1em;
    margin-bottom: 0;
    background-color: #FFF
  }

  .card-text, .output {
    font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .8em
  }

  .card-img-top {
      max-height: 80px;
      margin: 0 auto
  }

  .card-header {
    background-color: transparent;
    text-align: center
  }
</style>


<template lang="html">
  <div>
    <b-row>
      <template v-for="(site, i) in sites">
        <b-col :key="i" cols="4">
          <b-card no-body
                  :img-src="getEncodedSvg(site.type)"
                  :img-alt="site.type"
                  img-top
                  class="mb-2">
            <h4 slot="header"><i class="fa fa-folder-o"></i> {{ site.folder }}</h4>
            
            <p class="card-text">
              <span v-if="site.url">
                <strong>Url:</strong> <a target="_blank" :href="site.url">{{ site.url }}</a><br>
              </span>

              <span v-if="site.path">
                <strong>Path:</strong> <span>{{ site.path }}</span><br>
              </span>

              <span v-if="site.storagePermissions">
                <strong>Storage Perms:</strong> <span>{{ site.storagePermissions }}</span>
                <span v-if="site.storagePermissions === '0755'"><i class="fa fa-check text-success"></i></span>
                <span v-else><i class="fa fa-times text-danger"></i> <a @click.prevent="fixPermissions(site.path)" title="Set to 0755" href="#">Fix</a></span><br>
              </span>

              <span v-else>
                <strong>Storage Perms:</strong> <span>N/A</span><br>
              </span>
            </p>

            <b-card-footer>
              <b-dropdown size="sm" v-if="site.composer || site.package">
                <template slot="button-content">{ }</template>

                <b-dd-item-button v-if="site.composer" v-b-modal.info @click="fillModal('composer.json', site.composer)">composer.json</b-dd-item-button>
                <b-dd-item-button v-if="site.package" v-b-modal.info @click="fillModal('package.json', site.package)">package.json</b-dd-item-button>
              </b-dropdown>

              <b-btn size="sm" class="pull-right" variant="danger" @click="deleteProject(site.folder)"><i class="fa fa-trash-o"></i></b-btn>
            </b-card-footer>
          </b-card>
        </b-col>
      </template>
    </b-row>
  </div>
</template>


<script type="text/javascript">
  import svgs from '../svgPaths'

  export default {
    components: {
      svgs
    },

    data () {
      return {
        svgs
      }
    },

    methods: {
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
        // TODO
      },

      getEncodedSvg (el) {
        return `data:image/svg+xml;base64,${svgs[el]}`
      },

      fillModal (title, info) {
        Bus.$emit('populateModal', { title, info })
      }
    },

    props: {
      sites: {
        required: true,
        type: Array
      }
    }
  }
</script>
