$(function(){
	$(".content img").eq(0).show();
	var i = 0;
	var index = $(".content img").length -1;
	$('.page_right').on("click",function(){
		$(".content img").hide().attr("data-show","s");
		i++;
		$(".content img").eq(i).show().attr("data-show","t");
		menuShow()
		if(i==index){
			i= -1
		}
	})

	$(".page_left").on("click",function(){
		$(".content img").hide().attr("data-show","s");
		i--;
		$(".content img").eq(i).show().attr("data-show","t");
		menuShow()
		if(i == -index){
			i = 1;
		}
	})

	$(".page7").on("click",function(){
		window.location.href = window.location.origin;
	})

	$(".menu li").eq(0).click(function(){
		$(".menu li").removeClass("menuLi");
		$(".container-fluid").hide();
		$(".content img").hide();
		$(".content img").eq(0).show();
		i = 0;
		$(".title").show()
		$(".menu li").eq(1).addClass("menuLi");
	})

	$(".menu li").on("click",function(){
		if($(this).index()==0){
			return
		}
		var index = $(this).data("page");
		$(".menu li").removeClass("menuLi");
		$(this).addClass("menuLi")
		var that = ".content img[data-page='"+index+"']";
		$(".content img").hide();
		$(that).eq(0).show();
	})
})

function menuShow(i){
	$(".menu li").removeClass("menuLi");
	var index = $(".content img[data-show='t']").data("page")
	var that = ".menu li[data-page='"+index+"']";
	$(that).addClass("menuLi")
}
$(".title").on("click",function(){
	$(this).hide();
	$(".container-fluid").show()
})