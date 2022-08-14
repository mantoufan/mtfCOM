var mtfUid = {
  get:function () {
    return store.get('mtfuid') || store.set('mtfuid', 'xxxxxxxxxx'.replace(/x/g, function(){
      return Math.floor(Math.random() * 16).toString(16)
    })).get('mtfuid')
  }
}