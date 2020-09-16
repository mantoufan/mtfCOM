var mtfFol={
	tag:[],
	max:6,
	_add:function(k,_i){//标签，权重
		var l=mtfFol.tag.length,a=0,_i=(_i?_i:1);
		for(var i=0;i<l;i++){
			if(mtfFol.tag[i].k===k){
				mtfFol.tag[i].i+=_i;
				a=1;
			}
		}
		if(a===0){
			mtfFol.tag.push({'k':k,'i':_i});
		}
		mtfFol.tag.sort(function(a,b){  
			if(a.i<b.i){  
				return 1;  
			}else if(a.i>b.i){  
				return -1;  
			}  
			return 0;  
		}); 
	},
	add:function(k,_t){//标签数组，权重
		var a;
		for(var i in k){
			mtfFol._add(k[i],_t);
		}
		a=mtfFol.tag.slice(0,mtfFol.max);
		store.set('fol',a);
		return a;
	},
	get:function(k){//读取到的标签
		if(k){
			store.set('fol',k);
			mtfFol.tag=k;	
		}else if(store.get('fol')){
			mtfFol.tag=store.get('fol');
		}
		return mtfFol.tag;
	}
}