var mtfKey = {
	key: "",
	cha: function(ask, f) {
		Ply.dialog("prompt", {
		  title: ask,
		  form: { a: '' }
		}).done(function(ui){
			f(ui.data.a);
		});
	},
	chaAjax:function(ask,a){
		mtfKey.cha(ask,function(s){
			a.data+=('&answer='+s);
			$.ajax(a);
		});	
	}
};