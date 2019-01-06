/**
 * Smooth scroll to top script with
 * stop on mouse clicks or mousewheel
 * Author: JoiNNN
 * 
 * Uses: jQuery mousewheel plugin
 */

//smoothscroll to top on click
jQuery(document).ready(function() {
	$(".scroll").click(function(e){
		//prevent the default action for the click event
		e.preventDefault();
		//this element
		var el = this;
		//get the full url - like mysitecom/index.htm#home
		var full_url = this.href;
		//split the url by # and get the anchor target name - home in mysitecom/index.htm#home
		var parts = full_url.split("#");
		var trgt = parts[1];
		//get the top offset of the target anchor
		var target_offset = $("#"+trgt).offset();
		var target_top = target_offset.top;
		//goto that anchor by setting the body scroll top to anchor top
		$("html, body").animate({scrollTop:target_top}, 500, function() {
			//add the hash in url if scrolling is complete
			if(!$(el).hasClass("clean")) {
				window.location.hash = trgt;
			}
		});
	});
	//if hash found in url scroll to coresponding anchor
	var hash = window.location.hash;
	var target_offset = $(hash).offset();
	var clean = $("a[href=" + hash + "]").hasClass("clean");
	if (target_offset && !clean) {
		var target_top = target_offset.top;
		//scroll
		$("html, body").animate({scrollTop:target_top}, 500);
	}
	//stop scrolling if mousewheel or clicks are hit
	$(document).bind("mousewheel mousedown", function(ev) {
		$("html, body").stop()
	});
});