<style lang="css" scoped>
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
</style>

<template lang="html">
  <b-container fluid>
    <footer>
        <template v-for="(tool, i) in $store.state.data.tools">
            <span
                :key="i"><a
                  target="_blank"
                  :title="tool.desc"
                  :href="tool.url">{{ tool.name}}</a></span
            >
        </template>
    </footer>
  </b-container>
</template>
