function Sizzle(id, e){
		if(id.search(/^\#/) != -1){
			return [document.getElementById(id.replace(/^\#/, ""))];
		}else if(id.search(/^\./) != -1){
			if(!e) e = document;
			els = e.getElementsByTagName("*");
			elsLen = els.length;
			pattern = new RegExp("(^|\\s)"+(id.replace(/^\./, ""))+"(\\s|$)");
			for (i = 0, j = 0; i < elsLen; i++) {
				if ( pattern.test(els[i].className) ) {
					return [els[i]];
				}
			}
			return false;
		}else{
			return false;
		}
}