# yzhanModal
A jQuery plugin to show a modal dialog with support for iframes, supporting the Drag and Close.
# Demo
## Popup with m.baidu.com using iframe
- URL  
[Webpage](https://mantoufan.github.io/yzhanModal/demo.html)
- Captrue
![Demo](https://i.loli.net/2021/04/23/yLZ8nj7T2HbSwkX.png)

# Feature
- The jQuery Plugin only provide the basic feature of Modal
- You define your own PREFIX, feel free to change DOM and CSS
![Code](https://i.loli.net/2021/04/23/QHFW4KDjuXnCqpT.png)

# Start
```javascript
<script src="https://code.jquery.com/jquery-1.12.4.min.js" crossorigin="anonymous"></script>
<script>var yzhanPrefix = 'js' // Define your own prefix</script>
<script src="jquery.yzhanmodal.js"></script>
<script>
  const jsModal = $.jsModal()
  jsModal.open({
    title: 'yzhanModal Demo: Open m.baidu.com with iframe',
    url: 'http://m.baidu.com'
  })
</script>
```