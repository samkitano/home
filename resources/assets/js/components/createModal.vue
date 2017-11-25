<template>
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
          :options="managers"></b-form-select>
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

        <!-- TODO: values = runtime|standalone -->
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
</template>
