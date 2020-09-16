var mtfWay = {
	_oid:1,
	id:function(o) {
		if (o==null){
			return null;
		}
		if (o._oid==null){
			o._oid=mtfWay._oid++;
		}
		return o._oid;
	},
	s:{p:[],o:[]},
	q:function(a,g,h){
		for (var i=0,p;p=a[i],i<a.length;i++) {
			var c = [],e=Math.min(p[0],p[1]),f=Math.max(p[0],p[1]),m,n;
			if(!g[e])g[e]=2;if(!g[f])g[f]=1;
			for (var k in g) {
				if(k>=e&&k<=f){
					c.push([k, g[k]]);
				}
			}
			c.sort(function(x,y){return x[0]-y[0];});
			delete g[e];
			delete g[f];
			var l=c.length;
			if(p[1]>=p[0]){
				for (var j=0,d;d=c[j],j<l;j++) {
					if(d[1]==2){
						m=d[0];
					}else if(d[1]==1){
						if(m){
							if((d[0]-m)>=h){
								return parseFloat(m);
							}
						}	
					}
				}
			}else{
				for (var j=l-1,d;d=c[j],j>-1;j--) {
					if(d[1]==1){
						m=d[0];
					}else if(d[1]==2){
						if(m){
							if((m-d[0])>=h){
								return parseFloat(m-h);
							}
						}	
					}
				}
			}
		}
		return false;
	},
	bpi:function(b,p,i){
		if(!b[p])b[p]={};
		if(!b[p][i])b[p][i]={};
		return b;
	},
	add:function(v,o){
		var t=false,h=o.offsetHeight,p=v.id,i=0,id=mtfWay.id(o);
		while(t===false&&i<3){
			mtfWay.s.p=mtfWay.bpi(mtfWay.s.p,p,i);
			t=mtfWay.q([[v.y1,v.y2]],mtfWay.s.p[p][i],h);
			h=t+h;
			i++;
		}
		i--;
		var bt=mtfWay.s.p[p][i][t],bh=mtfWay.s.p[p][i][h];
		mtfWay.s.p[p][i][t]=(bt&&bt==2?3:1);
		mtfWay.s.p[p][i][h]=(bh&&bh==1?3:2);
		mtfWay.s.o[id]=[t,h,i,p];
		return t;
	},
	remove:function(o){
		var id=mtfWay.id(o),a=mtfWay.s.o[id],t=a[0],h=a[1],i=a[2],p=a[3],bt=mtfWay.s.p[p][i][t],bh=mtfWay.s.p[p][i][h];delete mtfWay.s.o[id];
		if(bt){
			if(bt==3){
				mtfWay.s.p[p][i][t]=2;
			}else if(bt==1){
				delete mtfWay.s.p[p][i][t];
			}
		}
		if(bh){
			if(bh==3){
				mtfWay.s.p[p][i][h]=1;
			}else if(bh==2){
				delete mtfWay.s.p[p][i][h];
			}
		}
	}
};