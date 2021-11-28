//依赖：mtfWay 通道管理器 mtfScroll 滚动管理器 Muscache Render 模版引擎
var mtfDM={
	p:'',
	o:'',
	c:['普通','滚动','渐显','固定'],
	s:['.mtf-dm-cover','.mtf-dm-point','.mtf-dm-po','.mtf-dm'],
	_po:'',
	full:0,//是否全屏
	last:'',//上一条弹幕内容
	guid:function(){
		function S4() {
		   return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
		}
		return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
	},
	closestNum:function(ar, number) {
		var tmp_ar = [];
		var length = ar.length;
		for (var i = 0; i < length; i++) {
			tmp_ar[i] = Math.abs(ar[i] - number)
		};
		var ar_min = Math.min.apply(null, tmp_ar);
		for (var i in tmp_ar) {
			if (tmp_ar[i] == ar_min) {
				return ar[i];
				break
			}
		}
	},
	arrayKeys:function(tmp_ar) {
		var arrayKeys = [];
		for (var key in tmp_ar) {
			arrayKeys.push(key)
		};
		return arrayKeys
	},
	init:function(p,o){//菜单按钮位置 目标对象
		var l=o.length,cl=mtfDM.c.length,_s,_d;
		mtfDM.p=p;
		
		if(l){
			if(!p.html()){
				_s=$('<select>');
				for(var i=1;i<cl;i++){//略过普通
					_s.append('<option value="'+i+'">'+mtfDM.c[i]+'</option>');
				}
				p.append(_s);
				_s.on('change',function(){
					$(mtfDM.s[1]).remove();
					
					switch($(this).val())
					{
						case '3':
							mtfDM.cover('display');
							break;
						default:
							mtfDM.cover('hide');
					}
					//console.log(o.find("option:selected").text());
				 });
			}
			p.css('display','inline-block');
			mtfDM.o=o[0];
			switch(p.find('select').val())
			{
				case '3':
					
					mtfDM.cover('display');
			}
		
			var po,d,t0,c,ds=[],_max=4,_c;
			for(var i=0;i<1;i++){//选择第一个元素
				po=mtfDM.po($(o[i].o));
				t0=o[i].d/100*po.h;
				c=mtfDM.s[2];
				$(c).hide();
				clearTimeout(mtfDM._po);
				
				if($(c).length===0){
					for(var _i=0;_i<_max;_i++){//两个箭头
						d=$('<div>');
						d.addClass(c.substr(1));
						ds.push(d);	
						$('body').append(d);
					}
				}else{
					ds=$(c);
				}
				
				mtfDM._po=setTimeout(function(){
					$(mtfDM.s[2]).fadeOut();
				},3000);
				
				for(var _i=0;_i<_max;_i++){//两个箭头
					d=$(ds[_i]);d.show();
					d.css({width:0,height:0,position:'absolute','opacity':0.85});
					var l=-parseInt($(o[i].o).css('margin-left'));
					if(_i<2){
						_c='black';
					}else{
						_c='white';
					}
					if(_i===0||_i===2){
						d.css({left:po.x+l-13,'border-left':'15px solid '+_c});
					}else{
						d.css({left:po.x+po.w-l-3,'border-right':'15px solid '+_c});
					}
					d.css({top:po.y-15+t0,'border-top':'10px solid transparent','border-bottom':'10px solid transparent'});
				}
			}
		}else if(!mtfDM.full){
			mtfDM.o='';
			p.hide();
			mtfDM.cover('hide');
		}
	},
	cover:function(_t){//显示 隐藏
		var st=$(mtfScroll.wrap).scrollTop();
		$(mtfDM.s[0]).remove();//兼容全屏模式
		if(_t==='display'){
			var d=$('<div>');
			d.addClass(mtfDM.s[0].substr(1));
			d.css({'background':'#000000','opacity':0.65,'position':'fixed','width':'100%','height':'100%','top':0});
			mtfDM._append(d);
			d.on('click',function(e){
				var x=e.pageX,y=e.pageY;
				$(mtfDM.s[1]).remove();
				var s=$('<div>'),sy=(mtfScroll.wrap===window?y:st+y);//框架内外
				s.addClass(mtfDM.s[1].substr(1)); 
				s.css({width:20,height:20,background:'#FFFFFF',left:x-10,top:sy-10,position:'absolute','opacity':0.65,'border-radius':'50%','z-index':1});
				mtfDM._append(s);

			});
		}
	},
	remove:function(o,t){
		if(!t){
			mtfWay.remove(o);
		}else{
			$(o).remove();
		}	
	},
	_append:function(o){
		if($('.mejs__container-fullscreen').length>0){
			$('.mejs__container-fullscreen').find('.mejs__layers').append(o);
			return false;
		}else{
			if(mtfScroll.wrap===window){
				$('body').append(o);
			}else{
				$(mtfScroll.wrap).append(o);
			}
			return true;
		}
	},
	display:function(data,_d,p,_t){//_t=1,时间弹幕，{1:{m:'',v:'',d:''},3:{m:'',v:'',d:''}}
		if(p){
			_d=parseFloat(_d);
			var i=mtfDM.closestNum(mtfDM.arrayKeys(data),_d),dd,d,t,dw=$(document).width(),dl,t0,__d=_d-i,j,_b,st=$(mtfScroll.wrap).scrollTop();
			if(__d>=0 && __d<(_t===1?0.55:1.35)){//等于0，刚发的弹幕，马上出现
				dd=data[i];
				dl=dd.length;
				for(var _i=0;_i<dl;_i++){
					var o=$('<div>'),h;
					d=dd[_i];
					if(d.v){
						j=JSON.parse(d.v);
					}
					h=MTF.Render.render([d.d]);
					if(_t!==1){//非时间弹幕 检测过滤重复弹幕
						if(h===mtfDM.last){
							return false;
						}else{
							mtfDM.last=h;
						}
					}
					o.html(h);
					o.css('position','absolute');
					o.addClass(this.s[3].substr(1));
					po=mtfDM.po(p);
					
					var sy=(mtfScroll.wrap===window?po.y:st+po.y);//框架内外
					
					switch(d.m)
					{
						case '1'://滚动弹幕
							if(_t){
								t0=0;
							}else{
								t0=i/100*po.h;
							}
							
							_b=mtfDM._append(o);
							o.css({'right':-o.width()});
							
							t=mtfWay.add({y1:t0,y2:po.h,id:mtfWay.id(p)+'_'+d.m},o[0]);
							o.css({'top':(_b?sy:0)+t,'width':o.width()+5});
							
							o.animate({'right':dw},{
								duration:2600+Math.floor($(window).width()*4),
								step:function(n, fx) {
									var e=$(fx.elem);
									if(!e.attr('r') && Math.floor(n-e.width())>=1){
										mtfDM.remove(e[0]);
										e.attr('r',1);
									}
								},
								complete:function(){
									mtfDM.remove(this,1);
								},
								easing:'linear'
							 });
							
							break;
						case '2'://渐显渐隐
							if(_t){
								t0=0;
							}else{
								t0=i/100*po.h;
							}
							
							o.css({'right':0});
							_b=mtfDM._append(o);
							
							t=mtfWay.add({y1:t0,y2:po.h,id:mtfWay.id(p)+'_'+d.m},o[0]);
							o.css({'top':(_b?sy:0)+t,'right':(dw-o.width())/2,'width':o.width()+5});
							
							o.fadeIn(300).delay(2400).fadeOut(300,function(){mtfDM.remove(this);mtfDM.remove(this,1);});
							
							break;
						case '3'://固定
							_b=mtfDM._append(o);
	
							o.css({'left':j.x/100*po.w+po.x,'top':j.y/100*po.h+(_b?sy:0),'width':o.width()+5}); 
							o.fadeIn(300).delay(2400).fadeOut(300,function(){mtfDM.remove(this,1);});
							break;
						default:
							break;
					}
				}
			}else{
				mtfDM.last='';//避免短时重复出现
			}
			
		}
	},
	po:function(o){
		var p=$(o),po=p.offset(),_h=p.height();//全屏后，_h=0
		return {x:po.left,y:po.top,w:p.width(),h:_h?_h:$(window).height()};
	},
	value:function(){
		var p=mtfDM.p,m,d,v,po,oo,x,y;
		if(p && p.css('display')!=='none'){
			m=p.find('select').val();
			if(m==='3'){
				oo=$(mtfDM.s[1]).offset();
				if(oo){
					x=oo.left;
					y=oo.top;
				}else{
					x=y=Math.round(Math.random()*300+0);//如果没有设置，随机固定位置出现
				}	
				po=mtfDM.po(mtfDM.full?mtfDM.full:mtfDM.o.o);
				_x=(x-po.x)/po.w*100;
				_y=(y-po.y)/po.h*100;
				
				v=JSON.stringify({x:_x.toFixed(4),y:_y.toFixed(4)});
			}
			d=mtfDM.o.d.toFixed(4);
		}
		return {m:m,d:d,v:v};
	}
};