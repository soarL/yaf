requirejs.config({
	baseUrl:'js',
	paths: {
		'jquery':'jquery/jquery.min',
		'mui':'mui/mui.min',
		"mui/zoom":"mui/zoom",
		"mui/preview":"mui/previewimage",
		"mui/picker":"../lib/mui/mui.picker.min",
	}
});

requirejs(["require","jquery","mui"],function(_r,$,mui){
	var _this = {
		"ready":function(){
			var group = _this.getParam("param");
			$("#pageHelp").find('li').each(function(){
				var $this = $(this);
				if($this.is('[data-group="'+ group +'"]')){
					$this.removeClass("hidden");
				}
				else if($this.is(":not(.hidden)")){
					$this.addClass("hidden");
				}
			});
			console.log(group);
		},
		"isNull":function(val){
			return "" === val || null === val || undefined === val || ("number" !== typeof(val)?$.isEmptyObject(val) : false);
		},
		"getParam":function(str){
			var s = location.search || data;
			if(_this.isNull(s)) return undefined;
			var r = /[\?|&]([^=]*)=([^&]*)/ig, p = s.match(r),o = {};
			$.each(p,function(i,v){
				var l = v.indexOf("="),ik = v.substring(1,l),iv = v.substr(l+1);
				o[ik] = iv;
			});
			return _this.isNull(str)?o:(o[str]);
		}
	};
	
	$(function(){
		mui.init();
		_this.ready();
	});
	
});