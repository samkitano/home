<style lang="css" scoped>
    .red {
        color: red
    }

    .green {
        color: green
    }

    aside {
        width: 20%
    }

    footer {
        text-align: center;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 1em 0;
        background: rgba(250,250,250, 0.8)
    }

    footer span a {
        color: #636b6f;
        padding: 0 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase
    }

    .card {
        background-color: rgba(0, 0, 0, 0.03);
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
        font-size: .8em;
    }

    .card-img-top {
        max-height: 80px;
        margin: 0 auto
    }

    .card-header {
        background-color: transparent;
        text-align: center
    }

    .output {
        color: green;
        background: black;
        padding: 0 .5em 1.5em .5em;
        max-height: 300px;
        height: 300px;
        overflow-y: auto;
    }

    .output p {
        margin: 0;
        padding: 0
    }
</style>


<template>
    <div>
        <b-container>
            <b-row>
                <b-col xl class="my-4">
                    <h2>Local Projects <span class="small text-muted">[ {{ location }} ]</span></h2>
                </b-col>
            </b-row>

            <b-row>
                <b-col xl class="my-4">
                    <b-btn
                        variant="primary"
                        @click="showCreateProject=true"><i class="fa fa-plus"></i> Create Project</b-btn>
                </b-col>
            </b-row>

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
                                    <span v-if="site.storagePermissions === '0755'"><i class="fa fa-check green"></i></span>
                                    <span v-else><i class="fa fa-times red"></i> <a @click.prevent="fixPermissions(site.path)" title="Set to 0755" href="#">Fix</a></span><br>
                                </span>

                                <span v-else>
                                    <strong>Storage Perms:</strong> <span>N/A</span><br>
                                </span>
                            </p>

                            <b-card-footer>
                                <!-- TODO: dropdown btn for files -->
                                <b-btn size="sm" v-if="site.composer" v-b-modal.composer @click="openComposerModal(site.composer)"><i class="fa fa-file-text-o"></i> composer</b-btn>
                                <b-btn size="sm" v-if="site.package" v-b-modal.package @click="openPackageModal(site.package)"><i class="fa fa-file-text-o"></i> package</b-btn>
                                <b-btn size="sm" class="pull-right" variant="danger" @click="deleteProject(site.folder)"><i class="fa fa-trash-o"></i></b-btn>
                            </b-card-footer>
                        </b-card>
                    </b-col>
                </template>
            </b-row>
        </b-container>

        <b-container fluid>
            <footer>
                <template v-for="(tool, i) in tools">
                    <span
                        :key="i"><a
                            target="_blank"
                            :title="tool.desc"
                            :href="tool.url">{{ tool.name}}</a></span
                    >
                </template>
            </footer>
        </b-container>

        <b-modal
            id="composer" 
            ef="composer"
            size="lg"
            title="composer.json"
            ok-only>
            <pre>{{ composerJson }}</pre>
        </b-modal>

        <b-modal
            id="package"
            ref="package"
            size="lg"
            title="package.json"
            ok-only>
            <pre>{{ packageJson }}</pre>
        </b-modal>

        <b-modal
            id="project"
            ref="project"
            size="lg"
            v-model="showCreateProject"
            @hide="checkWorking"
            @hidden="cancelProject">
            <div slot="modal-header">
                <h3 v-if="!isWorking">Create a new project</h3>
                <h3 v-if="isWorking && !done">Creating a new project <i class="fa fa-refresh fa-spin fa-fw text-primary" aria-hidden="true"></i></h3>
                <h3 v-if="done">Project Created! <i class="fa fa-check text-success" aria-hidden="true"></i></h3>
            </div>

            <form @submit.stop="createProject" v-show="!output.length">
                <b-form-group
                    description="Select Project Type."
                    label="Project Type *"
                    :feedback="feedbacks.type" 
                    :state="states.type"
                >
                    <b-form-select
                        :state="states.type"
                        id="type"
                        v-model="fields.type"
                        :options="projectOptions"></b-form-select>
                </b-form-group>
                
                <b-form-group
                    description="/^[a-zA-Z]\w+$/ Project name will be camelized in .json files."
                    label="Project Name *"
                    :feedback="feedbacks.name" 
                    :state="states.name"
                >
                    <b-form-input
                        id="name"
                        ref="name"
                        autofocus
                        :state="states.name"
                        v-on:input="validateProjectName"
                        v-on:change="checkDirExists"
                        v-model.trim="fields.name"></b-form-input>
                </b-form-group>

                <b-form-group
                    description="/^[a-zA-Z]\w+$/"
                    label="Project Description"
                    :feedback="feedbacks.description" 
                    :state="states.description"
                >
                    <b-form-input
                        id="description"
                        ref="description"
                        :state="states.description"
                        v-on:input="validateProjectDescription"
                        v-model.trim="fields.description"></b-form-input>
                </b-form-group>

                <b-form-checkbox
                    id="run_npm"
                    v-model="fields.runNpm"
                    v-show="fields.type === 'Laravel'"
                    :value="1"
                    :unchecked-value="0">Run npm install</b-form-checkbox>

                <div v-if="fields.type === 'Vue'">
                    <b-form-group
                        description="Select Vue Cli Template."
                        label="Vue Template *"
                        :feedback="feedbacks.template" 
                        :state="states.template"
                    >
                        <b-form-select
                            :state="states.template"
                            id="template"
                            v-model="fields.template"
                            :options="vueTplOptions"></b-form-select>
                    </b-form-group>

                    <div v-if="fields.template === 'webpack'">
                        <b-form-checkbox
                            id="build"
                            v-model="fields.build"
                            :value="1"
                            :unchecked-value="0">Runtime + Compiler?</b-form-checkbox>

                        <b-form-checkbox
                            id="router"
                            v-model="fields.router"
                            :value="1"
                            :unchecked-value="0">Install Vue-Router?</b-form-checkbox>

                        <b-form-checkbox
                            id="eslint"
                            v-model="fields.eslint"
                            :value="1"
                            :unchecked-value="0">Install Eslint?</b-form-checkbox>

                        <b-form-checkbox
                            id="unit"
                            v-model="fields.unit"
                            :value="1"
                            :unchecked-value="0">Unit Tests?</b-form-checkbox>

                        <b-form-checkbox
                            id="e2e"
                            v-model="fields.e2e"
                            :value="1"
                            :unchecked-value="0">E2E Tests?</b-form-checkbox>

                        <b-form-group
                            description="Select Eslint Config."
                            label="Eslint Config *"
                            v-if="fields.eslint"
                            :feedback="feedbacks.eslintConfig" 
                            :state="states.eslintConfig"
                        >
                            <b-form-select
                                :state="states.eslintConfig"
                                id="eslintConfig"
                                v-model="fields.eslintConfig"
                                :options="eslintOptions"></b-form-select>
                        </b-form-group>
                    </div>

                    <div v-if="fields.template === 'webpack-simple'">
                        <b-form-checkbox
                            id="sass"
                            v-model="fields.sass"
                            :value="1"
                            :unchecked-value="0">Use SASS?</b-form-checkbox>
                    </div>
                </div>
            </form>

            <div slot="modal-footer" class="w-100">
                <b-btn size="sm"
                    v-show="!isWorking"
                    class="float-right"
                    variant="primary"
                    @click="createProject">Create</b-btn>

                <b-button
                    :pressed.sync="verbose"
                    v-show="!isWorking"
                    size="sm"
                    class="float-right mr-1"
                    variant="info">{{ verboseText }}</b-button>
                
                <b-btn size="sm"
                    v-show="done"
                    @click="cancelProject">Close</b-btn>

                <b-btn size="sm"
                    v-show="!isWorking"
                    @click="cancelProject">Cancel</b-btn>
            </div>

            <div ref="output" class="output" v-show="output.length">
                <template v-for="(line, i) in output">
                    <p v-html="line" :key="i"></p>
                </template>
            </div>
        </b-modal>
    </div>
