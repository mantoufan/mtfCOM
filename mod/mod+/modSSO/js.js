(function(){
	$('body>div').hide()
	var a = MTF.Render.parseStr(window.location.search)
	if(a.r === void 0) return
	MTF.Var.login.t = mtfLang.get(['请','登录'])
	$('.qq-login').trigger('click')
	MTF.Var.login.f = function(i, u) {
		top.location.href = decodeURIComponent(a.r.split('#')[0]) + '#i=' + i + '&uid=' + u
	}
	$("<style></style>").text(".ply-overlay{background:url(https://api.yzhan.cyou/img/bg/?out_type=redirect&des=mtfimssologin&order=rand&theme=fanxiaoxi) no-repeat center;background-size:cover}.ply-layer{opacity:.75}.ply-name{float:left;padding:0 10px;margin:-10px 10px 0 -20px;line-height:44px;font-size:14px;color:white;background-color:#fc89a1;}.ply-back{float:right}").appendTo($("head"))
	$('.ply-header').append($('.ply-cancel').clone().addClass('ply-back').html(mtfLang.get(['返回'])).on('click', function(){history.back()})).prepend($('<div>').html(mtfLang.get(['M站','通行证'])).addClass('ply-name'))
})()