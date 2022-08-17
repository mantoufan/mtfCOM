/** jquery.yzhanmodal.js 0.0.1 author: Shon Ng Date: 2021-04-23 */
(function ($) {
  var PREFIX = yzhanPrefix || 'yzhan'
  var $modal, $modalHeader, $modalHeaderCloseBtn, $modalBody, $modalIframe

  function initDom() {
    $modal = $('<div>').addClass(PREFIX  + '-modal yzhan-modal'),
    $modalHeader = $('<div>').addClass(PREFIX  + '-modal-header yzhan-modal-header'),
    $modalHeaderTitle = $('<div>').addClass(PREFIX  + '-modal-header-title yzhan-modal-header-title'),
    $modalHeaderCloseBtn = $('<div>').addClass(PREFIX  + '-modal-header-close-btn yzhan-modal-header-close-btn').html('X'),
    $modalBody = $('<div>').addClass(PREFIX  + '-modal-body yzhan-modal-body'),
    $modalIframe = $('<iframe>').addClass(PREFIX  + '-modal-iframe yzhan-modal-iframe')

    // Form the DOM
    $modalHeader.append($modalHeaderCloseBtn).append($modalHeaderTitle)
    $modalBody.append($modalIframe)
    $modal.append($modalHeader).append($modalBody)
    $('body').append($modal)
  }

  function initListener() {
    // show Modal
    $modal.show = function() {
      $(this).show()
    }

    // hide Modal
    $modal.hide = function() {
      $(this).hide()
    }

    // close Modal
    $modal.close = function() {
      $(this).remove()
    }

    // close Modal with Btn
    $modalHeaderCloseBtn.on('click', function() {
      $(this).parent().parent().remove()
    })

    // drag HeaderBar
    $modalHeader.on('mousedown', function(e) {
      var marginLeft = parseInt($modal.css('margin-left')) | 0
      var marginTop = parseInt($modal.css('margin-top')) | 0
      var offsetX = $modal.offset().left - marginLeft
      var offsetY = $modal.offset().top - marginTop
      var startX = e.pageX
      var startY = e.pageY

      $(document).on('mousemove', function(e) {
        var curX = e.pageX
        var curY = e.pageY

        $modal.css({
          left: offsetX + curX - startX,
          top: offsetY + curY - startY
        })
      })

      $(document).on('mouseup', function() {
        $(document).off('mousemove')
      })
    })

  }

  $.extend({[PREFIX + 'Modal']: function (opt) {
    function open (opt) {
      var title = opt.title
      var url = opt.url
      if (title && url) {
        initDom()
        initListener()
  
        $modalHeaderTitle.html(title)
        $modalIframe.attr('src', url)
      }
      return $modal
    }

    return {
      open: open
    }
  }})
  

})(jQuery)