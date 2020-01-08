jQuery(document).ready(function($){
	var $omnivo_calendar = $(),	//allows to determine which calendar should be used
		omnivo_calendar_atts;	//value depends on the active calendar
	
	$(".omnivo_calendar_tabs_navigation a").click(function(event){
		var $this = $(this);
		$this.parent().parent().find("li").removeClass("ui-tabs-active");
		$this.parent().addClass("ui-tabs-active");
	});
	
	$(".omnivo_calendar_tabs").tabs({
		event: "change",
		show: true,
		create: function(event, ui){
			$omnivo_calendar = ui.panel.closest(".omnivo_calendar_wrapper");
			if($omnivo_calendar.find(".omnivo_calendar_tabs_navigation.all_filters").length
				&& window.location.href.indexOf("book-event-hour-")===-1)
			{
				if(ui.tab.length && $omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_categories_filter a[href='" + ui.tab[0].children[0].hash + "']").length)
					$omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_categories_filter a[href='" + ui.tab[0].children[0].hash + "']").parent().addClass("ui-tabs-active");
				else
					$omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_categories_filter li:first-child a").parent().addClass("ui-tabs-active");
				
				if(ui.tab.length && $omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_filter a[href='" + ui.tab[0].children[0].hash + "']").length)
					$omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_filter a[href='" + ui.tab[0].children[0].hash + "']").parent().addClass("ui-tabs-active");
				else
					$omnivo_calendar.find(".omnivo_calendar_tabs_navigation.events_filter li:first-child a").parent().addClass("ui-tabs-active");
			}
			
//			scroll to active calendar
			var param_fragment = escape_str($.param.fragment());
			if($('#' + param_fragment).closest(".omnivo_calendar_wrapper").length)
				$("html, body").animate({scrollTop: $('#' + param_fragment).closest(".omnivo_calendar_wrapper").offset().top-80}, 400);
		}
	});
	
	//browser history
	$(".omnivo_calendar_tabs .ui-tabs-nav a").click(function(){
		if($(this).attr("href").substr(0,4)!="http")
			$.bbq.pushState($(this).attr("href"));
		else
			window.location.href = $(this).attr("href");
	});
	
	//dropdown navigation
	$(".tabs_box_navigation").mouseover(function(){
		$(this).find("ul").removeClass("tabs_box_navigation_hidden");
	});
	
	$(".tabs_box_navigation a").click(function(event){
		if($.param.fragment()==$(this).attr("href").replace("#", "") || ($.param.fragment()=="" && $(this).attr("href").replace("#", "").substr(0, 10)=="all-events"))
			event.preventDefault();
		$(this).parent().parent().find(".selected").removeClass("selected");
		$(this).parent().addClass("selected");
		$(this).parent().parent().parent().children("label").text($(this).text());
		$(this).parent().parent().addClass("tabs_box_navigation_hidden");
	});
	
	$(".omnivo_calendar_tabs_navigation:not(.all_filter) a, .tabs_box_navigation:not(.all_filter) a").click(function(event){
		event.preventDefault();
		var $this = $(this),
			hash,
			newHref,
			sharpIdx,
			event_str,
			events_category_str,
			all_events_str;
		
		$omnivo_calendar = $this.closest(".omnivo_calendar_wrapper");
		all_events_str = "all-events" + (typeof($omnivo_calendar.attr("id"))!="undefined" ? "-" + $omnivo_calendar.attr("id") : "");
		
		hash = $this.attr("href");
		sharpIdx = (window.location.href.indexOf("#")!==-1 ? window.location.href.indexOf("#") : window.location.href.length);
		event_str = ($omnivo_calendar.find(".events_filter .selected a").length ? $omnivo_calendar.find(".events_filter .selected a").attr("href").replace("#", "") : ($omnivo_calendar.find(".events_filter .ui-tabs-active a").length ? $omnivo_calendar.find(".events_filter .ui-tabs-active a").attr("href").replace("#", "") : ""));
		events_category_str = ($omnivo_calendar.find(".events_categories_filter .selected a").length ? $omnivo_calendar.find(".events_categories_filter .selected a").attr("href").replace("#", "") : ($omnivo_calendar.find(".events_categories_filter .ui-tabs-active a").length ? $omnivo_calendar.find(".events_categories_filter .ui-tabs-active a").attr("href").replace("#", "") : ""));
		$omnivo_calendar.find(".omnivo_calendar_error_message").addClass("omnivo_calendar_hide");
		if(event_str!=="" && events_category_str!=="")
		{
			if((event_str!==all_events_str && events_category_str!==all_events_str && $omnivo_calendar.find("[id='" + event_str + "'][class*='omnivo_calendar-event-category-" + events_category_str.toLowerCase() +"']").length) || (events_category_str===all_events_str))
			{
				newHref = escape_str(window.location.href.substr(0, sharpIdx)) + decodeURIComponent("#" + event_str);
				if(window.location.href!=newHref)
					window.location.href = newHref;
			}
			else if(event_str===all_events_str && events_category_str!==all_events_str)
			{
				newHref = escape_str(window.location.href.substr(0, sharpIdx)) + decodeURIComponent("#" + events_category_str);
				if(window.location.href!=newHref)
					window.location.href = newHref;
			}
			else
			{
				var scrollTop = $(document).scrollTop();
				newHref = escape_str(window.location.href.substr(0, sharpIdx)) + "#";
				if(window.location.href!=newHref)
					window.location.href = newHref;
				$omnivo_calendar.find(".omnivo_calendar_tabs").tabs("option", "collapsible", true);
				$omnivo_calendar.find(".omnivo_calendar_tabs").tabs("option", "active", false);
				$omnivo_calendar.find(".omnivo_calendar_error_message").removeClass("omnivo_calendar_hide");
				$("html, body").scrollTop(scrollTop);
			}
		}
		else
		{
			newHref = escape_str(window.location.href.substr(0, sharpIdx)) + decodeURIComponent(hash);
			if(window.location.href!=newHref)
				window.location.href = newHref;
			//window.location.hash is causing issues on Safari, because of that
			//it's necessary to use window.location.href
		}			
	});
	
	//hashchange
	$(window).bind("hashchange", function(event){
		var param_fragment = escape_str($.param.fragment());
		//some browsers will have the URL fragment already encoded, 
		//while others will not, thus it's necessary to handle both cases.
	
		//URL fragment is already encoded:
		$(".tabs_box_navigation a[href='#" + param_fragment + "']").trigger("click");
		$(".omnivo_calendar_tabs .ui-tabs-nav [href='#" + param_fragment + "']").trigger("change");
		//URL fragment must be encoded:
		$(".tabs_box_navigation a[href='#" + encodeURIComponent(param_fragment) + "']").trigger("click");
		$(".omnivo_calendar_tabs .ui-tabs-nav [href='#" + encodeURIComponent(param_fragment) + "']").trigger("change");
	}).trigger("hashchange");
	
	//tooltip
	$(".omnivo_calendar_tooltip").bind("mouseover click", function(){
		var $this = $(this),
			$attach_to = $this,
			$tooltip_text,
			position,
			top,
			left;
			
		if($this.is(".event_container"))
			$attach_to = $this.parent();
		$tooltip_text = $this.children(".omnivo_calendar_tooltip_text");

		$tooltip_text.css("width", $this.outerWidth() + "px");
		$tooltip_text.css("height", $tooltip_text.height() + "px");
		
		if($('body').hasClass('rtl'))
		{
			//RTL MODE, TD is static
			position = $attach_to.position();
			top = position.top-$tooltip_text.innerHeight() + "px";
			left = position.left + "px";
		}
		else
		{
			//LTR MODE, TD is relative
			top = -($tooltip_text.parent().offset().top-$attach_to.offset().top+$tooltip_text.innerHeight()) + "px";
			left = "0px";
		}
		
		$tooltip_text.css({
			"top":  top,
			"left": left
		});
	});
	
	//Handle hover booking buttons in RTL mode
	$("body.rtl .booking_hover_buttons td.event").bind("mouseover click", function(){
		var $td = $(this),
			button_height = 50,	/* fixed value */
			height,
			width;
		
		if($td.hasClass("omnivo_calendar_single_event"))
		{
			height = Math.ceil($td[0].getBoundingClientRect().height);
			width = Math.ceil($td[0].getBoundingClientRect().width);
			$td.find(".event_hour_booking_wrapper.on_hover").css({
				'top': $td.position().top+height-button_height,
				'left': $td.position().left,
				//On FF elements may have fractional height/width,
				//we need to get the exact value and round it up.
				'width': width,
			});
		}
		else
		{
			$td.find(".event_container").each(function() {
				var $event_container = $(this),
					$booking_wrapper = $event_container.find(".event_hour_booking_wrapper.on_hover");
				
				height = Math.ceil($event_container[0].getBoundingClientRect().height);
				width = Math.ceil($event_container[0].getBoundingClientRect().width);
				$booking_wrapper.css({
					'top': $event_container.position().top+height-button_height,
					'left': $event_container.position().left,
					//On FF elements may have fractional height/width,
					//we need to get the exact value and round it up.
					'width': width,
				});
			});
		}
	});
	
	//upcoming events
	$(".omnivo_calendar_upcoming_events").each(function(){
		var self = $(this),
			autoscroll = 0,
			elementClasses = self.attr("class").split(" ");
		
		for(var i=0; i<elementClasses.length; i++)
		{
			if(elementClasses[i].indexOf("autoscroll-")!=-1)
				autoscroll = elementClasses[i].replace("autoscroll-", "");
		}
		self.carouFredSel({
			direction: "up",
			items: {
				visible: (self.children().length>2 ? 3 : self.children().length),
				height: "variable"
			},
			scroll: {
				items: 1,
				easing: "swing",
				pauseOnHover: true
			},
			prev: {button: self.next().children("#upcoming_event_prev")},
			next: {button: self.next().children("#upcoming_event_next")},
			auto: {
				play: (parseInt(autoscroll) ? true : false)
			}
		});
		
		self.find("li a.omnivo_calendar_upcoming_events_event_container, li>span").hover(function(){
			self.trigger("configuration", ["debug", false, true]);
		},
		function(){
			setTimeout(function(){
				self.trigger("configuration", ["debug", false, true]);
			}, 1);
		});
	});
	$(window).resize(function(){
		$(".omnivo_calendar_upcoming_events").trigger("configuration", ["debug", false, true]);
	});
	
	//show/hide event hours on mobile device
	$(document.body).on("click", ".omnivo_calendar.small .plus.box_header", function(event) {
		var $this = $(this),
			$list = $this.next("ul.omnivo_calendar_items_list");
		$list.slideDown(500);
		$this.removeClass("plus");
		$this.addClass("minus");
	});
	$(document.body).on("click", ".omnivo_calendar.small .minus.box_header", function(event) {
		var $this = $(this),
			$list = $this.next("ul.omnivo_calendar_items_list");
		$list.slideUp(500, function() {
			$this.removeClass("minus");
			$this.addClass("plus");
		});
	});
	
	if($(".omnivo_calendar_booking").length && $(".omnivo_calendar_booking_overlay").length)
	{
		$(".omnivo_calendar_booking_overlay").slice(1).remove();
		$(".omnivo_calendar_booking_overlay").appendTo("body");
		$(".omnivo_calendar_booking").slice(1).remove();
		$(".omnivo_calendar_booking").appendTo("body");
	}
	
	if($(".omnivo_calendar_booking").length && in_iframe())
	{
		$(".omnivo_calendar_booking").addClass("in_iframe");
	}

	$(document.body).on("click", ".event_hour_booking:not(.redirect)", get_event_hour_details);


	$(document.body).on("click", ".omnivo_calendar_booking_overlay", close_booking_popup);
	//don't close popup if scroll occurs
	var touchmoved;
	$(document.body).on("touchend", ".omnivo_calendar_booking_overlay", function(event) {
		if(touchmoved != true) 
			close_booking_popup();
	}).on("touchmove", ".omnivo_calendar_booking_overlay", function(event) {
		touchmoved = true;
	}).on("touchstart", ".omnivo_calendar_booking_overlay", function(event) {
		touchmoved = false;
	});


	function get_event_hour_details(event)
	{
		event.preventDefault();
		var $this = $(this),
			$booking_popup = $('.omnivo_calendar_booking'),
			$booking_popup_message = $booking_popup.find('.omnivo_calendar_booking_message'),
			$booking_popup_preloader = $booking_popup.find('.omnivo_calendar_preloader'),
			event_hour_id = $this.attr('data-event-hour-id'),
			redirect_url = window.location.href;
			
		$omnivo_calendar = $this.closest('.omnivo_calendar_wrapper');
		omnivo_calendar_atts = $.parseJSON($omnivo_calendar.find('.omnivo_calendar_atts').val());
		
		if(redirect_url.indexOf('#')===-1)
			redirect_url = redirect_url + '#book-event-hour-' + event_hour_id;
		else
			redirect_url = redirect_url.substr(0, redirect_url.indexOf('#')) + '#book-event-hour-' + event_hour_id;
		
		if($this.hasClass('unavailable') || $this.hasClass('booked'))
			return;
		
		$booking_popup_message.html('');
		open_booking_popup(event);
		resize_booking_popup();
		$booking_popup_message.attr('data-event-hour-id', event_hour_id);
		
		$.post(omnivo_calendar_config.ajaxurl,
			{
				action: 'omnivo_calendar_ajax_event_hour_details',
				redirect_url: redirect_url,
				event_hour_id: event_hour_id,
				atts: omnivo_calendar_atts,
			},
			function(result){
				$booking_popup_preloader.addClass('omnivo_calendar_hide');
				
				result = parse_server_response(result);
				if(typeof(result.msg!=='undefined'))
				{
					if(!result.error)
						$booking_popup_message.html(result.msg);
					else
					{
						$booking_popup_message.html('<p>' + result.msg + '</p><div><a href="#" class="omnivo_calendar_btn cancel">' + omnivo_calendar_atts.cancel_popup_label + '</a></div>');
					}
					resize_booking_popup();
				}
			},
			"html"
		);
	}
	
	$(document.body).on('click', '.omnivo_calendar_booking .omnivo_calendar_btn.book', create_event_hour_booking);
	
	function create_event_hour_booking(event)
	{
		event.preventDefault();
		var $this = $(this),
			$booking_popup = $('.omnivo_calendar_booking'),
			$booking_popup_message = $booking_popup.find('.omnivo_calendar_booking_message'),
			$booking_popup_preloader = $booking_popup.find('.omnivo_calendar_preloader'),
			slots_number = $booking_popup.find('.omnivo_calendar_slots_number').val(),
			guest_name = $booking_popup.find('.omnivo_calendar_guest_name').val(),
			guest_email = $booking_popup.find('.omnivo_calendar_guest_email').val(),
			guest_phone = $booking_popup.find('.omnivo_calendar_guest_phone').val(),
			guest_message = $booking_popup.find('.omnivo_calendar_guest_message').val(),
			terms_checkbox = $booking_popup.find('.tt_terms_checkbox').is(':checked'),
			event_hour_id = $booking_popup_message.attr('data-event-hour-id');
		
		$this.qtip('destroy').removeClass('omnivo_calendar-qtip2');

		$booking_popup_message.addClass('omnivo_calendar_hide');
		$booking_popup_preloader.removeClass('omnivo_calendar_hide');
		resize_booking_popup();
		
		$.post(omnivo_calendar_config.ajaxurl,
			{
				action: 'omnivo_calendar_ajax_event_hour_booking',
				event_hour_id: event_hour_id,
				atts: omnivo_calendar_atts,
				slots_number: slots_number,
				guest_name: guest_name,
				guest_email: guest_email,
				guest_phone: guest_phone,
				guest_message: guest_message,
				terms_checkbox: terms_checkbox,
			},
			function(result){
				$booking_popup_preloader.addClass('omnivo_calendar_hide');
				$booking_popup_message.removeClass('omnivo_calendar_hide');
				resize_booking_popup();
				result = parse_server_response(result);
				if(typeof(result.msg)!=='undefined')
				{
					if(!result.error)
					{
						$booking_popup_message.html(result.msg);
						resize_booking_popup();

						$omnivo_calendar.find(".event_hour_booking.id-" + event_hour_id).parent().replaceWith(result.booking_button);
						
						if(typeof(result.available_slots_label)!=='undefined')
						{
							$omnivo_calendar.find('.available_slots.id-' + event_hour_id).html(result.available_slots_label);
						}
					}
					else
					{
						$this.addClass('omnivo_calendar-qtip2').qtip(
						{
							style:
							{
								classes: 'ui-tooltip-error omnivo_calendar-qtip2'
							},
							content:
							{ 
								text: result.msg
							},
							position:
							{ 
								my: 'bottom center',
								at: 'top center',
								target: $this,
							},
						});
						
						//show qtip after animation completes
						setTimeout(function() {
							$this.qtip('show');
						}, 200);
						
					}
				}
			},
			"html"
		);
	}
	
	function handle_orinentation_change()
	{
		var no_change_count_to_end = 100,
			no_end_timeout = 1000;
		
		(function () {
			var orientationchange_interval,
				orientationchange_timeout,
				end_timming,
				curr_inner_width,
				curr_inner_height,
				last_inner_width,
				last_inner_height,
				no_change_count;

			end_timming = function ()
			{
				clearInterval(orientationchange_interval);
				clearTimeout(orientationchange_timeout);

				orientationchange_interval = null;
				orientationchange_timeout = null;

				//orientationchange has ended
				resize_booking_popup();
			};

			orientationchange_interval = setInterval(function ()
			{
				curr_inner_width = $(window).width();
				curr_inner_height = $(window).height();
				if (curr_inner_width === last_inner_width && curr_inner_height === last_inner_height)
				{
					no_change_count++;

					if (no_change_count === no_change_count_to_end)
					{
						end_timming();
					}
				}
				else
				{
					last_inner_width = curr_inner_width;
					last_inner_height = curr_inner_height;
					no_change_count = 0;
				}
			});
			orientationchange_timeout = setTimeout(function () {
				end_timming();
			}, no_end_timeout);
		})();
	}
	
	$(document.body).on('click', '.omnivo_calendar_guest_option a', function(event) {
		event.preventDefault();
		var $booking = $('.omnivo_calendar_booking'),
			$booking_form_user = $booking.find('.omnivo_calendar_booking_form.user'),
			$booking_form_guest = $booking.find('.omnivo_calendar_booking_form.guest');
		
		$booking_form_user.addClass('omnivo_calendar_hide');
		$booking_form_guest.removeClass('omnivo_calendar_hide');
		
		$(this).parent().addClass('omnivo_calendar_hide')
			.next('.omnivo_calendar_login_option').removeClass('omnivo_calendar_hide');
		$booking.find('.omnivo_calendar_btn.login').addClass('omnivo_calendar_hide');
		$booking.find('.omnivo_calendar_btn.book').removeClass('omnivo_calendar_hide');
		resize_booking_popup();
	});
	
	$(document.body).on('click', '.omnivo_calendar_slots_number_plus, .omnivo_calendar_slots_number_minus', function(event) {
		event.preventDefault();
		var $booking = $('.omnivo_calendar_booking'),
			$booking_form = $booking.find('.omnivo_calendar_booking_form'),
			$slots_number = $booking_form.find('.omnivo_calendar_slots_number'),
			slots_number = parseInt($slots_number.val()),
			min = parseInt($slots_number.attr('min')),
			max = parseInt($slots_number.attr('max')),
			step = parseInt($slots_number.attr('step'));
		
		if(isNaN(slots_number))
			slots_number = 1;
		
		if($(this).hasClass('omnivo_calendar_slots_number_plus'))
		{
			if(slots_number<max)
				slots_number += step;
			else
				slots_number = max;
		}
		
		if($(this).hasClass('omnivo_calendar_slots_number_minus'))
		{
			if(slots_number>min)
				slots_number -= step;
			else
				slots_number = min;
		}
		
		$slots_number.val(slots_number);
	});
	
	$(document.body).on('click', '.omnivo_calendar_btn.cancel, .omnivo_calendar_btn.continue', function(event) {
		event.preventDefault();
		close_booking_popup(event);
	});
	
	
	function calc_popup_params()
	{
		//init vars
		var $booking_popup = $('.omnivo_calendar_booking');
		var booking_popup_margin_left = parseInt($booking_popup.css('margin-left'));
		
		var booking_popup_width_new;
		var booking_popup_width = $booking_popup.outerWidth();
		var booking_popup_width_auto = $booking_popup.css('width', '').outerWidth();		
		$booking_popup.css('width', booking_popup_width)

		var window_height = ($booking_popup.hasClass('in_iframe') ? $(parent.window).height() : $(window).height());
		var window_width = ($booking_popup.hasClass('in_iframe') ? $(parent.window).width() : $(window).width());
		var offset_top = ($(document).scrollTop()>=$(parent.document).scrollTop() ? $(document).scrollTop() : $(parent.document).scrollTop());
		
		
		//calc width
		if((booking_popup_width_auto+booking_popup_margin_left*2)>=window_width)
			booking_popup_width_new = window_width-booking_popup_margin_left*2;
		else
			booking_popup_width_new = booking_popup_width_auto;
		
		//calc left position
		var booking_popup_left = (window_width-booking_popup_width_new-booking_popup_margin_left*2)/2;
		
		//calc height
		var booking_popup_height = $booking_popup.outerHeight();
		var booking_popup_height_new = $booking_popup.css({
			'width': booking_popup_width_new,
			'height': '',
		}).outerHeight();
		$booking_popup.css({
			'width': booking_popup_width,
			'height': booking_popup_height,
		});

		//calc top position
		var booking_popup_top = offset_top;
		if(booking_popup_height_new<window_height)
			booking_popup_top += (window_height-booking_popup_height_new)/2;
		
		var result = {
			'top': booking_popup_top,
			'left': booking_popup_left,
			'width': booking_popup_width_new,
			'height': booking_popup_height_new,
		};
		return result;
	}
	
	function open_booking_popup(event)
	{
		var	
			$booking_popup = $(".omnivo_calendar_booking"),
			$booking_overlay = $(".omnivo_calendar_booking_overlay"),
			$booking_popup_preloader = $booking_popup.find('.omnivo_calendar_preloader');
		
		$booking_overlay.removeClass("omnivo_calendar_hide");
		$booking_popup.removeClass("omnivo_calendar_hide");
		$booking_popup_preloader.removeClass('omnivo_calendar_hide');
		
		
		$booking_overlay.css({
			'height' : $(document).height(),
		});
		
		var popup_params = calc_popup_params();
		
		$booking_popup.css({
			'top': popup_params.top,
			'left': popup_params.left,
			'width': popup_params.width,
			'height': popup_params.height,
		});
	}
	
	function close_booking_popup()
	{
		var $booking_popup = $(".omnivo_calendar_booking"),
			$booking_overlay = $(".omnivo_calendar_booking_overlay");
		
		$booking_popup.find('.omnivo_calendar-qtip2').qtip('destroy').removeClass('omnivo_calendar-qtip2');
		$booking_popup.addClass("omnivo_calendar_hide");
		$booking_overlay.addClass("omnivo_calendar_hide");

		$booking_popup.css({
			"top": "",
			"left": "",
			"width": "",
			"height": "",
		});
		if(window.location.href.indexOf("book-event-hour-")!==-1)
		{
			window.location.href = escape_str(window.location.href.substr(0, window.location.href.indexOf("#"))) + "#";
		}
		return false;
	}
	
	function resize_booking_popup()
	{
		var	$booking_popup = $('.omnivo_calendar_booking'),
			$booking_overlay = $(".omnivo_calendar_booking_overlay");
		
		if($booking_popup.hasClass('omnivo_calendar_hide'))
			return;
		
		$booking_overlay.css({
			'height' : $(document).height(),
		});
		
		var popup_params = calc_popup_params();
		$booking_popup.stop(false, true).animate({
			'top': popup_params.top,
			'left': popup_params.left,
			'width': popup_params.width,
			'height': popup_params.height,
		}, 200);
	}
	
	//IIFE: wait till resize event ends
	(function() {
		var last_resize,
			timeout = false,
			delta = 200;
			
		$(window).resize(function() {
			last_resize = new Date();
			if(timeout === false)
			{
				timeout = true;
				setTimeout(resize_end, delta);
			}
		});
		
		function resize_end()
		{
			if(new Date()-last_resize<delta)
				setTimeout(resize_end, delta);
			else
			{
				timeout = false;
				$(window).trigger('resize_end');	//trigger custom event
			}
		}
	})();
	$(window).on('resize_end', resize_booking_popup);	//handle custom event
	
	window.addEventListener('orientationchange', handle_orinentation_change);
	
	if(window.location.href.indexOf('book-event-hour-')!==-1)
	{ 
		var event_hour_id = window.location.href.substr(window.location.href.indexOf('book-event-hour-')+16),
			$booking_link = $('a.event_hour_booking.id-' + event_hour_id).eq(0);
		if($booking_link.length)
		{
			$('html, body').animate({scrollTop: $booking_link.offset().top-80}, 400);
			$booking_link.click();
		}
	}
	
	function escape_str($text)
	{
		return $('<div/>').text($text).html();
	}
	
	$('form.omnivo_calendar_generate_pdf').on('submit', function(event) {
		var $this = $(this),
			$omnivo_calendar_copy,
			omnivo_calendar_html;
		
		$omnivo_calendar = $this.closest('.omnivo_calendar_wrapper');
		
		$omnivo_calendar_copy = $omnivo_calendar.find('.omnivo_calendar_tabs div.ui-tabs-panel:visible .omnivo_calendar.small').clone();
		$omnivo_calendar_copy.find('*').attr('style', '');	//helps to remove the colors
		omnivo_calendar_html = $omnivo_calendar_copy[0].outerHTML;
		if($('body').hasClass('rtl'))
			omnivo_calendar_html = "<div class='rtl'>" + omnivo_calendar_html + "</div>";
		$this.find("textarea[name='omnivo_calendar_pdf_html_content']").val(omnivo_calendar_html);
		return true;
	});
	
	function in_iframe()
	{
		try {
			return window.self !== window.top;
		} catch (e) {
			return true;
		}
	}
	
	function parse_server_response(response)
	{
		var indexStart = response.indexOf('omnivo_calendar_start')+15,
			indexEnd = response.indexOf('omnivo_calendar_end')-indexStart;
		return $.parseJSON(response.substr(indexStart, indexEnd));
	}
	
});
