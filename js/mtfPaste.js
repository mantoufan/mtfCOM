//粘贴
var mtfPaste={
	cancel:false,
	domain:window.location.host.split('.').slice(-2).join('.'),
	filter:function(t, h){
		var r=false;
		if(t){
			t=t.replace(/<div>(.*?)<br><\/div>/,'$1').replace(/<div>(.*?)<\/div>/,'$1');//过滤从程序中复制文字后跟的特殊符号
			if(t.indexOf(mtfPaste.domain)===-1){
				if(h && h.indexOf(mtfPaste.domain)!==-1) {
					r=true;
				} else {
					t=t.replace(/<(img\b)[^>]*>/gi, '');
					r=false;
				}
			}else{
				r=true;
			}
			
		} else if (h && h.indexOf(mtfPaste.domain)!==-1) {
			r=true;
		}
		mtfPaste.cancel=r;
		return t;
	},
	run:function(_b,_f){
		// 干掉IE http之类地址自动加链接
		// try {
		// 	document.execCommand("AutoUrlDetect", false, false);
		// } catch (e) {}
		
		var e=event,s=window.getSelection ? window.getSelection() : window.document.selection,r=s.rangeCount > 0 ? s.getRangeAt(0) : window.document.createRange(),n=document.createElement('DIV'),c=document.createTextNode('_'),_s,_s0;
		n.appendChild(c);
		r.deleteContents();
		r.insertNode(n);
		r.setStartBefore(c);
		r.setEndAfter(c);
		s.removeAllRanges();
		s.addRange(r);

		// if(window.clipboardData && clipboardData.setData) {
			// IE
		// 	_s = window.clipboardData.getData('text');
		// 	_h = '';
		// } else {
			_s = (e.originalEvent || e).clipboardData.getData('text/plain');
			_h = (e.originalEvent || e).clipboardData.getData('text/html');
		// }
		_s=mtfPaste.filter(_s, _h);
		if(_b){
			_s=_b(_s);
		}
		setTimeout(function() {
			if(!mtfPaste.cancel){
				if(n.parentNode){
					n.parentNode.removeChild(n);
					// if (document.body.createTextRange) {    
					// 	if (document.selection) {
					// 		textRange = document.selection.createRange();
					// 	} else if (window.getSelection) {
					// 		sel = window.getSelection();
					// 		var range = sel.getRangeAt(0);

					// 		// 创建临时元素，使得TextRange可以移动到正确的位置
					// 		var tempEl = document.createElement("span");
					// 		tempEl.innerHTML = "&#FEFF;";
					// 		range.deleteContents();
					// 		range.insertNode(tempEl);
					// 		textRange = document.body.createTextRange();
					// 		textRange.moveToElementText(tempEl);
					// 		tempEl.parentNode.removeChild(tempEl);
					// 	}
					// 	textRange.text = _s;
					// 	textRange.collapse(false);
					// 	textRange.select();
					// } else {
						// Chrome之类浏览器
						document.execCommand("insertText", false, _s);
					// }
				}
			}
			if(_f){
				_f();
			}
		},4);
		
		/*
		var s = null,e=event;
		e.preventDefault();
		
		if(window.clipboardData && clipboardData.setData) {
			// IE
			s = window.clipboardData.getData('text');
		} else {
			s = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('在这里输入文本');
		}
		
		s=mtfPaste.filter(s);
		
		if (document.body.createTextRange) {    
			if (document.selection) {
				textRange = document.selection.createRange();
			} else if (window.getSelection) {
				sel = window.getSelection();
				var range = sel.getRangeAt(0);

				// 创建临时元素，使得TextRange可以移动到正确的位置
				var tempEl = document.createElement("span");
				tempEl.innerHTML = "&#FEFF;";
				range.deleteContents();
				range.insertNode(tempEl);
				textRange = document.body.createTextRange();
				textRange.moveToElementText(tempEl);
				tempEl.parentNode.removeChild(tempEl);
			}
			textRange.text = s;
			textRange.collapse(false);
			textRange.select();
		} else {
			// Chrome之类浏览器
			document.execCommand("insertText", false, s);
		}
		*/
	}
};