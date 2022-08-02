var mtfProgress={
	off: 0,
	start: function(){
		if (this.off) return this.off = 0
		$('<div>').addClass('mtf-progress').css({'width':'0%','position':'fixed','height':'2px','top':0,'background':'#000','opacity':.2,'z-index':7})
		.appendTo($('body')).animate({'width':'90%'}, {
			duration: 1500,
			complete: this.complete
		})
	},
	end: function(){
		$('.mtf-progress').stop().animate({'width':'100%', 'opacity': 0}, {
			complete: this.complete
		})
	},
	complete: function() {
		$(this).remove()
	}
};