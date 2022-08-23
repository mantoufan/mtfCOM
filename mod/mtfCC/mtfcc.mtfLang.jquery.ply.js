var T={};//变量
var mtfCC={
	isContained:function(a,b){
		if(!(a instanceof Array) || !(b instanceof Array)) return false;
		if(a.length < b.length) return false;
		var aStr = a.toString();
		for(var i = 0, len = b.length; i < len; i++){
		   if(aStr.indexOf(b[i]) == -1) return false;
		}
		return true;
	},
	utf8to16:function(a){for(var e,f,g,b="",d=a.length,c=0;d>c;)switch(e=a.charCodeAt(c++),e>>4){case 0:case 1:case 2:case 3:case 4:case 5:case 6:case 7:b+=a.charAt(c-1);break;case 12:case 13:f=a.charCodeAt(c++),b+=String.fromCharCode((31&e)<<6|63&f);break;case 14:f=a.charCodeAt(c++),g=a.charCodeAt(c++),b+=String.fromCharCode((15&e)<<12|(63&f)<<6|(63&g)<<0)}return b},
	I:function(id){
		return $('#'+id)[0];	
	},
	aC:function(t,id){
		var d=$('<'+t+'>')[0];
		$('#'+id).append(d);
		return d;
	},
	c:'',
	cj:[],
	t:[],
	a:[],
	j:[],
	h:[],
	T:'',
	S:'',
	to:[],
	no:{},
	ic:{},
	e:function(s){
		try {
			return eval(s.split('^')[1]);
		} catch(error) {
　　			return false;
		}
	},
	g:function(jj,id){
		var l=mtfCC.j[id].length;
		for(var i=0;i<l;i++){
			if(mtfCC.j[id][i]==jj){
				return i;
			}
		}
		return 0;
	},
	f:function(s){
		return s.replace(/&amp;/gi,'&').replace(/<div.*<\/div>/gi,'').replace(/<(?!(\/p|b|\/b|img|\/img)\b)[^>]*>/gi, '').replace(/&lt;/gi,'<').replace(/&gt;/gi,'>');
	},
	l:function(id){
		if(typeof(mtfCC.a[id])==="undefined"){//防止重复初始化
			mtfCC.a[id]=[],mtfCC.j[id]=[],mtfCC.h[id]=[],mtfCC.t[id]=0,mtfCC.to[id]=[];
			mtfCC.c=mtfCC.f($('#'+id).html().replace(/<P(.*?)>/gi,''));
			mtfCC.c=mtfCC.c.split(/<\/P>/i);
			if(window[id+'JS']){
				//mtfCC.cj=JSON.parse(mtfCC.utf8to16($.base64.decode(window[id+'JS'])));
				mtfCC.cj=JSON.parse(mtfCC.utf8to16(window.atob(window[id+'JS'])));
				window[id+'JS']='';
			}
			var l=mtfCC.c.length,last_n=0,last_nn=0;	
			for (var i = 0; i < l; i++) {
				//var c=$.trim(mtfCC.c[i]+(mtfCC.cj[i]?mtfCC.f(mtfCC.utf8to16($.base64.decode(mtfCC.cj[i]))):''));
				var c=$.trim(mtfCC.c[i]+(mtfCC.cj[i]?mtfCC.f(mtfCC.utf8to16(window.atob(mtfCC.cj[i]))):''));
				if(!c)continue;
				var ar=$.trim(c).split(' '),n=ar[0];
				if(!isNaN(n)){
					mtfCC.j[id].push(n);
					mtfCC.a[id][n]=c;
					last_n=n;
				}else{
					mtfCC.a[id][last_n]+='/n/n'+c;
					if(last_nn!=last_n){
						mtfCC.t[id]++;
						last_nn=last_n;
					}
				}
			}
			mtfCC.c=[];mtfCC.cj=[];
			mtfCC.d(id,0);
		}
	},
	tr:function(s,id){
		var ar=s.split(' '),l=ar.length,s0=ar[0],s1,s2=ar[l-1],i,_s;
		if(l>2 && (s2.indexOf('^')>-1||!isNaN(s2)||s2==='|')){
			ar.splice(l-1,1);
		}else{
			s2='';
		}
		ar.splice(0,1);
		s1=ar.join(' ');
		if(!isNaN(s0)){
			i=mtfCC.no[id]=s0;
		}else{
			i=mtfCC.no[id]+s0;
		}
		_s=(s1?mtfCC.v(s1):'');
		return {i:i,n:s0,s:_s,j:s2,ar1:s.substr(s0.length+2).split(' ')};
	},
	v:function(s){
		var ar=s.match(/\^T\._\S+?\^/g);//非空格 问号-非贪婪模式
		if($.isArray(ar)&&ar[0]){
			var l=ar.length;
			for (var i = 0; i < l; i++) {
				s=s.replace(ar[i],eval(ar[i].substr(1,ar[i].length-2)));
			}
		}
		return s;
	},
	d:function(id,ii,jj){
		/*
		for(o in mtfCC){
			if(o.indexOf('_')==0){
				console.log('mtfCC.'+o+':'+mtfCC[o]+' ');
			}
		}
		*/
		mtfCC.ic[id]=false;
		var index=jj?jj:mtfCC.j[id][ii];
		if(!mtfCC.a[id][index]){
			return false;
		}
		var ar=mtfCC.a[id][index].split(/\/n\/n/i),l=ar.length,s=mtfCC.tr(ar[0],id);
		//s.s=mtfCC.v(s.s);
		if(jj)ii=mtfCC.g(jj,id);
		if(l===1){
			if(s.s.substr(0,1)==='^'){
				var ar=s.ar1,l=ar.length;
				for (var i = 0; i < l; i+=2) {
					var arr=ar[i].split('^');
					if(arr[1]){
						if(mtfCC.e(ar[i])){
							if(arr[0]==''){
								mtfCC.o(id,(parseInt(ii)+1),ar[i+1]?ar[i+1]:'','',1);
								return;
							}
						}
					}
					if(arr[0]&&mtfCC.isContained(mtfCC.h[id],arr[0].split(','))){
						mtfCC.o(id,(parseInt(ii)+1),ar[i+1]?ar[i+1]:'','',1);
						return;
					}else{
						continue;
					}
				}
				if(mtfCC.to[id][0]){mtfCC.d(id,'',mtfCC.to[id][0]);mtfCC.to[id].shift();return;}
				Ply.dialog("alert", mtfLang.get(['无','结果']));
				$('#'+id).delay(100).fadeIn();
				return;
			}else{
				mtfCC.I(id).innerHTML=s.s;
				if(s.j){mtfCC.e(s.j);}
				mtfCC.aC('ul',id).id=n=id+'_'+index+'_'+(parseInt(ii)+1)+'_'+(s.j&&s.j!=='|'?s.j.split('^')[0]:'');
				mtfCC.I(n).className='mtfCC-sel';
				if(s.j&&s.j=='|'){
					mtfCC.r(s.n,id,function(r){
						mtfCC.I(n).innerHTML+='<br>（<b>'+(r[s.n]?r[s.n]:0+mtfLang.get('人'))+'</b>'+mtfLang.get(['与你相同'])+')';
					});
					
					mtfCC.I(n).innerHTML=mtfLang.get('再来一遍');
					mtfCC.I(id).innerHTML=mtfCC.I(id).innerHTML.replace(s.s,'<b>'+s.s+'</b>');
					mtfCC.I(n).onclick=function(){
						if(mtfCC.ic[id])return false;
						mtfCC.ic[id]=true;
						mtfCC.h[id]=[];
						mtfCC.to[id]=[];
						mtfCC.o(id,0,'',this);
					}
				}else{
					if(mtfCC.ic[id])return false;
					mtfCC.ic[id]=true;
					mtfCC.I(n).innerHTML=mtfLang.get('继续');
					mtfCC.I(n).onclick=function(){
						mtfCC.o(id,this.id.split('_')[2],this.id.split("_")[3],this);
					}
				}	
			}
		}else{
			mtfCC.I(id).innerHTML=s.s;
			if(s.j){mtfCC.e(s.j);}
			for (var i = 1; i < l; i++) {
				var s=mtfCC.tr(ar[i],id),n=id+'___'+index+s.n+'___'+(parseInt(ii)+1)+'___'+(s.j&&s.j!=='|'?s.j.split('^')[0].replace(/,/g,'_'):'');
				mtfCC.aC('ul',id).id=n;
				mtfCC.I(n).className='mtfCC-sel';
				mtfCC.I(n).innerHTML=s.s;
				mtfCC.I(n).setAttribute('j',(s.j?s.j.split('^')[1]:''));
				mtfCC.I(n).onclick=function(){
					/*
					if(mtfCC.ic[id])return false;
					mtfCC.ic[id]=true;
					*/
					var ar=this.id.split('___'),j=this.getAttribute('j');
					mtfCC.h[id].push(ar[1]);
					if(ar[3]||j){
						mtfCC.o(id,ar[2],ar[3]+(j?'^'+j:''),this);
					}else{
						mtfCC.r(ar[1],ar[0],function(r){
							var a=[],ar=[],b=mtfCC.a[id];
							for(var i in b){
								var c=b[i].split(/\/n\/n/i);
								for(var j in c){
									var s=mtfCC.tr(c[j],id);
									ar[s.i]=s.s;
								}
							}
							for(var i in r){
								a.push(ar[i]+'：'+mtfLang.get(r[i].split(' ')));
							}
							Ply.dialog("alert", {title:mtfLang.get('结果'),html:a.join('<br>')});
						});
					}
				}
			}
		}
		var _s=mtfCC.aC('div',id);_s.innerHTML='<div>'+mtfLang.get('最多')+'<b>'+mtfCC.t[id]+'</b>'+mtfLang.get(['次','选择'])+'，'+(s.j&&s.j==='|'?'<b>'+mtfCC.h[id].length+'</b>'+mtfLang.get(['次','完成'])+'<br>':mtfLang.get(['已经','完成'])+'<b>'+mtfCC.h[id].length+'</b>'+mtfLang.get('次'))+'</div>';
		if(mtfCC.T){M.alert(mtfCC.T);mtfCC.T='';}
		_s.innerHTML='<button class="m-button" onclick="mtfCC.save(\''+id+'\',\''+ii+'\')">'+mtfLang.get('存档')+'</button><button class="m-button" onclick="mtfCC.load(\''+id+'\')">'+mtfLang.get('读档')+'</button>'+(mtfCC.S?mtfCC.S:'')+_s.innerHTML;mtfCC.S='';
		$('#'+id).delay(100).fadeIn();
	},
	r:function(i,id,f){
		mtfCC.ajax('result',{"i":i,"id":MTF.ID(),"si":id.replace('mtfCC','')},f);
	},
	o:function(id,ii,jj,o,auto){
		if(o)o.className='mtfCC-sel-a';ar=[];
		if(jj){
			var ar=jj.split('^');
			if(ar[1])mtfCC.e(jj);
			ar=ar[0].split('_');
			jj=ar[0];
		}
		if(!jj&&mtfCC.to[id][0]){jj=mtfCC.to[id][0];mtfCC.to[id].shift();}
		if(!auto){
			if(ar.length>1){
				ar.shift();
				mtfCC.to[id]=ar;if(!ar[ar.length-1]){mtfCC.to[id].shift();mtfCC.to[id].push(mtfCC.j[id][ii]);}
			}
			dl=150;
			$('#'+id).delay(dl).fadeOut('',function(){mtfCC.d(id,ii,jj)});
		}else{
			dl=0;
			mtfCC.d(id,ii,jj);
		}
	},
	ajax:function(t,d,f){
		$.ajax({
		  url:MTF.Var.domain.api+'bt/mtfCC/',
		  data:$.extend(d,{type:t}),
		  success: function(r){
			  if(f){
				  f(r);
			  }
		  }
		});
	},
	SL:function(id,t,ii){
		if(store.get('uid')){
			mtfCC.ajax('list','',function(r){
				
				var m={},r=JSON.parse(r);
				for(var _i = 1, len = 6; _i <= len; _i++){
					var i='s'+_i,_h=(t==='s'?i:(r&&r[i]?JSON.stringify(r[i]):'')),_v=(r&&r[i]?(r[i].i+' '+r[i].time):'');
					m['m'+i]={tag:'input',type:'button',className:'ply-input mtfCCi',hint:_h,value:_v};
				}
				Ply.dialog("alert", {
					title:mtfLang.get((t==='s'?'存':'读')+'档'),
					form:m
				});
				$('.mtfCCi').click(function(){
					$('.ply-overlay').parent().remove();
					if(t==='s'){
						mtfCC.save(id,ii,$(this).attr('placeholder'));
					}else{
						mtfCC.load(id,$(this).attr('placeholder'));
					}
				});
				
			})
		}else{
			Ply.dialog("alert", mtfLang.get(['请','登录']));
		}
	},
	save:function(id,ii,i){
		var s={};
		if(!i){
			mtfCC.SL(id,'s',ii);
		}else{
			for(o in T){
				if(o.indexOf('_')==0){
					s[o]=T[o];
				}
			}
			s.h=mtfCC.h[id].join(',');
			s.j=mtfCC.j[id][ii];
			s.i=MTF.ID();
			s.id=id.replace('mtfCC','');
			s.si=i;
			mtfCC.ajax('save',s);
		}
		
	},
	load:function(id,c){
		if(!c){
			mtfCC.SL(id,'l');
		}else{
			var s=JSON.parse(c);
			
			if(s.i!==MTF.ID()){
				Ply.dialog(
				  "confirm","",
				  mtfLang.get('离开')+'?'
				).done(function(){
					MTF.Render.jump(s.i);
				});
				return false;
			}
			id='mtfCC'+s.id;
			mtfCC.h[id]=s.h?s.h.split(','):[];
			for(o in T){
				if(o.indexOf('_')==0){
					if(isNaN(s[o])){
						T[o]=s[o];
					}else{
						T[o]=parseFloat(s[o]);
					}
				}
			}
			mtfCC.d(id,'',s.j);
		}
	}
}