var mtfBBcode={
	a:function(d,t,f){
		$.ajax({
		  url:MTF.Var.domain.api+'bt/mtfBBcode',
		  data:$.extend(d,{type:t}),
		  success: function(r){
			  f(r);
		  }
		});
	},
	listen:function(o){
		o.find('.mtfBBcode-buy').click(function(){
			var a=$(this).find('sup').html(),d=JSON.parse($(this).attr('data'));
			Ply.dialog(
				"confirm",
				{ effect: "3d-sign" },
				a
			).done(function(ui){
				mtfBBcode.a(d,'buy',function(){
					MTF.Render.init();
				});
			});
		});
		o.find('.mtfBBcode-key').click(function(){
			var a=$(this).find('sup').html(),d=JSON.parse($(this).attr('data'));
			Ply.dialog("prompt", {
			  title: a,
			  form: { num: mtfLang.get([mtfLang.get('购买'),mtfLang.get('数量')]) }
			}).done(function(ui){
				mtfBBcode.a($.extend(d,ui.data),'key',function(){
					MTF.Render.init();
				});
			});
		});
		o.find('.mtfBB-weather').each(function(a,b){
			setTimeout(function(){
				mtfWeather('stop');
				mtfWeather($(b).text(),90);
				mtfWeather('play');
			});
		});
		var a=o.find('.mtfBB-Menu a');
		if(a.length>0){
			a.unbind('click');
			a.click(function(){
				var t=$(this),p=t.parent(),q=p.parent(),r=[],b;
				if(t.hasClass('cur')){
					t.removeClass('cur');
				}else{
					p.find('a').removeClass('cur');
					t.addClass('cur');
				}
				q.find('.cur').each(function(a,b){
					r.push($(b).text());
				});
				/*
				b=location.href.split('#')[0].split('?')[0].split('/');
				if(b.length>4){
					b.pop();
				}
				*/
				MTF.Render.jump(r.length>0?'/'+r.join(' '):'');
				return false;
			});
			var b=window.location.pathname.substr(1).split('?')[0].split('#')[0].split('%20');
			for(var i=0;i<b.length;i++){
				var p=o.find('.mtfBB-Menu [href="/'+decodeURIComponent(b[i])+'"]');
				p.addClass('cur');
				p.insertAfter(p.parent().children().first());
			}
		}
		
		
	},
	_c:'mtfBBcode-',
	c:function(){
		return this._c+'json';
	},
	cs:function(){
		return this._c+'set';
	},
	cq:function(){
		return this._c+'q';
	},
	ch:function(){
		return this._c+'hide';
	},
	cp:function(){
		var ch=this.ch(),s=$('<span>');
		s.addClass(ch);
		s.html('|');
		return s;
	},
	fh:function(o,t){
		var h=o.html(),c=this.c(),cs=this.cs(),cq=this.cq(),ch=this.ch();
		o.find('.'+c).each(function(a,b){
			b=$(b);
			h=h.replace(b.prop('outerHTML'),b.html().replace('] ',']'));   
		});
		o.find('.'+cs).each(function(a,b){
			h=h.replace($(b).prop('outerHTML'),'');   
		});
		o.find('.'+ch).each(function(a,b){
			h=h.replace($(b).prop('outerHTML'),'');   
		});
		if(t==='save'){
			o.find('.'+cq).each(function(a,b){
				b=$(b);
				h=h.replace(b.prop('outerHTML'),'<p>'+b.html()+'</p>');   
			});
		}
		return h;
	},
	paste:function(h){
		var a=[new RegExp('\\|'+mtfLang.get('设置'), 'g'),new RegExp('\\|'+mtfLang.get('数据'), 'g'),new RegExp('\\[set\\] ', 'g'),new RegExp('\\[dat\\] ', 'g')],s='';
		for(var i=0;i<a.length;i++){
			if(i===2){
				s='[set]';
			}else if(i===3){
				s='[dat]';
			}
			h=h ? h.replace(a[i],s) : '';
		}
		return h;
	},
	fl:function(o){
		var h,e,s,_j,m,c=this.c(),cs=this.cs(),cq=this.cq(),ch=this.ch();
		h=this.fh(o);
		m=h.match(/\[(set|dat)\](.*?)\[\/\1\]/gm);//多行 /m
		if(m){
			for(var i=0;i<m.length;i++){
				e=m[i];
				t=e.replace(/\[(set|dat)\](.*?)\[\/\1\]/gm,'$1');
				h=h.replace(e,'<div type="'+t+'" class="'+c+' '+ch+'">'+e.replace(']','] ')+'</div>');//避免重复替换
			}
		}
		o.html(h);
		o.find('.'+c).each(function(a,b){
			b=$(b),s=$('<input>');s.attr('type','button'),t=b.attr('type');
			if(t==='set'){
				s.val(mtfLang.get('设置'));
			}else{
				s.val(mtfLang.get('数据'));
			}

			s.addClass(cs);
			b.before(s);
			s.before(mtfBBcode.cp());
			s.click(function(){
				mtfBBcode.p($(this).next());
			});
		});
		o.find('.'+cq).click(function(){
			var t=$(this);
			t.html('');
			t.unbind('click');
		});
	},
	p:function(o,obj){
		var e,t,j,k={},v,h,d,_t,cq=this.cq();
		if(typeof(o)==='string'){
			_t='set';
			switch(o)
			{
				case 'hide':
				q=mtfLang.get(['点','此','输入','回复','可见','的','内容']);
				break;		
				case 'buy':
				j={'zan':''};
				q=mtfLang.get(['点','此','输入','出售','的','内容']);
				break;	
				case 'key':
				j={'zan':'','num':''};
				q=mtfLang.get(['点','此','输入','出售','的','卡密',',1','行','1','/个']);
				break;
				case 'cc':
				q=mtfLang.get(['<p>1 ','选择','题','：</p><p>A ','选项','A</p><p>B ','选项','B</p><p>C ','选项','C</p><p>D ','选项','D</p>']);
				break;
				case 'weather':
				q='雪/蝴蝶/光/雨/樱花';
				break;
				case 'video':
				q=mtfLang.get(['优酷','/','腾讯','/','虎牙','视频','链接']);
				break;
			}
			if(q){
				q='<div class="'+cq+'">'+q+'</div>';
			}
		}else{
			e=o.html();
			if(e){
				_t=e.replace(/\[(set|dat)\](.*?)\[\/\1\]/gm,'$1');
				j=JSON.parse(e.replace(/\[(set|dat)\](.*?)\[\/\1\]/gm,'$2'));
				
			}
		}
		
		if(j){
			for(var i in j){
				var b;
				switch(i)
				{
					case 'zan':
						b=mtfLang.get('价格');
						break;
					case 'num':
						b=mtfLang.get(['每人','最多','购买','数量']);
						break;
					case 'reply':
						b=mtfLang.get(['已经','回复','的','人']);
						break;
					case 'buy':
						b=mtfLang.get(['已经','购买','的','人']);
						if(j[i]){
							if($.isArray(j[i])){
								
							}else{//卡密
								j[i]=JSON.stringify(j[i]);
							}
						}
						break;
					case 'result'://投票、测试结果数据
						j[i]={value:JSON.stringify(j[i])};
						break;
				}
				if(b){
					j[i]={hint:b,value:j[i],required:0};
				}
			}
			switch(_t)
			{
				case 'set':
					t=mtfLang.get('设置');
					break;
				case 'dat':
					t=mtfLang.get('数据');
					break;
			}
				
			MTF.T.prompt(t,j).done(function(ui){
				delete ui.data['ok'];
				delete ui.data['cancel'];
				delete ui.data['dialog-form'];
				var z=ui.data['zan'];
				if(ui.data.hasOwnProperty('zan') && isNaN(z)){
					ui.data['zan']=1;
				}
				if(ui.data.hasOwnProperty('buy')){
					var b=ui.data['buy'];
					if(b){
						if(b.indexOf('":["')>0){//卡密
							ui.data['buy']=JSON.parse(b);
						}else{
							ui.data['buy']=b.split(',');
						}
					}
				}
				if(ui.data.hasOwnProperty('result')){//投票、测试结果数据
					ui.data['result']=JSON.parse(ui.data['result']);
				}
				var _h='['+_t+']'+JSON.stringify(ui.data)+'[/'+_t+']';
				if(typeof(o)==='string'){
					h='['+o+']'+q+_h+'[/'+o+']';
					obj.focus();
					insertHtmlAtCaret(h);
					mtfBBcode.fl(obj);
				}else{
					o.html(_h);
				}
			});
		}else{
			h='['+o+']'+q+'[/'+o+']';
			obj.focus();
			insertHtmlAtCaret(h);
			mtfBBcode.fl(obj);
		}
	}
}