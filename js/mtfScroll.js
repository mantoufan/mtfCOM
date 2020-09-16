var mtfScroll={
	wrap:window,
	dt:'',
	init:function()
	{
		var a=['list','current','last','index','fc','lo','range'];
		for(var i in a){
			var d=[];
			if(a[i]==='last'||a[i]==='index'){
				d=0;
			}
			mtfScroll[a[i]]={'Top':d,'Left':d};
		}
	},
	sortNumber:function(a,b)
	{
		return a - b
	},
	add:function(list,direction,fc)
	{
		/*
			list=array();elements
			direction=Left,Top
			fc=function(){}
		*/
		var list=mtfScroll.list[direction].concat(list),l=list.length,a=[],k=[],z=0;mtfScroll.fc[direction]=fc;mtfScroll.list[direction]=list;
		for (var i = 0; i < l; i++) {
			var o=list[i],t=o['offset'+direction];
			if(typeof(a[t])==="undefined"){
				a[t]=[];
			}
			a[t].push(o);
			k.push(t);
		}
		k.sort(mtfScroll.sortNumber);
		l=k.length;
		for (var i = 0; i < l; i++) {
			var ii=k[i],ar=a[ii],ll=ar.length;
			for (var j = 0; j < ll; j++) {
				var o=ar[j];
				o.setAttribute('i', z);
				z++;
			}
		}
	},
	check:function(a,b)
	{
		return b.x > a.x - b.w && b.x < a.x + a.w + b.w && b.y > a.y - b.h && b.y < a.y + a.h + b.h;
	},
	loop:function(i,direction,view,dr,dt,t){
		var r=false,l=mtfScroll.list[direction],o=l[i],z=l.length;
		
		if(o){
			var rect={x:o.offsetLeft,y:o.offsetTop,w:o.offsetWidth,h:o.offsetHeight},d;
			if(mtfScroll.check(rect,view)){
				//mtfScroll.is[direction]=1;
				mtfScroll.index[direction]=i;
				if(direction==='Top')
				{
					d=(view.y-rect.y)/rect.h*100;	
				}else{
					d=(view.x-rect.x)/rect.w*100;
				}

				mtfScroll.current[direction].push({'o':o,'d':d});
				
				//if(i===l.length-1){
					r=true;
				//}	
			}
			/*
			else{
				if(mtfScroll.is[direction]===1){
					r=true;	
				}
				mtfScroll.is[direction]=0;
			}
			*/
			var p=[],c=false,next='';
			if(r===true){
				p=mtfScroll.current[direction];
				if(mtfScroll.lo[direction]!==i){
					mtfScroll.lo[direction]=i;
					c=true;
				}
			}
			
			if(dr){//↓
				if(i<z-1){
					next=l[i+1];
				}
			}else{//↑
				if(i>1){
					next=l[i-1];
				}
			}
			mtfScroll.fc[direction](p,c,next,dr,dt,t);
		}
		
		return r;
	},
	listen:function(view,direction)
	{
		/*
			view={x:'',y:'',w:'',h:''}
			direction:Left,Top
		*/
		var w=$(mtfScroll.wrap),l=mtfScroll.list[direction].length,s=mtfScroll.last[direction],a=mtfScroll.index[direction],r=mtfScroll.range[direction],dt,ds,t,dr;
		
		//mtfScroll.is[direction].length=0;
		mtfScroll.current[direction].length=0;
		if(direction==='Top'){
			t=w.scrollTop();
			if(r.a||r.b){//限制滚动范围
				if(t<r.a){
					w.scrollTop(r.a);//$('html,body') 兼容:html chrome body 手机
				}
				if(t>r.b){
					w.scrollTop(r.b);
				}
			}
			view.y+=t;
		}else{
			t=w.scrollLeft();
			view.x+=t;
		}
		ds=t-s;
		if(ds>0)
		{
			dt=1;
		}
		else if(ds<0)
		{
			dt=0;
		}
		
		if(mtfScroll.dt!==dt){
			mtfScroll.dt=dt;
			dr=1;
		}
		
		if(dt)
		{
			for (var i = a; i < l; i++) {
				if(mtfScroll.loop(i,direction,view,dt,dr,ds)){//1为down
					break;	
				}
			}
		}
		else
		{
			for (var i = a; i >= 0; i--) {
				if(mtfScroll.loop(i,direction,view,dt,dr,ds)){//0为up
					break;	
				}
			}	
		}
		
		mtfScroll.last[direction]=t;
	},
	limit:function(direction,a,b){//限制滚动范围
		if(mtfScroll.range){
			var c={'a':'','b':''};
			if(a||b){
				if(a){
					c.a=a;
				}
				if(b){
					c.b=b;
				}	
			}
			mtfScroll.range[direction]=c;
		}
	}
};