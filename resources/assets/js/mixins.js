/* global Vue */
Vue.mixin({
  methods: {
    /**
     * Check if array contains element
     *
     * @param   {string} str Needle
     * @param   {array}  arr Haystack
     * @returns {boolean}
     */
    inArray: (str, arr) => arr.indexOf(str) > -1,

    /**
     * Test if string is JSON
     *
     * @param   {string} str The string to test
     * @returns {json|false}
     */
    isJson: (str) => {
      try {
        let j = JSON.parse(str)
        if (j && typeof j === 'object') return j
      } catch (e) {}

      return false
    }
  }
})
