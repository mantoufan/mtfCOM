var mtfHelp={
	run:function(ar,index){
		if(!index){
			index=0;
		}
		if(index<ar.length){
			var a=ar[index],s=a.s,t=a.t,p=(typeof(a.p)==="undefined"?'bottom center':a.p);
			if(index<ar.length-1){
				$(s).click(function help(){
					mtfHelp.run(ar,index+1);
					$(this).unbind('click',help);
				});
			}
			setTimeout(function(){mtfHelp.clear();$(s).notify(mtfLang.get(t)+' X',{position:p, autoHideDelay:3000});},500);
		}
	},
	clear:function(){
		$('.notifyjs-wrapper').remove();
	}
};