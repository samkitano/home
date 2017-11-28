Vue.mixin({
  methods: {
    inArray: (str, arr) => arr.indexOf(str) > -1,
    isJson: (str) => {
      try {
        let j = JSON.parse(str)

        if (j && typeof j === "object") {
          return j
        }
      } catch (e) {}

      return false
    }
  }
})