</template>


<script>
    const consoleColors = {
        info: 'green',
        success: 'cyan',
        error: 'red',
        warning: 'yellow'
    }

    import svgs from '../svgPaths'
    import forbidden from '../forbiddenFileNames'
    import { find } from 'lodash'

    export default {
        computed: {
            isWorking () {
                return this.output.length > 0
            },

            verboseText () {
                return this.verbose ? 'Verbose' : 'Quiet'
            }
        },

        created () {
            Echo.channel('console')
                .listen('ConsoleMessageEvent', (e) => {
                    if (e.message) {
                        this.sendOutput(e.message)
                    }
                })
        },

        data () {
            let i = JSON.parse(this.items)

            return {
                composerJson: '',
                done: false,
                eslintOptions: [
                    {value: 'standard', text: 'Standard'},
                    {value: 'airbnb', text: 'Airbnb'},
                    {value: 'none', text: 'None'}
                ],
                feedbacks: {
                    e2e: '',
                    eslint: '',
                    eslintConfig: '',
                    description: '',
                    name: '',
                    type: '',
                    router: '',
                    runNpm: '',
                    sass: '',
                    build: '',
                    unit: '',
                    template: ''
                },
                fields: {
                    e2e: 0,
                    eslint: 0,
                    eslintConfig: 'standard',
                    description: '',
                    name: '',
                    type: 'Laravel',
                    router: 1,
                    runNpm: 1,
                    sass: 1,
                    build: 1,
                    unit: 0,
                    template: 'webpack',
                },
                forbidden,
                location: i.location,
                projectOptions: ['Laravel', 'Vue', 'Nuxt', 'Html', 'Empty Project'],
                output: [],
                packageJson: '',
                showCreateProject: false,
                sites: i.sites,
                states: {
                    e2e: '',
                    eslint: '',
                    eslintConfig: '',
                    description: '',
                    name: '',
                    type: true,
                    router: '',
                    runNpm: '',
                    sass: '',
                    build: '',
                    template: '',
                    unit: ''
                },
                svgs,
                tools: i.tools,
                verbose: false,
                vueTplOptions: ['webpack', 'webpack-simple', 'browserify', 'browserify-simple', 'pwa', 'simple']
            }
        },

        methods: {
            cancelProject () {
                this.resetFields()
                this.resetStates()

                this.output = []
                this.showCreateProject = false
                this.done = false
            },

            checkDirExists () {
                let found = find(this.sites, {folder: this.fields.name})

                if (found) {
                    this.setInvalidProject()
                }

                return found
            },

            checkWorking (e) {
                if (this.isWorking) {
                    e.preventDefault()
                }
            },

            createProject (e) {
                e.preventDefault()

                if (!this.validateProject()) {
                    return false
                }

                axios
                    // firstly, we check if project can be created at all
                    .get(`can-create-project/${this.fields.name}`)
                    .then((r) => { // then either we start creating project for real...
                        this.output.push(r.data.message)
                        this.startCreating()
                    })
                    .catch((e) => { // ...or miserably fail
                        if (e.response.data.message === `Project '${this.fields.name}' already exists!`) {
                            this.setInvalidProject()
                        } else {
                            this.$swal('ERROR', e.response.data.message, 'error')
                        }
                    })
            },

            deleteProject (project) {
                // TODO
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

            formatMessage (str) {
                let r1 = str.replace(/ \*\*/g, ' <span style="color:white">')
                let r2 = r1.replace(/\*\*/g, '</span>')

                return r2
            },

            getEncodedSvg (el) {
                return `data:image/svg+xml;base64,${svgs[el]}`
            },

            inArray (str, arr) {
                return arr.indexOf(str) > -1
            },

            openComposerModal (info) {
                this.composerJson = info
            },

            openPackageModal (info) {
                this.packageJson = info
            },

            resetFields () {
                this.fields.e2e = 0
                this.fields.eslint = 0
                this.fields.eslintConfig = 'standard'
                this.fields.description = ''
                this.fields.name = ''
                this.fields.type = 'Laravel'
                this.fields.router = 1
                this.fields.runNpm = 1
                this.fields.sass = 1
                this.fields.build = 1
                this.fields.unit = 0
                this.fields.template = 'webpack'
            },

            resetStates () {
                this.states.e2e = ''
                this.states.eslint = ''
                this.states.eslintConfig = ''
                this.states.type = '',
                this.states.name = ''
                this.states.description = ''
                this.states.template = ''
                this.states.sass = ''
                this.states.build = ''
                this.states.router = ''
                this.states.unit = ''

                this.feedbacks.e2e = ''
                this.feedbacks.eslint = ''
                this.feedbacks.eslintConfig = ''
                this.feedbacks.type = ''
                this.feedbacks.name = ''
                this.feedbacks.description = ''
                this.feedbacks.template = ''
                this.feedbacks.sass = ''
                this.feedbacks.build = ''
                this.feedbacks.router = ''
                this.feedbacks.unit = ''
            },

            sendOutput (out) {
                let json = this.isJson(out)
                let type = 'info'
                let msg = out

                if (json) {
                    type = json.hasOwnProperty('type') ? json.type : type
                    msg = json.hasOwnProperty('message') ? json.message : msg
                }

                if (msg.indexOf('**' ) > -1) {
                    msg = this.formatMessage(msg)
                }

                let color = consoleColors[type]

                this.output.push(`<span style="color:${color}">${msg}</span>`)
            },

            setInvalidProject () {
                this.states.name = false
                this.feedbacks.name = `Project '${this.fields.name}' already exists!`
                this.$refs.name.focus()
            },

            startCreating () {
                let payload = {
                    _method: 'POST',
                }

                if (this.verbose) {
                    payload._verbose = true
                }

                this.sendOutput('This may take a while! Please Wait...')
                this.sendOutput(JSON.stringify({message: 'DO NOT CLOSE THIS MODAL!', type: 'warning'}))
                this.sendOutput(JSON.stringify({message: 'WAIT FOR THE [CLOSE] BUTTON TO APPEAR', type: 'warning'}))

                Object.assign(payload, this.fields)

                axios
                    .post('/', payload)
                    .then((r) => {
                        this.sites.push(r.data.site)
                        this.done = true
                        this.sendOutput(' ')
                    })
                    .catch((e) => {
                        this.done = true
                        this.sendOutput(JSON.stringify({message: e.response.data.message, type: 'error'}))
                        this.sendOutput(' ')
                    })
            },

            isJson (str) {
                try {
                    let j = JSON.parse(str)

                    if (j && typeof j === "object") {
                        return j
                    }
                } catch (e) {}

                return false
            },

            validateProject () {
                if (!this.inArray(this.fields.type, this.projectOptions)) {
                    this.states.type = false
                    this.feedbacks.type = `"${this.type}" is not a valid project type`
                } else {
                    this.states.type = true
                }

                if (this.fields.name === '') {
                    this.states.name = false
                    this.feedbacks.name = 'A project name is required!'
                } else {
                    if (this.validateProjectName()) {
                        this.states.name = true
                    }
                }

                return this.states.type && this.states.name
            },

            validateProjectName () {
                if (this.checkDirExists()) {
                    return false
                }

                if (!this.fields.name.match(/^[a-zA-Z]\w+$/) 
                    || this.inArray(this.fields.name.toUpperCase(), this.forbidden)) {
                    this.states.name = false
                    this.feedbacks.name = 'Invalid Name!'
                    return false
                } else {
                    this.states.name = true
                    this.feedbacks.name = ''
                    return true
                }
            },

            validateProjectDescription () {
                if (!this.fields.description.match(/^[a-zA-Z]\w+$/) || this.inArray(this.fields.description, this.forbidden)) {
                    this.states.description = false
                    this.feedbacks.description = 'Invalid Description!'
                    return false
                } else {
                    this.states.description = true
                    this.feedbacks.description = ''
                    return true
                }
            }
        },

        props: {
            items: {
                required: true,
                type: String
            }
        },

        watch: {
            output () {
                this.$refs.output.scrollTop = this.$refs.output.scrollHeight
            }
        }
    }
</script>
