var mtfLJ={
	is:function(){
		var ua=navigator.userAgent;
		if(ua.match(/QQ\//i)){
			return 'qq';
		}else if(ua.match(/MicroMessenger/i)){
			return 'weixin'
		}
		return false;
	},
	tip:function(tips){
		var is=mtfLJ.is(),s;
		if(tips[is]){
			s=tips[is];
		}else if(is&&tips['all']){
			s=tips['all'];
		}
		if(s){
			var d=document.createElement('div'),f=document.body.firstChild;
			d.innerHTML=s;
			var a=d.getElementsByTagName('A');
			for(var i in a){console.log(a[i]);
				if(a[i].style)a[i].style.color='white';
			}
			d.style.background='red';d.style.color='white';d.style.padding='.1em .5em';d.style.textAlign='center';d.style.fontSize='14px';
			if(typeof d.style.textShadow !== undefined){
				d.style.textShadow='none';	
			}
			document.body.insertBefore(d,f);
		}
	}
}