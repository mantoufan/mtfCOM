var store = (function () {
  var g = {};
  try {
    g = window.sessionStorage;
  } catch (e) {
    g.__proto__.getItem = function (k) {
      return this[k] || null;
    };
    g.__proto__.setItem = function (k, v) {
      this[k] = v;
    };
    g.__proto__.removeItem = function (k) {
      delete this[k];
    };
  }
  return {
    get: function (k) {
      return JSON.parse(g.getItem(k));
    },
    set: function (k, v) {
      return g.setItem(k, JSON.stringify(v)), this;
    },
    remove: function (k) {
      return g.removeItem(k), this;
    },
  };
})();
