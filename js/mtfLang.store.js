var mtfLang={
	usrLang:'',
	rec:function(){
		if(this.usrLang){
			
		}else if(store.get('lang')){
			this.usrLang=store.get('lang');
		}else{
			var lang = navigator.language;   //判断除IE外其他浏览器使用语言
			if(!lang){//判断IE浏览器使用语言
				lang = navigator.browserLanguage;
			}
			var _ar=lang.split(';'),_l=_ar.length;
			for(var _i=0;_i<_l;_i++){
				if(typeof(LANG[_i])!=="undefined"){
					this.usrLang=_i;
				}
			}
		}
		return typeof(LANG[this.usrLang])!=="undefined"?this.usrLang:'zh-CN';
		
	},
	get:function(_s,_ar){
		var _usrLang=this.rec(),_a=[],_l,__s,s='';
		if(Object.prototype.toString.call(_s)==='[object Array]'){
			_a=_s;
		}else{
			_a.push(_s);
		}
		_l=_a.length;
		for(var _i=0;_i<_l;_i++){
			__s=_a[_i];
			s+=(LANG[_usrLang]&&LANG[_usrLang][__s])?LANG[_usrLang][__s]:__s;
			if(_usrLang!=='zh-CN' && _usrLang!=='zh-TW' && _i<_l-1){//非中文，添加空格分隔符
				s+=' ';
			}
		}
		
		return this.getRaw(s,_ar);
	},
	getRaw:function(_s,_ar){
		if(_ar){
			var _l=_ar.length;
			for(var _i=0;_i<_l;_i++){
				_s=_s.replace('$'+_i,_ar[_i]);
			}
		}
		return _s;
	},
	flag:function(list,icon){
		var _ar=list,_l=_ar.length,_d,_p;
		_d=document.createElement('div');
		_d.className='mtf-flag';
		for(var _i=0;_i<_l;_i++){
			_p=document.createElement('img');
			_p.src=icon+'/'+_ar[_i]+'.png';
			_p.onclick=function(){
				var _a=this.src.split('/'),_e=_a[_a.length-1].split('.')[0];
				store.set('lang',_e);
				location.reload();
			}
			_d.appendChild(_p);
		}
		return _d;
	}
}