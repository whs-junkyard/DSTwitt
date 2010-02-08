function Sizzle(id, e){
		if(id.indexOf("#") == 0){
			return [document.getElementById(id.substr(1))];
		}else if(id.indexOf(".") == 0){
			if(!e) e = document;
			els = e.getElementsByTagName("*");
			pattern = new RegExp("(^|\\s)"+(id.substr(1))+"(\\s|$)");
			for (i = 0, j = 0; i < els.length; i++) {
				if (pattern.test(els[i].className)) {
					return [els[i]];
				}
			}
			return false;
		}else{
			return false;
		}
}
