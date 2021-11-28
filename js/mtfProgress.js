var mtfProgress={
	off:0,
	start:function(){
		if(this.off){
			this.off=0;
			return false;
		}
		var d=$('<div>');
		d.addClass('mtf-progress');
		d.css({'width':'0%','position':'fixed','height':'.3em','top':'0','background':'black','opacity':'.5','z-index':'7'});
		$('body').append(d);
		d.animate({'width':'90%'},{
			duration:1000,
			complete:function(){
				$(this).remove();
			}
		 })
	},
	end:function(){
		$('.mtf-progress').animate({'width':'100%','opacity':'0'},{
			complete:function(){
				$(this).remove();
			}
		 });
	}
};