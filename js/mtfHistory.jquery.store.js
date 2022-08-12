var mtfHistory={
	c:'.mtfHistory',
	r:[],
	t:'',
	s:['mhnoauto','mhr'],
	auto:0,//将要 连续播放 到的媒体的ID
	full:0,//是否全屏
	on:1,//人工 连续播放 开关
	load:function(){
		return store.get(this.s[1])?store.get(this.s[1]):[];
	},
	o:'',//绑定媒体
	t:'',//绑定媒体的标题
	d:0,//删除状态
	add:function(t,h,p,o){
		if(t){
			var r=this.load(),has=0;
			for(var i in r){
				if(t===r[i][0]){
					has=1;
				}
			}
			if(!has){
				r.push([t,h,p]);
				store.set(this.s[1],r);
			}
			this.o=o;
			this.t=t;
			this.list();
		}
	},
	list:function(){
		var a,img,r=this.load(),b,d=$(this.c),s,t,h;
		if(d.length===0){
			d=$('<div>');
			d.addClass(this.c.substr(1));
			if(this.o){
				d.insertAfter(this.o);
			}else{
				$('body').append(d);
			}
			
			s=$('<div>');
			s.html('<span class="m-lang">播放 列表</span><li><span class="m-lang">循环</span> <b><span class="m-lang">列表</span></b><b><span class="m-lang">单曲</span></b></li><li><span class="m-lang">删除</span><b> <span class="m-lang">退出</span></b></li>');
			s.find('b:eq(2)').hide();
			if(store.get(mtfHistory.s[0])){
				s.find('b:eq(0)').hide();	
			}else{
				s.find('b:eq(1)').hide();
			}

			s.find('li:eq(0)').click(function(){
				var o=$(this),h=o.html(),s=o.find('b');
				if($(s[0]).css('display')!=='none'){
					$(s[0]).hide();
					$(s[1]).css('display','inline-block');
					store.set(mtfHistory.s[0],1);
				}else{
					$(s[0]).css('display','inline-block');
					$(s[1]).hide();
					store.remove(mtfHistory.s[0]);
				}
			});
			
			s.find('li:eq(1)').click(function(){
				if(mtfHistory.d===0){
					mtfHistory.d=1;
					$(this).find('b').show();
					$(this).notify(mtfLang.get(['请','点','下面的','项目']),{className:'info'});
				}else{
					mtfHistory.d=0;
					$(this).find('b').hide();
				}
			});
			d.append(s);
			d.append($('<div>'));
		}else{
			d.find('a').remove();
		}
		var _d=d.find('div').last(),_l=0;
		_d.css({'white-space':'nowrap','overflow':'auto'});
		
		for(var i in r){
			b=r[i];
			t=b[0];
			h=b[1];
			
			a=$('<a>');
			a.attr('href',h);
			a.attr('t',t);
			if(b[2]){
				img=$('<img>');
				img.attr('src',b[2].replace(/h_(\d+)/, 'h_50'));
				a.append(img);
			}
			//a.append('<div>'+t+'</div>');
			
			_d.append(a);
			
			a.mousedown(function(){
				if(mtfHistory.d===1){
					var o=$(this);
					event.stopImmediatePropagation();
					mtfHistory.del(o.attr('t'));
					o.remove();
				}	
			});
			
			if(t===this.t){
				a.addClass(this.c.substr(1)+'-current');
				_l=i;
			}

		}
		setTimeout(function(){_d.scrollLeft(_l*100);},1000);
	},
	next:function(){
		if(this.on===1 && !store.get(this.s[0])){
			var r=this.load(),has=0,t=this.t;
			for(var i in r){
				if(t===r[i][0]){
					has=1;
				}else if(has===1){
					return r[i][1];
				}
			}
			return r[0][1];
		}else{
			return false;
		}
	},
	del:function(t){
		if(t){
			var r=this.load(),_r=[];
			for(var i in r){
				if(t===r[i][0]){

				}else{
					_r.push(r[i]);
				}
			}
			store.set(this.s[1],_r);
		}
	}
};