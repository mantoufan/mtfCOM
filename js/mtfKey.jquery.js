var mtfKey = {
	key: "undefinedundefined",
	cha: function(ask, f) {
		var a = prompt(ask);
		if (a) {
			f(a);
		}
	},
	chaAjax:function(ask,a){
		mtfKey.cha(ask,function(s){
			a.data+=('&answer='+s);
			$.ajax(a);
		});	
	}
};