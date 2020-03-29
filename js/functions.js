jQuery(function($) {
	if ( $('#socialshareprivacy').length ) {
		$('#socialshareprivacy').socialSharePrivacy({
			"css_path"  : "socialshareprivacy/socialshareprivacy.css",
			"lang_path" : "socialshareprivacy/lang/",
			"language"  : "de"
		});
	}

	$(document)
	.on('focusin', 'input[type="text"], input[type="tel"], input[type="email"], input[type="url"], textarea', function() {
		if ( this.title == this.value ) {
			this.value = '';
		}
	})
	.on('focusout', 'input[type="text"], input[type="tel"], input[type="email"], input[type="url"], textarea', function(){
		if ( this.value == '' ) {
			this.value = this.title;
		}
	})
	.on('click', '.nav .trigger', function(e) {
		e.preventDefault();

		$('.nav').toggleClass('expanded');
	})
	.on('click', '.touch .nav li a', function(e) {
		var dd = $(this).parent().find('ul:eq(0)');

		if ( dd.length && !$(this).hasClass('clicked')) {
			e.preventDefault();

			dd.stop(true,true).slideToggle(500);
			$(this).addClass('clicked');
		}
	})
	.on('ready', function(){
		var isNavClickable = true;
		var navClickableTreshold = 500;
		
		$('.menu-item-has-children').children('a').one('click', function(e){
			if ( $(window).width() <= 1024 && isNavClickable ) {
				isNavClickable = false;
		
				var $menuList = $(this).parent().find( 'ul' );

				if ( $menuList.is( ':hidden' )) {
					$(this).parent().addClass('open');
					$( '.nav li' ).removeClass('open');
					$( '.nav li ul' ).hide( 400 );
				}

		
				setTimeout(function(){
					isNavClickable = true;
				}, navClickableTreshold);
		
				e.preventDefault();
			}
		});
	});


	$(window)
	.on('scroll', function() {
		var scTop = $(window).scrollTop();

		if ( scTop >= $('.site-header').outerHeight(true) ){
			$('body').addClass('scrolled');
		} else {
			$('body').removeClass('scrolled');
		}
	})
	.on('load', function() {
		var $push = $('.site-footer').outerHeight(true);

		$('.wrapper').css('margin-bottom', -1 * $push);
		$('#push').height($push);
	});

	$(document).find('.author-post img').removeClass('avatar');
});