/*
Name: 			Theme Base
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version:	13.0.0
*/

// Theme
window.theme = {};

// Theme Common Functions
window.theme.fn = {

	getOptions(opts) {

		if (typeof(opts) == 'object') {

			return opts;

		} else if (typeof(opts) == 'string') {

			try {
				return JSON.parse(opts.replace(/'/g, '"').replace(';', ''));
			} catch (e) {
				return {};
			}

		} else {

			return {};

		}

	},

	execPluginFunction(functionName, context) {
		const args = Array.prototype.slice.call(arguments, 2);
		const namespaces = functionName.split(".");
		const func = namespaces.pop();

		for (let i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		}

		return context[func](...args);
	},

	intObs(selector, functionName, intObsOptions, alwaysObserve) {
		const $el = document.querySelectorAll(selector);
		let intersectionObserverOptions = {
			rootMargin: '0px 0px 200px 0px'
		};

		if (Object.keys(intObsOptions).length) {
			intersectionObserverOptions = $.extend(intersectionObserverOptions, intObsOptions);
		}

		const observer = new IntersectionObserver(entries => {
            for (const entry of entries) {
                if (entry.intersectionRatio > 0) {
					if (typeof functionName === 'string') {
						const func = Function('return ' + functionName)();
					} else {
						const callback = functionName;

						callback.call($(entry.target));
					}

					// Unobserve
					if (!alwaysObserve) {
						observer.unobserve(entry.target);
					}

				}
            }
        }, intersectionObserverOptions);

		$($el).each(function() {
			observer.observe($(this)[0]);
		});
	},

	intObsInit(selector, functionName) {
		const $el = document.querySelectorAll(selector);
		const intersectionObserverOptions = {
			rootMargin: '200px'
		};

		const observer = new IntersectionObserver(entries => {
            for (const entry of entries) {
                if (entry.intersectionRatio > 0) {
                    const $this = $(entry.target);
                    let opts;

                    const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                    if (pluginOptions)
						opts = pluginOptions;

                    theme.fn.execPluginFunction(functionName, $this, opts);

                    // Unobserve
                    observer.unobserve(entry.target);
                }
            }
        }, intersectionObserverOptions);

		$($el).each(function() {
			observer.observe($(this)[0]);
		});
	},

	dynIntObsInit(selector, functionName, pluginDefaults) {
		const $el = document.querySelectorAll(selector);

		$($el).each(function() {
            const $this = $(this);
            let opts;

            const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
            if (pluginOptions)
				opts = pluginOptions;

            const mergedPluginDefaults = theme.fn.mergeOptions(pluginDefaults, opts);

            const intersectionObserverOptions = {
				rootMargin: theme.fn.getRootMargin(functionName, mergedPluginDefaults),
				threshold: 0
			};

            if (!mergedPluginDefaults.forceInit) {

				const observer = new IntersectionObserver(entries => {
                    for (const entry of entries) {
                        if (entry.intersectionRatio > 0) {
							theme.fn.execPluginFunction(functionName, $this, mergedPluginDefaults);

							// Unobserve
							observer.unobserve(entry.target);
						}
                    }
                }, intersectionObserverOptions);

				observer.observe($this[0]);

			} else {
				theme.fn.execPluginFunction(functionName, $this, mergedPluginDefaults);
			}
        });
	},

	getRootMargin(plugin, {accY}) {
		switch (plugin) {
			case 'themePluginCounter':
				return accY ? `0px 0px ${accY}px 0px` : '0px 0px 200px 0px';
				break;

			case 'themePluginAnimate':
				return accY ? `0px 0px ${accY}px 0px` : '0px 0px 200px 0px';
				break;

			case 'themePluginIcon':
				return accY ? `0px 0px ${accY}px 0px` : '0px 0px 200px 0px';
				break;

			case 'themePluginRandomImages':
				return accY ? `0px 0px ${accY}px 0px` : '0px 0px 200px 0px';
				break;

			default:
				return '0px 0px 200px 0px';
				break;
		}
	},

	mergeOptions(obj1, obj2) {
		const obj3 = {};

		for (var attrname in obj1) {
			obj3[attrname] = obj1[attrname];
		}
		for (var attrname in obj2) {
			obj3[attrname] = obj2[attrname];
		}

		return obj3;
	},

	execOnceTroughEvent($el, event, callback) {
		const self = this, dataName = self.formatDataName(event);

		$($el).on(event, function() {
			if (!$(this).data(dataName)) {

				// Exec Callback Function
				callback.call($(this));

				// Add data name 
				$(this).data(dataName, true);

				// Unbind event
				$(this).off(event);
			}
		});

		return this;
	},

	execOnceTroughWindowEvent($el, event, callback) {
		const self = this, dataName = self.formatDataName(event);

		$($el).on(event, function() {
			if (!$(this).data(dataName)) {

				// Exec Callback Function
				callback();

				// Add data name 
				$(this).data(dataName, true);

				// Unbind event
				$(this).off(event);
			}
		});

		return this;
	},

	formatDataName(name) {
		name = name.replace('.', '');
		return name;
	},

	isElementInView($el) {
		const rect = $el[0].getBoundingClientRect();

		return (
			rect.top <= (window.innerHeight / 3)
		);
	},

	getScripts(arr, path) {
		const _arr = $.map(arr, scr => $.getScript((path || "") + scr));

		_arr.push($.Deferred(({resolve}) => {
			$(resolve);
		}));

		return $.when(..._arr);
	},

	showErrorMessage(title, content) {

		if ($('html').hasClass('disable-local-warning')) {
			return;
		}

		$('.modalThemeErrorMessage').remove();
		$('body').append('<div class="modal fade" id="modalThemeErrorMessage"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">' + title + '</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">' + content + '</div><div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button></div></div></div></div>');

		var modalThemeErrorMessage = document.getElementById('modalThemeErrorMessage');
		var modalThemeErrorMessage = bootstrap.Modal.getOrCreateInstance(modalThemeErrorMessage);
		modalThemeErrorMessage.show();

	}

};

(((theme = {}, $) => {
	/*
	Local Environment Warning
	*/
	try {
		if ("file://" === location.origin) {
			if ($('[data-icon]').length || $('iframe').length) {

				theme.fn.showErrorMessage('Local Environment Warning', 'SVG Objects, Icons, YouTube and Vimeo Videos might not show correctly on local environment. For better result, please preview on a server.<br><br><a target="_blank" href="https://www.okler.net/forums/topic/how-to-disable-local-environment-warning/" class="fw-semibold"><i class="fa-solid fa-link"></i> How to Disable Local Environment Warning</a>');

			}
		}
	} catch (e) {}

	/*
	Browser Selector
	*/
	$.extend({

		browserSelector() {

			// jQuery.browser.mobile (http://detectmobilebrowser.com/)
			(a => {(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);

			// Touch
			const hasTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;

			const u = navigator.userAgent, ua = u.toLowerCase(), is = t => ua.includes(t), g = 'gecko', w = 'webkit', s = 'safari', o = 'opera', h = document.documentElement, b = [(!(/opera|webtv/i.test(ua)) && /msie\s(\d)/.test(ua)) ? (`ie ie${parseFloat(navigator.appVersion.split("MSIE")[1])}`) : is('firefox/2') ? `${g} ff2` : is('firefox/3.5') ? `${g} ff3 ff3_5` : is('firefox/3') ? `${g} ff3` : is('gecko/') ? g : is('opera') ? o + (/version\/(\d+)/.test(ua) ? ` ${o}${RegExp.jQuery1}` : (/opera(\s|\/)(\d+)/.test(ua) ? ` ${o}${RegExp.jQuery2}` : '')) : is('konqueror') ? 'konqueror' : is('chrome') ? `${w} chrome` : is('iron') ? `${w} iron` : is('applewebkit/') ? `${w} ${s}${/version\/(\d+)/.test(ua) ? ` ${s}${RegExp.jQuery1}` : ''}` : is('mozilla/') ? g : '', is('j2me') ? 'mobile' : is('iphone') ? 'iphone' : is('ipod') ? 'ipod' : is('mac') ? 'mac' : is('darwin') ? 'mac' : is('webtv') ? 'webtv' : is('win') ? 'win' : is('freebsd') ? 'freebsd' : (is('x11') || is('linux')) ? 'linux' : '', 'js'];

			c = b.join(' ');

			if ($.browser.mobile) {
				c += ' mobile';
			}

			if (hasTouch) {
				c += ' touch';
			}

			h.className += ` ${c}`;

			// Edge Detect
			const isEdge = /Edge/.test(navigator.userAgent);

			if(isEdge) {
				$('html').removeClass('chrome').addClass('edge');
			}

			// Dark and Boxed Compatibility
			if($('body').hasClass('dark')) {
				$('html').addClass('dark');
			}

			if($('body').hasClass('boxed')) {
				$('html').addClass('boxed');
			}

		}

	});

	$.browserSelector();

	/*
	Browser Workarounds
	*/
	if (/iPad|iPhone|iPod/.test(navigator.platform)) {

		// iPad/Iphone/iPod Hover Workaround
		$(document).ready($ => {
			$('.thumb-info').attr('onclick', 'return true');
		});
	}

	/*
	Lazy Load Bacground Images
	*/

	// Check for IntersectionObserver support
	if ('IntersectionObserver' in window) {
		document.addEventListener("DOMContentLoaded", function() {

			function handleIntersection(entries) {
				entries.map((entry) => {
					if (entry.isIntersecting) {
						// Item has crossed our observation
						// threshold - load src from data-src
						entry.target.style.backgroundImage = "url('" + entry.target.dataset.bgSrc + "')";
						// Job done for this item - no need to watch it!
						observer.unobserve(entry.target);
					}
				});
			}

			const lazyLoadElements = document.querySelectorAll('.lazyload');
			const observer = new IntersectionObserver(
				handleIntersection, {
					rootMargin: "100px"
				}
			);
			lazyLoadElements.forEach(lazyLoadEl => observer.observe(lazyLoadEl));
		});
	} else {
		// No interaction support? Load all background images automatically
		const lazyLoadElements = document.querySelectorAll('.lazyload');
		lazyLoadElements.forEach(lazyLoadEl => {
			lazyLoadEl.style.backgroundImage = "url('" + lazyLoadEl.dataset.bgSrc + "')";
		});
	}

	/*
	Tabs
	*/
	if( $('a[data-bs-toggle="tab"]').length ) {
		$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function({target}) {
			const $tabPane = $($(target).attr('href'));

			// Carousel Refresh
			if($tabPane.length) {
				$tabPane.find('.owl-carousel').trigger('refresh.owl.carousel');
			}

			// Change Active Class
			$(this).parents('.nav-tabs').find('.active').removeClass('active');
			$(this).addClass('active').parent().addClass('active');
		});	

		if( window.location.hash ) {
			$(window).on('load', () => {
				if( window.location.hash !== '*' && $( window.location.hash ).get(0) ) {
					new bootstrap.Tab( $('a.nav-link[href="'+ window.location.hash +'"]:not([data-hash])')[0] ).show();
				}
			});
		}
	}

	/*
	On Load Scroll
	*/
	if( !$('html').hasClass('disable-onload-scroll') && window.location.hash && !['#*'].includes( window.location.hash ) ) {

		window.scrollTo(0, 0);

		$(window).on('load', () => {
			setTimeout(() => {
				const target = window.location.hash;
				let offset = ( $(window).width() < 768 ) ? 180 : 90;

				if (!$(target).length) {
					return;
				}

				if ( $("a[href$='" + window.location.hash + "']").is('[data-hash-offset]') ) {
					offset = parseInt( $("a[href$='" + window.location.hash + "']").first().attr('data-hash-offset') );
				} else if ( $("html").is('[data-hash-offset]') ) {
					offset = parseInt( $("html").attr('data-hash-offset') );
				}

				if (isNaN(offset)) {
					offset = 0;
				}

				$('body').addClass('scrolling');

				$('html, body').animate({
					scrollTop: $(target).offset().top - offset
				}, 600, 'easeOutQuad', () => {
					$('body').removeClass('scrolling');
				});
			}, 1);
		});
	}

	/*
	* Text Rotator
	*/
	$.fn.extend({
		textRotator(options) {

			const defaults = {
				fadeSpeed: 500,
				pauseSpeed: 100,
				child: null
			};

			var options = $.extend(defaults, options);

			return this.each(function() {
				const o = options;
				const obj = $(this);
				const items = $(obj.children(), obj);
				items.each(function() {
					$(this).hide();
				})
				if (!o.child) {
					var next = $(obj).children(':first');
				} else {
					var next = o.child;
				}
				$(next).fadeIn(o.fadeSpeed, () => {
					$(next).delay(o.pauseSpeed).fadeOut(o.fadeSpeed, function() {
						let next = $(this).next();
						if (next.length == 0) {
							next = $(obj).children(':first');
						}
						$(obj).textRotator({
							child: next,
							fadeSpeed: o.fadeSpeed,
							pauseSpeed: o.pauseSpeed
						});
					})
				});
			});
		}
	});

	/*
	* Notice Top bar
	*/
	const $noticeTopBar = {
		$wrapper: $('.notice-top-bar'),
		$closeBtn: $('.notice-top-bar-close'),
		$header: $('#header'),
		$body: $('.body'),
		init() {
			const self = this;

			if( !$.cookie('portoNoticeTopBarClose') ) {
				self
					.build()
					.events();
			} else {
				self.$wrapper.parent().prepend( '<!-- Notice Top Bar removed by cookie -->' );
				self.$wrapper.remove();
			}

			return this;
		},
		build() {
			const self = this;

			$(window).on('load', () => {
				setTimeout(() => {
					self.$body.css({
						'margin-top': self.$wrapper.outerHeight(),
						'transition': 'ease margin 300ms'
					});

					$('#noticeTopBarContent').textRotator({
						fadeSpeed: 500,
						pauseSpeed: 5000
					});

					if( ['absolute', 'fixed'].includes( self.$header.css('position') ) ) {
						self.$header.css({
							'top': self.$wrapper.outerHeight(),
							'transition': 'ease top 300ms'
						});
					}

					$(window).trigger('notice.top.bar.opened');

				}, 1000);
			});

			return this;
		},
		events() {
			const self = this;

			self.$closeBtn.on('click', e => {
				e.preventDefault();

				self.$body.animate({
					'margin-top': 0,
				}, 300, () => {
					self.$wrapper.remove();
					self.saveCookie();
				});

				if( ['absolute', 'fixed'].includes( self.$header.css('position') ) ) {
					self.$header.animate({
						top: 0
					}, 300);
				}

				// When header has shrink effect
				if( self.$header.hasClass('header-effect-shrink') ) {
					self.$header.find('.header-body').animate({
						top: 0
					}, 300);
				}

				$(window).trigger('notice.top.bar.closed');
			});

			return this;
		},
		checkCookie() {
			const self = this;

			if( $.cookie('portoNoticeTopBarClose') ) {
				return true;
			} else {
				return false;
			}

			return this;
		},
		saveCookie() {
			const self = this;

			$.cookie('portoNoticeTopBarClose', true);

			return this;
		}
	};

	if( $('.notice-top-bar').length ) {
		$noticeTopBar.init();
	}

	/*
	* Image Hotspots
	*/
	if( $('.image-hotspot').length ) {
		$('.image-hotspot')
			.append('<span class="ring"></span>')
			.append('<span class="circle"></span>');
	}

	/*
	* Reading Progress
	*/
	if( $('.progress-reading').length ) {

		function updateScrollProgress() {
			const pixels = $(document).scrollTop();
				pageHeight = $(document).height() - $(window).height()
				progress = 100 * pixels / pageHeight;

			$('.progress-reading .progress-bar').width(parseInt(progress) + "%");
		}

		$(document).on('scroll ready', () => {
			updateScrollProgress();
		});

		$(document).ready(() => {
			$(window).afterResize(() => {
				updateScrollProgress();
			});
		});

	}

	/*
	* Page Transition
	*/
	if( $('body[data-plugin-page-transition]').length ) {
		
		let link_click = false;

		$(document).on('click', 'a', function(e){
			link_click = $(this);
		});

		$(window).on("beforeunload", e => {
			if( typeof link_click === 'object' ) {
				const href = link_click.attr('href');

				if( href.indexOf('mailto:') != 0 && href.indexOf('tel:') != 0 && !link_click.data('rm-from-transition') ) {
					$('body').addClass('page-transition-active');
				}
			}
		});

		$(window).on("pageshow", ({persisted, originalEvent}) => {
			if( persisted || originalEvent.persisted) {
				if( $('html').hasClass('safari') ) {
					window.location.reload();
				}
				
				$('body').removeClass('page-transition-active');
			}
		});
	}

	/*
	* Clone Element
	*/
	if( $('[data-clone-element]').length ) {

		$('[data-clone-element]').each(function() {

			const $el = $(this), content = $el.html(), qty = $el.attr('data-clone-element');

			for (let i = 0; i < qty; i++) {
				$el.html($el.html() + content);
			}

		});

	}

	if( $('[data-clone-element-to]').length ) {

		$('[data-clone-element-to]').each(function() {

			const $el = $(this);
			const content = $el.html();
			const $to = $($el.attr('data-clone-element-to'));

			$to.html($to.html() + content);

		});

	}

	/*
	* Thumb Info Floating Caption
	*/
	$('.thumb-info-floating-caption').each(function() {

		$(this)
			.addClass('thumb-info-floating-element-wrapper')
			.append( '<span class="thumb-info-floating-element thumb-info-floating-caption-title d-none">'+ $(this).data('title') +'</span>' );

		if( $(this).data('type') ) {
			$('.thumb-info-floating-caption-title', $(this))
				.append( '<div class="thumb-info-floating-caption-type">'+ $(this).data('type') +'</div>' )
				.css({
					'padding-bottom' : 22
				});
		}

		if( $(this).hasClass('thumb-info-floating-caption-clean') ) {
			$('.thumb-info-floating-element', $(this)).addClass('bg-transparent');
		}

	});

	/*
	* Thumb Info Floating Element
	*/
	if( $('.thumb-info-floating-element-wrapper').length ) {

		if (typeof gsap !== 'undefined') {

			$('.thumb-info-floating-element-wrapper').on('mouseenter', function({clientX, clientY}) {
				
				if(!$(this).data('offset')) {
					$(this).data('offset', 0);
				}

				const offset = parseInt($(this).data('offset'));

				$('.thumb-info-floating-element-clone').remove();

				$('.thumb-info-floating-element', $(this)).clone().addClass('thumb-info-floating-element-clone p-fixed p-events-none').attr('style', 'transform: scale(0.1);').removeClass('d-none').appendTo('body');

				$('.thumb-info-floating-element-clone').css({
					left: clientX + (offset),
					top: clientY + (offset)
				}).fadeIn(300);

				gsap.to('.thumb-info-floating-element-clone', 0.5, {
					css: {
						scaleX: 1,
						scaleY: 1
					}
				});

				$(document).off('mousemove').on('mousemove', ({clientX, clientY}) => {

					gsap.to('.thumb-info-floating-element-clone', 0.5, {
						css: {
							left: clientX + (offset),
							top: clientY + (offset)
						}
					});

				});

			}).on('mouseout', () => {

				gsap.to('.thumb-info-floating-element-clone', 0.5, {
					css: {
						scaleX: 0.5,
						scaleY: 0.5,
						opacity: 0
					}
				});

			});

		} else {
			theme.fn.showErrorMessage('Failed to Load File', 'Failed to load: GSAP - Include the following file(s): (vendor/gsap/gsap.min.js)');
		}

	}

	/*
	* Thumb Info Direction Aware
	*/
	$(window).on('load', () => {
		$('.thumb-info-wrapper-direction-aware').each( function() {
			$(this).hoverdir({
				speed : 300,
				easing : 'ease',
				hoverDelay : 0,
				inverse : false,
				hoverElem: '.thumb-info-wrapper-overlay'
			});
		});
	});

	/*
	* Thumb Info Container Full
	*/
	$('.thumb-info-container-full-img').each(function() {

		const $container = $(this);

		$('[data-full-width-img-src]', $container).each(function() {
			const uniqueId = 'img' + Math.floor(Math.random() * 10000);
			$(this).attr('data-rel', uniqueId);

			$container.append('<div style="background-image: url(' + $(this).attr('data-full-width-img-src') + ');" id="' + uniqueId + '" class="thumb-info-container-full-img-large opacity-0"></div>');
		});

		$('.thumb-info', $container).on('mouseenter', function(e){
			$('.thumb-info-container-full-img-large').removeClass('active');
			$('#' + $(this).attr('data-rel')).addClass('active');
		});

	});

	/*
	* Toggle Text Click
	*/
	$('[data-toggle-text-click]').on('click', function () {
		$(this).text(function(i, text){
			return text === $(this).attr('data-toggle-text-click') ? $(this).attr('data-toggle-text-click-alt') : $(this).attr('data-toggle-text-click');
		});
	});

	/*
	* Toggle Class
	*/
	$('[data-toggle-class]').on('click', function (e) {
		e.preventDefault();

		$(this).toggleClass( $(this).data('toggle-class') );
	});

	/*
	* Shape Divider Aspect Ratio
	*/
	if( $('.shape-divider').length ) {
		aspectRatioSVG();
		$(window).on('resize', () => {
			aspectRatioSVG();
		});
	}

	/*
	* Shape Divider Animated
	*/
	if( $('.shape-divider-horizontal-animation').length ) {
		theme.fn.intObs('.shape-divider-horizontal-animation', function(){
			for( let i = 0; i <= 1; i++ ) {
				const svgClone = $(this).find('svg:nth-child(1)').clone();

				$(this).append( svgClone )
			}

			$(this).addClass('start');
		}, {});
	}

	/*
	* Shape Divider - SVG Aspect Ratio
	*/
	function aspectRatioSVG() {
		if( $(window).width() < 1950 ) {
			$('.shape-divider svg[preserveAspectRatio]').each(function(){
				if( !$(this).parent().hasClass('shape-divider-horizontal-animation') ) {
					$(this).attr('preserveAspectRatio', 'xMinYMin');
				} else {
					$(this).attr('preserveAspectRatio', 'none');
				}
			});
		} else {
			$('.shape-divider svg[preserveAspectRatio]').each(function(){
				$(this).attr('preserveAspectRatio', 'none');
			});
		}
	}

	/*
	* Content Switcher
	*/
	$('[data-content-switcher]').on('change', function(e, v) {
		const switcherRel = ($(this).is(':checked') ? '1' : '2' ), switcherId = $(this).attr('data-content-switcher-content-id');

		$('[data-content-switcher-id=' + switcherId + ']').addClass('initialized').removeClass('active');

		const $activeEl = $('[data-content-switcher-id=' + switcherId + '][data-content-switcher-rel=' + switcherRel + ']');

		$activeEl.addClass('active');

		$activeEl.parent().css('height', $activeEl.height());
	});

	$('[data-content-switcher]').trigger('change');

	/*
	* Dynamic Height
	*/
	const $window = $(window);
	$window.on('resize dynamic.height.resize', () => {
		$('[data-dynamic-height]').each(function(){
			const $this = $(this), values = JSON.parse($this.data('dynamic-height').replace(/'/g,'"').replace(';',''));

			// XS
			if( $window.width() < 576 ) {
				$this.height( values[4] );
			}

			// SM
			if( $window.width() > 575 && $window.width() < 768 ) {
				$this.height( values[3] );
			}

			// MD
			if( $window.width() > 767 && $window.width() < 992 ) {
				$this.height( values[2] );
			}

			// LG
			if( $window.width() > 991 && $window.width() < 1200 ) {
				$this.height( values[1] );
			}

			// XS
			if( $window.width() > 1199 ) {
				$this.height( values[0] );
			}
		});
	});

	// Mobile First Load
	if( $window.width() < 992 ) {
		$window.trigger('dynamic.height.resize');
	}

	/*
	* Video - Trigger Play
	*/
	if( $('[data-trigger-play-video]').length ) {
		theme.fn.execOnceTroughEvent( '[data-trigger-play-video]', 'mouseover.trigger.play.video', function(){
			const $video = $( $(this).data('trigger-play-video') );

			$(this).on('click', function(e){
				e.preventDefault();

				if( $(this).data('trigger-play-video-remove') == 'yes' ) {
					$(this).animate({
						opacity: 0
					}, 300, function(){
						$video[0].play();

						$(this).remove();
					});
				} else {
					setTimeout(() => {
						$video[0].play();
					},300);
				}
			});
		});
	}

	/*
	* Video - Auto Play
	*/
	if( $('video[data-auto-play]').length ) {
		$(window).on('load', () => {
			$('video[data-auto-play]').each(function(){
				const $video = $(this);

				setTimeout(() => {
					if( $( '#' + $video.attr('id') ).length ) {
						if( $( '[data-trigger-play-video="#' + $video.attr('id') + '"]' ).data('trigger-play-video-remove') == 'yes' ) {
							$( '[data-trigger-play-video="#' + $video.attr('id') + '"]' ).animate({
								opacity: 0
							}, 300, () => {
								$video[0].play();

								$( '[data-trigger-play-video="#' + $video.attr('id') + '"]' ).remove();
							});
						} else {
							setTimeout(() => {
								$video[0].play();
							},300);
						}
					}
				}, 100);

			});
		});
	}

	/*
	* Remove min height after the load of page
	*/
	if( $('[data-remove-min-height]').length ) {
		$(window).on('load', () => {
			$('[data-remove-min-height]').each(function(){
				$(this).css({
					'min-height': 0
				});
			});
		});
	}

	/*
	* Title Border
	*/
	if($('[data-title-border]').length) {

		const $pageHeaderTitleBorder = $('<span class="page-header-title-border"></span>'), $pageHeaderTitle = $('[data-title-border]'), $window = $(window);

		$pageHeaderTitle.before($pageHeaderTitleBorder);

		const setPageHeaderTitleBorderWidth = () => {
			$pageHeaderTitleBorder.width($pageHeaderTitle.width());
		};

		$window.afterResize(() => {
			setPageHeaderTitleBorderWidth();
		});

		setPageHeaderTitleBorderWidth();

		$pageHeaderTitleBorder.addClass('visible');
	}

	/*
	* Footer Reveal
	*/
	($ => {
		const $footerReveal = {
			$wrapper: $('.footer-reveal'),
			init() {
				const self = this;

				self.build();
				self.events();
			},
			build() {
				const self = this, footer_height = self.$wrapper.outerHeight(true), window_height = ( $(window).height() - $('.header-body').height() );

				if( footer_height > window_height ) {
					$('#footer').removeClass('footer-reveal');
					$('body').css('margin-bottom', 0);
				} else {
					$('#footer').addClass('footer-reveal');
					$('body').css('margin-bottom', footer_height);
				}

			},
			events() {
				const self = this, $window = $(window);

				$window.on('load', () => {
					$window.afterResize(() => {
						self.build();
					});
				});
			}
		};

		if( $('.footer-reveal').length ) {
			$footerReveal.init();
		}
	})(jQuery);

	/*
	* Re-Init Plugin
	*/
	if( $('[data-reinit-plugin]').length ) {
		$('[data-reinit-plugin]').on('click', function(e) {
			e.preventDefault();

			const pluginInstance = $(this).data('reinit-plugin'), pluginFunction = $(this).data('reinit-plugin-function'), pluginElement  = $(this).data('reinit-plugin-element'), pluginOptions  = theme.fn.getOptions($(this).data('reinit-plugin-options'));

			$( pluginElement ).data( pluginInstance ).destroy();

			setTimeout(() => {
				theme.fn.execPluginFunction(pluginFunction, $( pluginElement ), pluginOptions);	
			}, 1000);

		});
	}

	/*
	* Simple Copy To Clipboard
	*/
	if( $('[data-copy-to-clipboard]').length ) {
		theme.fn.intObs( '[data-copy-to-clipboard]', function(){
			const $this = $(this);

			$this.wrap( '<div class="copy-to-clipboard-wrapper position-relative"></div>' );

			const $copyButton = $('<a href="#" class="btn btn-primary btn-px-2 py-1 text-0 position-absolute top-8 right-8">COPY</a>');
			$this.parent().prepend( $copyButton );

			$copyButton.on('click', function(e){
				e.preventDefault();

				const $btn       = $(this), $temp = $('<textarea class="d-block opacity-0" style="height: 0;">');

				$btn.parent().append( $temp );

				$temp.val( $this.text() );
					
				$temp[0].select();
				$temp[0].setSelectionRange(0, 99999);

				document.execCommand("copy");

				$btn.addClass('copied');
				setTimeout(() => {
					$btn.removeClass('copied');
				}, 1000);

				$temp.remove();
			});
		}, {
			rootMargin: '0px 0px 0px 0px'
		} );
	}

	/*
	* Marquee
	*/
	if( $('.marquee').length && $.isFunction($.fn.marquee) ) {
		$('.marquee').marquee({
			duration: 5000,
			gap: 0,
			delayBeforeStart: 0,
			direction: 'left',
			duplicated: true
		});
	}

	/*
	* Style Switcher Open Loader Button
	*/
	if( $('.style-switcher-open-loader').length ) {

		const urlParams = new URLSearchParams(window.location.search);

		let hideStyleSwitcherAfterShow = false;

		$('.style-switcher-open-loader').on('click', function(e){
			e.preventDefault();

			const $this = $(this);

			// Add Spinner to icon
			$this.addClass('style-switcher-open-loader-loading');

			const basePath = $(this).data('base-path'), skinSrc = $(this).data('skin-src');

			const script1 = document.createElement("script");
			script1.src = basePath + "master/style-switcher/style.switcher.localstorage.js";

			const script2 = document.createElement("script");
			script2.src = basePath + "master/style-switcher/style.switcher.js";
			script2.id = "styleSwitcherScript";
			script2.setAttribute('data-base-path', basePath);
			script2.setAttribute('data-skin-src', skinSrc);

			script2.onload = () => {
				setTimeout(() => {
					// Trigger a click to open the style switcher sidebar
					function checkIfReady() {
						if( !$('.style-switcher-open').length ) {
							window.setTimeout(checkIfReady, 100);
						} else {
							$('.style-switcher-open').trigger('click');

							if (hideStyleSwitcherAfterShow) {
								setTimeout(() => {
									$('.style-switcher-open').trigger('click');
								}, 2000);
							}
						}
					}
					checkIfReady();

				}, 500);
			}

			document.body.appendChild(script1);
			document.body.appendChild(script2);	

		});

		let htmlDataOptions = $('html').data('style-switcher-options');
		let showSwitcher = false;

		if(htmlDataOptions) {
			htmlDataOptions = htmlDataOptions.replace(/'/g, '"');

			if (JSON.parse(htmlDataOptions).showSwitcher) {
				showSwitcher = true;
			}

			if (JSON.parse(htmlDataOptions).hideStyleSwitcherAfterShow) {
				hideStyleSwitcherAfterShow = true;
			}
		}

		if (urlParams.has("showStyleSwitcher")) {
			showSwitcher = true;
		}

		if (urlParams.has("hideStyleSwitcherAfterShow")) {
			hideStyleSwitcherAfterShow = true;
		}

		if (showSwitcher) {
			$('.style-switcher-open-loader').trigger('click');
		}

	}

})).apply(this, [ window.theme, jQuery ]);

// Animate
(((theme = {}, $) => {
    const instanceName = '__animate';

    class PluginAnimate {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginAnimate.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			// Flag Class Only
			// - Useful for simple animations like hightlight
			// - Less process and memory
			if( self.options.flagClassOnly ) {
				const delay = self.options.wrapper.attr('data-appear-animation-delay') ? self.options.wrapper.attr('data-appear-animation-delay') : self.options.delay;
				
				self.options.wrapper.css({
					'animation-delay': delay + 'ms',
					'transition-delay': delay + 'ms'
				});
				self.options.wrapper.addClass( self.options.wrapper.attr('data-appear-animation') );

				return this;
			}

			if($('body').hasClass('loading-overlay-showing')) {
				$(window).on('loading.overlay.ready', () => {
					self.animate();
				});
			} else {
				self.animate();
			}

			return this;
		}

        animate() {
            const self = this;
            const $el = this.options.wrapper;
            let delay = 0;
            let duration = this.options.duration;
            const elTopDistance = $el.offset().top;
            const windowTopDistance = $(window).scrollTop();

            // If has appear animation elements inside a SVG. 
            // Intersection Observer API do not check elements inside SVG's, so we need initialize trough top parent SVG
            if( $el.data('appear-animation-svg') ) {
				$el.find('[data-appear-animation]').each(function(){
                    const $this = $(this);
                    let opts;

                    const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                    if (pluginOptions)
						opts = pluginOptions;

                    $this.themePluginAnimate(opts);
                });

				return this;
			}

            // No animation at the first load of page. This is good for performance
            if( self.options.firstLoadNoAnim ) {
				$el.removeClass('appear-animation');

				// Inside Carousel
				if( $el.closest('.owl-carousel').get(0) ) {
					setTimeout(() => {
						$el.closest('.owl-carousel').on('change.owl.carousel', () => {
							self.options.firstLoadNoAnim = false;
							$el.removeData('__animate');
							$el.themePluginAnimate( self.options );
						});
					}, 500);
				}

				return this;
			}

            $el.addClass('appear-animation animated');

            if (!$('html').hasClass('no-csstransitions') && $(window).width() > self.options.minWindowWidth && elTopDistance >= windowTopDistance || self.options.forceAnimation == true) {
				delay = ($el.attr('data-appear-animation-delay') ? $el.attr('data-appear-animation-delay') : self.options.delay);
				duration = ($el.attr('data-appear-animation-duration') ? $el.attr('data-appear-animation-duration') : self.options.duration);

				if (duration != '750ms') {
					$el.css('animation-duration', duration);
				}

				$el.css('animation-delay', delay + 'ms');
				$el.addClass($el.attr('data-appear-animation') + ' appear-animation-visible');
				
				$el.trigger('animation:show');

			} else {
				$el.addClass('appear-animation-visible');
			}

            return this;
        }
    }

    PluginAnimate.defaults = {
		accX: 0,
		accY: -80,
		delay: 100,
		duration: '750ms',
		minWindowWidth: 0,
		forceAnimation: false,
		flagClassOnly: false
	};

    // expose to scope
    $.extend(theme, {
		PluginAnimate
	});

    // jquery plugin
    $.fn.themePluginAnimate = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginAnimate($this, opts);
			}

		});
	};
})).apply(this, [window.theme, jQuery]);

// Animated Content
(((theme = {}, $) => {
    const instanceName = '__animatedContent';

    class PluginAnimatedContent {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			const self = this;

			this.$el = $el;
			this.initialText = $el.text();

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginAnimatedContent.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self    = this;

			if( $(window).width() < self.options.minWindowWidth ) {
				return this;
			}

			if( self.options.firstLoadNoAnim ) {
				self.$el.css({
					visibility: 'visible'
				});

				// Inside Carousel
				if( self.$el.closest('.owl-carousel').get(0) ) {
					setTimeout(() => {
						self.$el.closest('.owl-carousel').on('change.owl.carousel', () => {
							self.options.firstLoadNoAnim = false;
							self.build();
						});
					}, 500);
				}

				return this;
			}

			// Set Min Height to avoid flicking issues
			self.setMinHeight();

			// Letter
			if( self.options.contentType == 'letter' ) {

				self.$el.addClass('initialized');

				const letters = self.$el.text().split('');

				self.$el.text('');

				// Type Writer
				if( self.options.animationName == 'typeWriter' ) {
					self.$el.append( '<span class="letters-wrapper"></span><span class="typeWriter"></pre>' );

					let index = 0;

					setTimeout(() => {

						const timeout = () => {
							const st = setTimeout(() => {
								const letter = letters[index];
								
								self.$el.find('.letters-wrapper').append( '<span class="letter '+ ( self.options.letterClass ? self.options.letterClass + ' ' : '' ) +'">' + letter + '</span>' );

								index++;
								timeout();
							}, self.options.animationSpeed);

							if( index >= letters.length ) {
								clearTimeout(st);
							}
						};
						timeout();

					}, self.options.startDelay);

				// Class Animation
				} else {
					setTimeout(() => {
						for( let i = 0; i < letters.length; i++ ) {
							const letter = letters[i];
							
							self.$el.append( '<span class="animated-letters-wrapper ' + self.options.wrapperClass + '"><span class="animated-letters-item letter '+ ( self.options.letterClass ? self.options.letterClass + ' ' : '' ) + self.options.animationName +' animated" style="animation-delay: '+ ( i * self.options.animationSpeed ) +'ms;">' + ( letter == ' ' ? '&nbsp;' : letter ) + '</span></span>' );
		
						}
					}, self.options.startDelay);
				}

			// Words
			} else if( self.options.contentType == 'word' ) {
                const words = self.$el.text().split(" ");
                let delay = self.options.startDelay;

                self.$el.empty();

                $.each(words, (i, v) => {
					self.$el.append( $('<span class="animated-words-wrapper ' + self.options.wrapperClass + '">').html('<span class="animated-words-item ' + self.options.wordClass + ' appear-animation" data-appear-animation="' + self.options.animationName + '" data-appear-animation-delay="' + delay + '">' + v + '&nbsp;</span>') );
					delay = delay + self.options.animationSpeed;
				});

                if ($.isFunction($.fn['themePluginAnimate']) && $('.animated-words-item[data-appear-animation]').length) {
					theme.fn.dynIntObsInit( '.animated-words-item[data-appear-animation]', 'themePluginAnimate', theme.PluginAnimate.defaults );
				}

                self.$el.addClass('initialized');
            }

			return this;
		}

        setMinHeight() {
			const self = this;

			// if it's inside carousel
			if( self.$el.closest('.owl-carousel').get(0) ) {
				self.$el.closest('.owl-carousel').addClass('d-block');
				self.$el.css( 'min-height', self.$el.height() );
				self.$el.closest('.owl-carousel').removeClass('d-block');
			} else {
				self.$el.css( 'min-height', self.$el.height() );
			}

			return this;
		}

        destroy() {
			const self = this;

			self.$el
				.html( self.initialText )
				.css( 'min-height', '' );

			return this;
		}

        events() {
			const self = this;

			// Destroy
			self.$el.on('animated.letters.destroy', () => {
				self.destroy();
			});

			// Initialize
			self.$el.on('animated.letters.initialize', () => {
				self.build();
			});

			return this;
		}
    }

    PluginAnimatedContent.defaults = {
		contentType: 'letter',
		animationName: 'fadeIn',
		animationSpeed: 50,
		startDelay: 500,
		minWindowWidth: 768,
		letterClass: '',
		wordClass: '',
		wrapperClass: ''
	};

    // expose to scope
    $.extend(theme, {
		PluginAnimatedContent
	});

    // jquery plugin
    $.fn.themePluginAnimatedContent = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginAnimatedContent($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Before / After
(((theme = {}, $) => {
    const instanceName = '__beforeafter';

    class PluginBeforeAfter {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginBeforeAfter.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {

			if ($.isFunction($.fn.twentytwenty)) {

				const self = this;

				self.options.wrapper.waitForImages(() => {
					self.options.wrapper.twentytwenty(self.options);
				});

			} else {

				theme.fn.showErrorMessage('Failed to Load File', 'Failed to load: twentytwenty - Include the following file(s): (vendor/twentytwenty/css/twentytwenty.css, vendor/twentytwenty/js/jquery.event.move.js, vendor/twentytwenty/js/jquery.twentytwenty.js)');

			}

			return this;

		}
    }

    PluginBeforeAfter.defaults = {
		forceInit: true,
		default_offset_pct: 0.5,
		orientation: 'horizontal',
		before_label: 'Before',
		after_label: 'After',
		no_overlay: false,
		move_slider_on_hover: false,
		move_with_handle_only: true,
		click_to_move: false
	};

    // expose to scope
    $.extend(theme, {
		PluginBeforeAfter
	});

    // jquery plugin
    $.fn.themePluginBeforeAfter = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginBeforeAfter($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Carousel Light
(((theme = {}, $) => {
    const instanceName = '__carouselLight';

    class PluginCarouselLight {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;
			this.clickFlag = true;

			this
				.setData()
				.setOptions(opts)
				.build()
				.owlNav()
				.owlDots()
				.autoPlay()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginCarouselLight.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			self.$el
				.css('opacity', 1)
				.find('.owl-item:first-child')
				.addClass('active');

			self.$el.trigger('initialized.owl.carousel');

			// Carousel Navigate By ID and item index
			self.carouselNavigate();

			return this;
		}

        changeSlide($nextSlide) {
			const self = this, $prevSlide = self.$el.find('.owl-item.active');

			self.$el.find('.owl-item.active').addClass('removing');

			$prevSlide
				.removeClass('fadeIn')
				.addClass( 'fadeOut animated' );

			setTimeout(() => {
				setTimeout(() => {
					$prevSlide.removeClass('active');
				}, 400);

				$nextSlide
					.addClass('active')
					.removeClass('fadeOut')
					.addClass( 'fadeIn animated' );

			}, 200);

			// Dots
			self.$el
				.find('.owl-dot')
				.removeClass('active')
				.eq( $nextSlide.index() )
				.addClass('active');

			self.$el.trigger({
				type: 'change.owl.carousel',
				nextSlideIndex: $nextSlide.index(),
				prevSlideIndex: $prevSlide.index()
			});

			setTimeout(() => {
				self.$el.trigger({
					type: 'changed.owl.carousel',
					nextSlideIndex: $nextSlide.index(),
					prevSlideIndex: $prevSlide.index()
				});
			}, 500);
		}

        owlNav() {
			const self = this, $owlNext = self.$el.find('.owl-next'), $owlPrev = self.$el.find('.owl-prev');

			$owlPrev.on('click', e => {
				e.preventDefault();

				if( self.options.disableAutoPlayOnClick ) {
					window.clearInterval(self.autoPlayInterval);
				}

				if( self.avoidMultipleClicks() ) {
					return false;
				}

				self.owlPrev();
			});

			$owlNext.on('click', e => {
				e.preventDefault();

				if( self.options.disableAutoPlayOnClick ) {
					window.clearInterval(self.autoPlayInterval);
				}

				if( self.avoidMultipleClicks() ) {
					return false;
				}

				self.owlNext();
			});

			return this;
		}

        owlDots() {
			const self = this, $owlDot = self.$el.find('.owl-dot');

			$owlDot.on('click', function(e){
				let $this = $(this);

				e.preventDefault();

				if( self.options.disableAutoPlayOnClick ) {
					window.clearInterval(self.autoPlayInterval);
				}

				if( self.avoidMultipleClicks() ) {
					return false;
				}

				const dotIndex = $(this).index();

				// Do nothing if respective dot slide is active/showing
				if( $this.hasClass('active') ) {
					return false;
				}

				self.changeSlide( self.$el.find('.owl-item').eq( dotIndex ) );
			});

			return this;
		}

        owlPrev() {
			const self = this;

			if( self.$el.find('.owl-item.active').prev().get(0) ) {
				self.changeSlide( self.$el.find('.owl-item.active').prev() );
			} else {
				self.changeSlide( self.$el.find('.owl-item:last-child') );
			}
		}

        owlNext() {
			const self = this;

			if( self.$el.find('.owl-item.active').next().get(0) ) {
				self.changeSlide( self.$el.find('.owl-item.active').next() );
			} else {
				self.changeSlide( self.$el.find('.owl-item').eq(0) );
			}
		}

        avoidMultipleClicks() {
			const self = this;

			if( !self.clickFlag ) {
				return true;
			}

			if( self.clickFlag ) {
				self.clickFlag = false;
				setTimeout(() => {
					self.clickFlag = true; 
				}, 1000);
			}

			return false;
		}

        autoPlay() {
			const self = this, $el  = this.options.wrapper;

			if( self.options.autoplay ) {
				self.autoPlayInterval = window.setInterval(() => {
					self.owlNext();
				}, self.options.autoplayTimeout);
			}

			return this;
		}

        carouselNavigate() {
			const self      = this, $el       = this.options.wrapper, $carousel = $el;

			if( $('[data-carousel-navigate]').get(0) ) {
				$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"]').each(function(){
					const $this = $(this), hasCarousel = $( $this.data('carousel-navigate-id') ).get(0), toIndex = $this.data('carousel-navigate-to');

					if( hasCarousel ) {

						$this.on('click', () => {

							if( self.options.disableAutoPlayOnClick ) {
								window.clearInterval(self.autoPlayInterval);
							}
							
							self.changeSlide( self.$el.find('.owl-item').eq( parseInt(toIndex) - 1 ) );
						});

					}
				});

				$el.on('change.owl.carousel', e => {
					$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"]').removeClass('active');
				});

				$el.on('changed.owl.carousel', ({nextSlideIndex}) => {
					$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"][data-carousel-navigate-to="'+ ( nextSlideIndex + 1 ) +'"]').addClass('active');
				});
			}

			return this;
		}

        events() {
			const self = this;

			self.$el.on('change.owl.carousel', event => {

				// Hide elements inside carousel
			    self.$el.find('[data-appear-animation]:not(.background-image-wrapper), [data-plugin-animated-letters]').addClass('invisible');

			    // Animated Letters
			    self.$el.find('[data-plugin-animated-letters]').trigger('animated.letters.destroy');

			    // Remove "d-none" class before show the element. This is useful when using background images inside a carousel. Like ken burns effect
			    self.$el.find('.owl-item:not(.active) [data-carousel-onchange-show]').removeClass('d-none');

			});

			self.$el.on('changed.owl.carousel', event => {
				setTimeout(() => {

				    // Appear Animation
				    if( self.$el.find('.owl-item.cloned [data-appear-animation]').get(0) ) {
				    	self.$el.find('.owl-item.cloned [data-appear-animation]').each(function() {
                            const $this = $(this);
                            let opts;

                            const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                            if (pluginOptions)
								opts = pluginOptions;

                            $this.themePluginAnimate(opts);
                        });
				    }

					// Show elements inside carousel
				    self.$el.find('.owl-item.active [data-appear-animation]:not(.background-image-wrapper), [data-plugin-animated-letters]').removeClass('invisible');

				    // Animated Letters
				    self.$el.find('.owl-item.active [data-plugin-animated-letters]').trigger('animated.letters.initialize');

				    // Background Video
				    self.$el.find('.owl-item.cloned.active [data-plugin-video-background]').trigger('video.background.initialize');

				}, 500);
			    
			});

			if( self.options.swipeEvents ) {
				self.$el.swipe({
					swipe(event, direction, distance, duration, fingerCount, fingerData) {
						switch ( direction ) {
							case 'right':
								self.owlPrev();
							break;
				
							case 'left':
								self.owlNext();
							break;
						}
					},
					allowPageScroll: "vertical"
				});
			}
		}
    }

    PluginCarouselLight.defaults = {
		autoplay: true,
		autoplayTimeout: 7000,
		disableAutoPlayOnClick: true,
		swipeEvents: true
	};

    // expose to scope
    $.extend(theme, {
		PluginCarouselLight
	});

    // jquery plugin
    $.fn.themePluginCarouselLight = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginCarouselLight($this, opts);
			}

		});
	};
})).apply(this, [window.theme, jQuery]);

// Carousel
(((theme = {}, $) => {
    const instanceName = '__carousel';

    class PluginCarousel {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			// If has data-icon inside, initialize only after icons get rendered
			// Prevent flicking issues
			if( $el.find('[data-icon]').get(0) ) {
				const self = this;

				$(window).on('icon.rendered', function(){
					if ($el.data(instanceName)) {
						return this;
					}

					setTimeout(() => {
						self
							.setData()
							.setOptions(opts)
							.build();
					}, 1000);
				});

				return this;
			}

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginCarousel.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.owlCarousel))) {
				return this;
			}

			const self = this, $el = this.options.wrapper;

			// Add Theme Class
			$el.addClass('owl-theme');

			// Add Loading
			$el.addClass('owl-loading');

			// Force RTL according to HTML dir attribute
			if ($('html').attr('dir') == 'rtl') {
				this.options = $.extend(true, {}, this.options, {
					rtl: true
				});
			}

			if (this.options.items == 1) {
				this.options.responsive = {}
			}

			if (this.options.items > 4) {
				this.options = $.extend(true, {}, this.options, {
					responsive: {
						1199: {
							items: this.options.items
						}
					}
				});
			}

			// Auto Height Fixes
			if (this.options.autoHeight) {
				const itemsHeight = [];

				$el.find('.owl-item').each(function(){
					if( $(this).hasClass('active') ) {
						itemsHeight.push( $(this).height() );
					}
				});

				$(window).afterResize(() => {
					$el.find('.owl-stage-outer').height( Math.max.apply(null, itemsHeight) );
				});

				$(window).on('load', () => {
					$el.find('.owl-stage-outer').height( Math.max.apply(null, itemsHeight) );
				});
			}

			// Initialize OwlCarousel
			$el.owlCarousel(this.options).addClass('owl-carousel-init animated fadeIn');

			// Remove "animated fadeIn" class to prevent conflicts
			setTimeout(() => {
				$el.removeClass('animated fadeIn');
			}, 1000);

			// Owl Carousel Wrapper
			if( $el.closest('.owl-carousel-wrapper').get(0) ) {
				setTimeout(() => {
					$el.closest('.owl-carousel-wrapper').css({
						height: ''
					});
				}, 500);
			}

			// Owl Carousel Loader
			if( $el.prev().hasClass('owl-carousel-loader') ) {
				$el.prev().remove();
			}

			// Nav Offset
			self.navigationOffsets();

			// Nav Outside
			if( $el.hasClass('nav-outside') ) {
				$(window).on('owl.carousel.nav.outside', () => {
					if( $(window).width() < 992 ) {
						self.options.stagePadding = 40;
						$el.addClass('stage-margin');
					} else {
						self.options.stagePadding = 0;
						$el.removeClass('stage-margin');
					}

					$el.owlCarousel('destroy').owlCarousel( self.options );

					// Nav Offset
					self.navigationOffsets();
				});

				// Window Resize
				$(window).on('load', () => {
					$(window).afterResize(() => {
						$(window).trigger('owl.carousel.nav.outside');
					});
				});

				// First Load
				$(window).trigger('owl.carousel.nav.outside');
			}

			// Nav style 5 (SVG Arrows)
			if( $el.hasClass('nav-svg-arrows-1') ) {
				const svg_arrow = '' +
					'<svg version="1.1" viewBox="0 0 15.698 8.706" width="17" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' +
						'<polygon stroke="#212121" stroke-width="0.1" fill="#212121" points="11.354,0 10.646,0.706 13.786,3.853 0,3.853 0,4.853 13.786,4.853 10.646,8 11.354,8.706 15.698,4.353 "/>' +
					'</svg>';

				$el.find('.owl-next, .owl-prev').append( svg_arrow );
			}

			// Sync
			if( $el.attr('data-sync') ) {
				$el.on('change.owl.carousel', ({namespace, property, relatedTarget}) => {
					if (namespace && property.name === 'position') {
					    const target = relatedTarget.relative(property.value, true);
					    $( $el.data('sync') ).owlCarousel('to', target, 300, true);				        
				  	}
				});
			}

			// Carousel Center Active Item
			if( $el.hasClass('carousel-center-active-item') ) {
				const itemsActive    = $el.find('.owl-item.active'), indexCenter    = Math.floor( ($el.find('.owl-item.active').length - 1) / 2 ), itemCenter     = itemsActive.eq(indexCenter);

				itemCenter.addClass('current');

				$el.on('change.owl.carousel', event => {
				  	$el.find('.owl-item').removeClass('current');
					
					setTimeout(() => {
					  	const itemsActive    = $el.find('.owl-item.active'), indexCenter    = Math.floor( ($el.find('.owl-item.active').length - 1) / 2 ), itemCenter     = itemsActive.eq(indexCenter);

					  	itemCenter.addClass('current');
					}, 100);
				});

				// Refresh
				$el.trigger('refresh.owl.carousel');

			}

			// AnimateIn / AnimateOut Fix
			if( self.options.animateIn || self.options.animateOut ) {
				$el.on('change.owl.carousel', event => {

					// Hide elements inside carousel
				    $el.find('[data-appear-animation], [data-plugin-animated-letters]').addClass('d-none');

				    // Animated Letters
				    $el.find('[data-plugin-animated-letters]').trigger('animated.letters.destroy');

				    // Remove "d-none" class before show the element. This is useful when using background images inside a carousel. Like ken burns effect
				    $el.find('.owl-item:not(.active) [data-carousel-onchange-show]').removeClass('d-none');

				});

				$el.on('changed.owl.carousel', event => {
					setTimeout(() => {

					    // Appear Animation
				    	$el.find('[data-appear-animation]').each(function() {
                            const $this = $(this);
                            let opts;

                            const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                            if (pluginOptions)
								opts = pluginOptions;

                            $this.themePluginAnimate(opts);
                        });

						// Show elements inside carousel
					    $el.find('.owl-item.active [data-appear-animation], [data-plugin-animated-letters]').removeClass('d-none');

					    // Animated Letters
					    $el.find('.owl-item.active [data-plugin-animated-letters]').trigger('animated.letters.initialize');

					    // Background Video
					    $el.find('.owl-item.cloned.active [data-plugin-video-background]').trigger('video.background.initialize');

					}, 10);
				    
				});
			}

			// data-icon inside carousel
			if( $el.find('[data-icon]').length ) {
				$el.on('change.owl.carousel drag.owl.carousel', () => {
					$el.find('.owl-item.cloned [data-icon]').each(function(){
                        const $this = $(this);
                        let opts;

                        const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                        if (pluginOptions)
							opts = pluginOptions;

                        $this.themePluginIcon(opts);
                    });
				});
			}

			// Render Background Videos inside carousel. Just a trigger on window is sufficient to render
			if( $el.find('[data-plugin-video-background]').get(0) ) {
				$(window).resize();
			}

			// Remove Loading
			$el.removeClass('owl-loading');

			// Remove Height
			$el.css('height', 'auto');

			// Carousel Navigate By ID and item index
			self.carouselNavigate();

			// Refresh Carousel
			if( self.options.refresh ) {
				$el.owlCarousel('refresh');
			}

			return this;
		}

        navigationOffsets() {
			const self 			 = this, $el  			 = this.options.wrapper, navHasTransform  = $el.find('.owl-nav').css('transform') == 'none' ? false : true, dotsHasTransform = $el.find('.owl-dots').css('transform') == 'none' ? false : true;

			// ************* NAV *****************
			// Nav Offset - Horizontal
			if( self.options.navHorizontalOffset && !self.options.navVerticalOffset ) {
				if( !navHasTransform ) {
					$el.find('.owl-nav').css({
						transform: 'translate3d('+ self.options.navHorizontalOffset +', 0, 0)'
					});
				} else {
					$el.find('.owl-nav').css({
						left: self.options.navHorizontalOffset
					});
				}
			}

			// Nav Offset - Vertical
			if( self.options.navVerticalOffset && !self.options.navHorizontalOffset ) {
				if( !navHasTransform ) {
					$el.find('.owl-nav').css({
						transform: 'translate3d(0, '+ self.options.navVerticalOffset +', 0)'
					});
				} else {
					$el.find('.owl-nav').css({
						top: 'calc( 50% - '+ self.options.navVerticalOffset +' )'
					});
				}
			}

			// Nav Offset - Horizontal & Vertical
			if( self.options.navVerticalOffset && self.options.navHorizontalOffset ) {
				if( !navHasTransform ) {
					$el.find('.owl-nav').css({
						transform: 'translate3d('+ self.options.navHorizontalOffset +', '+ self.options.navVerticalOffset +', 0)'
					});
				} else {
					$el.find('.owl-nav').css({
						top: 'calc( 50% - '+ self.options.navVerticalOffset +' )',
						left: self.options.navHorizontalOffset
					});
				}
			}

			// ********** DOTS *********************
			// Dots Offset - Horizontal
			if( self.options.dotsHorizontalOffset && !self.options.dotsVerticalOffset ) {
				$el.find('.owl-dots').css({
					transform: 'translate3d('+ self.options.dotsHorizontalOffset +', 0, 0)'
				});
			}

			// Dots Offset - Vertical
			if( self.options.dotsVerticalOffset && !self.options.dotsHorizontalOffset ) {
				if( !dotsHasTransform ) {
					$el.find('.owl-dots').css({
						transform: 'translate3d(0, '+ self.options.dotsVerticalOffset +', 0)'
					});
				} else {
					$el.find('.owl-dots').css({
						top: 'calc( 50% - '+ self.options.dotsVerticalOffset +' )'
					});
				}
			}

			// Dots Offset - Horizontal & Vertical
			if( self.options.dotsVerticalOffset && self.options.dotsHorizontalOffset ) {
				$el.find('.owl-dots').css({
					transform: 'translate3d('+ self.options.dotsHorizontalOffset +', '+ self.options.dotsVerticalOffset +', 0)'
				});
			}

			return this;
		}

        carouselNavigate() {
			const self      = this, $el       = this.options.wrapper, $carousel = $el.data('owl.carousel');

			if( $('[data-carousel-navigate]').get(0) ) {
				$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"]').each(function(){
					const $this = $(this), hasCarousel = $( $this.data('carousel-navigate-id') ).get(0), toIndex = $this.data('carousel-navigate-to');

					if( hasCarousel ) {

						$this.on('click', () => {
							$carousel.to( parseInt(toIndex) - 1 );
						});

					}
				});

				$el.on('change.owl.carousel', () => {
					$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"]').removeClass('active');
				});

				$el.on('changed.owl.carousel', ({item}) => {
					$('[data-carousel-navigate-id="#'+ $el.attr('id') +'"][data-carousel-navigate-to="'+ ( item.index + 1 ) +'"]').addClass('active');
				});
			}

			return this;
		}
    }

    PluginCarousel.defaults = {
		loop: true,
		responsive: {
			0: {
				items: 1
			},
			479: {
				items: 1
			},
			768: {
				items: 2
			},
			979: {
				items: 3
			},
			1199: {
				items: 4
			}
		},
		navText: [],
		refresh: false
	};

    // expose to scope
    $.extend(theme, {
		PluginCarousel
	});

    // jquery plugin
    $.fn.themePluginCarousel = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginCarousel($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Chart Circular
(((theme = {}, $) => {
    const instanceName = '__chartCircular';

    class PluginChartCircular {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginChartCircular.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.easyPieChart))) {
				return this;
			}

			const self = this, $el = this.options.wrapper, value = ($el.attr('data-percent') ? $el.attr('data-percent') : 0), percentEl = $el.find('.percent');

			$.extend(true, self.options, {
				onStep(from, to, currentValue) {
					percentEl.html(parseInt(currentValue));
				}
			});

			$el.attr('data-percent', 0);

			$el.easyPieChart(self.options);

			setTimeout(() => {

				$el.data('easyPieChart').update(value);
				$el.attr('data-percent', value);

			}, self.options.delay);

			return this;
		}
    }

    PluginChartCircular.defaults = {
		accX: 0,
		accY: -150,
		delay: 1,
		barColor: '#0088CC',
		trackColor: '#f2f2f2',
		scaleColor: false,
		scaleLength: 5,
		lineCap: 'round',
		lineWidth: 13,
		size: 175,
		rotate: 0,
		animate: ({
			duration: 2500,
			enabled: true
		})
	};

    // expose to scope
    $.extend(theme, {
		PluginChartCircular
	});

    // jquery plugin
    $.fn.themePluginChartCircular = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginChartCircular($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Countdown
(((theme = {}, $) => {
    const instanceName = '__countdown';

    class PluginCountdown {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginCountdown.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.countTo))) {
				return this;
			}

			const self = this, $el = this.options.wrapper, numberClass = ( self.options.numberClass ) ? ' ' + self.options.numberClass : '', wrapperClass = ( self.options.wrapperClass ) ? ' ' + self.options.wrapperClass : '';

			if( self.options.uppercase ) {
				$el.countdown(self.options.date).on('update.countdown', function(event) {
					const $this = $(this).html(event.strftime(self.options.insertHTMLbefore
						+ '<span class="days'+ wrapperClass +'"><span class="'+ numberClass +'">%D</span> '+ self.options.textDay +'</span> '
						+ '<span class="hours'+ wrapperClass +'"><span class="'+ numberClass +'">%H</span> '+ self.options.textHour +'</span> '
						+ '<span class="minutes'+ wrapperClass +'"><span class="'+ numberClass +'">%M</span> '+ self.options.textMin +'</span> '
						+ '<span class="seconds'+ wrapperClass +'"><span class="'+ numberClass +'">%S</span> '+ self.options.textSec +'</span> '
						+ self.options.insertHTMLafter
					));
				});
			} else {
				$el.countdown(self.options.date).on('update.countdown', function(event) {
					const $this = $(this).html(event.strftime(self.options.insertHTMLbefore
						+ '<span class="days'+ wrapperClass +'"><span class="'+ numberClass +'">%D</span> '+ self.options.textDay +'</span> '
						+ '<span class="hours'+ wrapperClass +'"><span class="'+ numberClass +'">%H</span> '+ self.options.textHour +'</span> '
						+ '<span class="minutes'+ wrapperClass +'"><span class="'+ numberClass +'">%M</span> '+ self.options.textMin +'</span> '
						+ '<span class="seconds'+ wrapperClass +'"><span class="'+ numberClass +'">%S</span> '+ self.options.textSec +'</span> '
						+ self.options.insertHTMLafter
					));
				});
			}

			return this;
		}
    }

    PluginCountdown.defaults = {
		date: '2030/06/10 12:00:00',
		textDay: 'DAYS',
		textHour: 'HRS',
		textMin: 'MIN',
		textSec: 'SEC',
		uppercase: true,
		numberClass: '',
		wrapperClass: '',
		insertHTMLbefore: '',
		insertHTMLafter: ''
	};

    // expose to scope
    $.extend(theme, {
		PluginCountdown
	});

    // jquery plugin
    $.fn.themePluginCountdown = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginCountdown($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Counter
(((theme = {}, $) => {
    const instanceName = '__counter';

    class PluginCounter {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginCounter.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.countTo))) {
				return this;
			}

			const self = this, $el = this.options.wrapper;

			if (self.options.comma) {
				self.options.formatter = (value, {decimals}) => value.toFixed(decimals).toString().replace('.',',')
			}

			$.extend(self.options, {
				onComplete() {
					
					if ($el.data('append')) {
						if( self.options.appendWrapper ) {
							const appendWrapper = $( self.options.appendWrapper );

							appendWrapper.append( $el.data('append') );

							$el.html( $el.html() + appendWrapper[0].outerHTML );
						} else {
							$el.html($el.html() + $el.data('append'));
						}
					}

					if ($el.data('prepend')) {
						if( self.options.prependWrapper ) {
							const prependWrapper = $( self.options.prependWrapper );

							prependWrapper.append( $el.data('prepend') );

							$el.html( $el.html() + prependWrapper[0].outerHTML );
						} else {
							$el.html($el.data('prepend') + $el.html());
						}
					}
				}
			});

			$el.countTo(self.options);

			return this;
		}
    }

    PluginCounter.defaults = {
		accX: 0,
		accY: 0,
		appendWrapper: false,
		prependWrapper: false,
		speed: 3000,
		refreshInterval: 100,
		decimals: 0,
		comma: false,
		onUpdate: null,
		onComplete: null
	}

    // expose to scope
    $.extend(theme, {
		PluginCounter
	});

    // jquery plugin
    $.fn.themePluginCounter = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginCounter($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// CursorEffect
(((theme = {}, $) => {
    const instanceName = '__cursorEffect';

    class PluginCursorEffect {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginCursorEffect.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			// Global Variables for cursor position
			self.clientX = -100;
			self.clientY = -100;

			// Hide Mouse Cursor
			if( self.options.hideMouseCursor ) {
				self.$el.addClass('hide-mouse-cursor');
			}

			// Creates the cursor wrapper node
			const cursorOuter = document.createElement('DIV');
				cursorOuter.className = 'cursor-outer';

			// Creates the cursor inner node
			const cursorInner = document.createElement('DIV');
				cursorInner.className = 'cursor-inner';

			// Custom Cursor Outer Color
			if( self.options.cursorOuterColor ) {
				cursorOuter.style = 'border-color: ' + self.options.cursorOuterColor + ';';
			}

			// Custom Cursor Inner Color
			if( self.options.cursorInnerColor ) {
				cursorInner.style = 'background-color: ' + self.options.cursorInnerColor + ';';
			}

			// Size
			if( self.options.size ) {
				switch ( self.options.size ) {
					case 'small':
						self.$el.addClass( 'cursor-effect-size-small' );
						break;
					
					case 'big':
						self.$el.addClass( 'cursor-effect-size-big' );
						break;
				}
			}

			// Style
			if( self.options.style ) {
				self.$el.addClass( self.options.style );
			}

			// Prepend cursor wrapper node to the body
			document.body.prepend( cursorOuter );

			// Prepend cursor inner node to the body
			document.body.prepend( cursorInner );

			// Loop for render
			const render = () => {
				cursorOuter.style.transform = 'translate('+ self.clientX +'px, '+ self.clientY +'px)';
				cursorInner.style.transform = 'translate('+ self.clientX +'px, '+ self.clientY +'px)';

				self.loopInside = requestAnimationFrame(render);
			};
			self.loop = requestAnimationFrame(render);

			return this;
		}

        events() {
			const self = this, $cursorOuter = $('.cursor-outer'), $cursorInner = $('.cursor-inner');

			const initialCursorOuterBox    = $cursorOuter[0].getBoundingClientRect(), initialCursorOuterRadius = $cursorOuter.css('border-radius');

			// Update Cursor Position
			document.addEventListener('mousemove', ({clientX, clientY}) => {
				if( !self.isStuck ) {
					self.clientX = clientX - 20;
					self.clientY = clientY - 20;
				}

				$cursorOuter.removeClass('opacity-0');
			});

			self.isStuck = false;
			$('[data-cursor-effect-hover]').on('mouseenter', function(e){

				// Identify Event With Hover Class
				$cursorOuter.addClass('cursor-outer-hover');
				$cursorInner.addClass('cursor-inner-hover');

				// Hover Color
				const hoverColor = $(this).data('cursor-effect-hover-color');
				$cursorOuter.addClass( 'cursor-color-' + hoverColor );
				$cursorInner.addClass( 'cursor-color-' + hoverColor );

				// Effect Types
				switch ( $(this).data('cursor-effect-hover') ) {
					case 'fit':
						const thisBox = $(this)[0].getBoundingClientRect();

						self.clientX = thisBox.x;
						self.clientY = thisBox.y;

						$cursorOuter.css({
							width: thisBox.width,
							height: thisBox.height,
							'border-radius': $(this).css('border-radius')
						}).addClass('cursor-outer-fit');

						$cursorInner.addClass('opacity-0');

						self.isStuck = true;
						break;

					case 'plus':
						$cursorInner.addClass('cursor-inner-plus');
						break;
				}
			});

			$('[data-cursor-effect-hover]').on('mouseleave', function(){
				
				// Identify Event With Hover Class
				$cursorOuter.removeClass('cursor-outer-hover');
				$cursorInner.removeClass('cursor-inner-hover');

				// Remove Color Class
				const hoverColor = $(this).data('cursor-effect-hover-color');
				$cursorOuter.removeClass( 'cursor-color-' + hoverColor );
				$cursorInner.removeClass( 'cursor-color-' + hoverColor );

				// Effect Types
				switch ( $(this).data('cursor-effect-hover') ) {
					case 'fit':
						$cursorOuter.css({
							width: initialCursorOuterBox.width,
							height: initialCursorOuterBox.height,
							'border-radius': initialCursorOuterRadius
						}).removeClass('cursor-outer-fit');

						$cursorInner.removeClass('opacity-0');

						self.isStuck = false;
						break;

					case 'plus':
						$cursorInner.removeClass('cursor-inner-plus');
						break;
				}
			});

			$(window).on('scroll', () => {
				if( $cursorOuter.hasClass('cursor-outer-fit') ) {
					$cursorOuter.addClass('opacity-0').removeClass('cursor-outer-fit');
				}
			});

			return this;
		}

        destroy() {
			const self = this;

			self.$el.removeClass('hide-mouse-cursor cursor-effect-size-small cursor-effect-size-big cursor-effect-style-square');		

			cancelAnimationFrame( self.loop );
			cancelAnimationFrame( self.loopInside );

			document.querySelector('.cursor-outer').remove();
			document.querySelector('.cursor-inner').remove();

			self.$el.removeData( instanceName, self );
		}
    }

    PluginCursorEffect.defaults = {

	}

    // expose to scope
    $.extend(theme, {
		PluginCursorEffect
	});

    // jquery plugin
    $.fn.themePluginCursorEffect = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginCursorEffect($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Float Element
(((theme = {}, $) => {
    const instanceName = '__floatElement';

    class PluginFloatElement {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginFloatElement.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this;
            const $el = this.options.wrapper;
            const $window = $(window);
            let minus;

            // If has floating elements inside a SVG. 
            // Intersection Observer API do not check elements inside SVG's, so we need initialize trough top parent SVG
            if( $el.data('plugin-float-element-svg') ) {
				$el.find('[data-plugin-float-element]').each(function(){
                    const $this = $(this);
                    let opts;

                    const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                    if (pluginOptions)
						opts = pluginOptions;

                    $this.themePluginFloatElement(opts);
                });

				return this;
			}

            if( self.options.style ) {
				$el.attr('style', self.options.style);
			}

            if( $window.width() > self.options.minWindowWidth ) {

				// Set Start Position
				if( self.options.startPos == 'none' ) {
					minus = '';
				} else if( self.options.startPos == 'top' ) {
					$el.css({
						top: 0
					});
					minus = '';
				} else {
					$el.css({
						bottom: 0
					});
					minus = '-';
				}

				// Set Transition
				if( self.options.transition ) {
					$el.css({
						transition: 'ease-out transform '+ self.options.transitionDuration +'ms ' + self.options.transitionDelay + 'ms'
					});
				}

				// First Load
				self.movement(minus);	

				// Scroll
				$window.on('scroll', () => {
					self.movement(minus);				   
				});

			}

            return this;
        }

        movement(minus) {
			const self = this, $el = this.options.wrapper, $window = $(window), scrollTop = $window.scrollTop(), elementOffset = $el.offset().top, currentElementOffset = (elementOffset - scrollTop), factor = ( self.options.isInsideSVG ) ? 2 : 100;

		   	const scrollPercent = factor * currentElementOffset / ($window.height());

		   	if( $el.visible( true ) ) {

		   		if( !self.options.horizontal ) {

		   			$el.css({
			   			transform: 'translate3d(0, '+ minus + scrollPercent / self.options.speed +'%, 0)'
			   		});

		   		} else {

		   			$el.css({
			   			transform: 'translate3d('+ minus + scrollPercent / self.options.speed +'%, 0, 0)'
			   		});

		   		}
		   		
		   	}

		}
    }

    PluginFloatElement.defaults = {
		startPos: 'top',
		speed: 3,
		horizontal: false,
		isInsideSVG: false,
		transition: false,
		transitionDelay: 0,
		transitionDuration: 500,
		minWindowWidth: 991
	};

    // expose to scope
    $.extend(theme, {
		PluginFloatElement
	});

    // jquery plugin
    $.fn.themePluginFloatElement = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginFloatElement($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// GDPR
(((theme = {}, $) => {
    const instanceName = '__gdpr';

    class PluginGDPR {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			const self = this;

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginGDPR.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			// Show
			if( !$.cookie( 'porto-privacy-bar' ) ) {
				setTimeout(() => {
					self.options.wrapper.addClass('show');
				}, self.options.cookieBarShowDelay);
			}

			// If already has preferences cookie, check inputs according preferences cookie data
			if( $.cookie( 'porto-gdpr-preferences' ) ) {
				const preferencesArr = $.cookie( 'porto-gdpr-preferences' ).split(',');

				for( let i = 0; i < preferencesArr.length; i++ ) {
					if( $('input[value="'+ preferencesArr[i] +'"]').get(0) ) {
						if( $('input[value="'+ preferencesArr[i] +'"]').is(':checkbox') ) {
							$('input[value="'+ preferencesArr[i] +'"]').prop('checked', true);
						}
					}
				}
			}

			return this;

		}

        events() {
			const self = this;

			// Agree Trigger
			self.options.wrapper.find('.gdpr-agree-trigger').on('click', e => {
				e.preventDefault();

				$('.gdpr-preferences-form').find('.gdpr-input').each(function(){
					if( $(this).is(':checkbox') || $(this).is(':hidden') ) {
						$(this).prop('checked', true);
					}
				});

				$('.gdpr-preferences-form').trigger('submit').removeClass('show');

				self.removeCookieBar();
			});

			// Preferences Trigger
			self.options.wrapper.find('.gdpr-preferences-trigger').on('click', e => {
				e.preventDefault();

				$('.gdpr-preferences-popup').addClass('show');
			});

			// Close Popup Button
			$('.gdpr-close-popup').on('click', e => {
				e.preventDefault();

				$('.gdpr-preferences-popup').removeClass('show');
			});

			// Close Popup When Click Outside of popup area
			$('.gdpr-preferences-popup').on('click', ({target}) => {
				if( !$(target).closest('.gdpr-preferences-popup-content').get(0) ) {
					$('.gdpr-preferences-popup').removeClass('show');
				}
			});

			// Preference Form
			$('.gdpr-preferences-form').on('submit', function(e){
				e.preventDefault();

				const $this = $(this);

				// Save Preferences Button
				$this.find('button[type="submit"]').text( 'SAVING...' );

				// Form Data
				const formData = [];
				$this.find('.gdpr-input').each(function(){
					if( $(this).is(':checkbox') && $(this).is(':checked') || $(this).is(':hidden') ) {
						formData.push( $(this).val() );
					}
				});

				$.cookie( 'porto-privacy-bar', true, {expires: self.options.expires} );

				setTimeout(() => {
					$this.find('button[type="submit"]').text( 'SAVED!' ).removeClass('btn-primary').addClass('btn-success');

					setTimeout(() => {
						$('.gdpr-preferences-popup').removeClass('show');
						self.removeCookieBar();

						$this.find('button[type="submit"]').text( 'SAVE PREFERENCES' ).removeClass('btn-success').addClass('btn-primary');

						if( $.cookie( 'porto-gdpr-preferences' ) ) {

							$.cookie( 'porto-gdpr-preferences', formData, {expires: self.options.expires} );
							location.reload();

						} else {

							$.cookie( 'porto-gdpr-preferences', formData, {expires: self.options.expires} );

							if ($.isFunction($.fn['themePluginGDPRWrapper']) && $('[data-plugin-gdpr-wrapper]').length) {

								$(() => {
									$('[data-plugin-gdpr-wrapper]:not(.manual)').each(function() {
                                        const $this = $(this);
                                        let opts;

                                        $this.removeData('__gdprwrapper');

                                        const pluginOptions = theme.fn.getOptions($this.data('plugin-options'));
                                        if (pluginOptions)
											opts = pluginOptions;

                                        $this.themePluginGDPRWrapper(opts);
                                    });
								});

							}

						}

					}, 500);
				}, 1000);
			});

			// Remove/Reset Cookies
			$('.gdpr-reset-cookies').on('click', e => {
				e.preventDefault();

				self.clearCookies();

				location.reload();
			});

			// Open Preferences
			$('.gdpr-open-preferences').on('click', e => {
				e.preventDefault();

				$('.gdpr-preferences-popup').toggleClass('show');
			});

			return this;
		}

        removeCookieBar() {
			const self = this;

			self.options.wrapper.addClass('removing').on('transitionend', () => {
				setTimeout(() => {
					self.options.wrapper.removeClass('show removing');
				}, 500);
			});

			return this;
		}

        clearCookies() {
			const self = this;

			$.removeCookie( 'porto-privacy-bar' );
			$.removeCookie( 'porto-gdpr-preferences' );

			return this;
		}
    }

    PluginGDPR.defaults = {
		cookieBarShowDelay: 3000,
		expires: 365
	};

    // expose to scope
    $.extend(theme, {
		PluginGDPR
	});

    // jquery plugin
    $.fn.themePluginGDPR = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginGDPR($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// GDPR Wrapper
(((theme = {}, $) => {
    const instanceName = '__gdprwrapper';

    class PluginGDPRWrapper {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			const self = this;

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();
				
			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginGDPRWrapper.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			if( $.cookie( 'porto-gdpr-preferences' ) && $.cookie( 'porto-gdpr-preferences' ).includes(self.options.checkCookie) ) {

				$.ajax({
					url: self.options.ajaxURL,
					cache: false,
					complete({responseText}) {
					
						setTimeout(() => {

							self.options.wrapper.html(responseText).addClass('show');

						}, 1000);

					}
				});

			} else {
				self.options.wrapper.addClass('show');
			}

			return this;

		}
    }

    PluginGDPRWrapper.defaults = {

	};

    // expose to scope
    $.extend(theme, {
		PluginGDPRWrapper
	});

    // jquery plugin
    $.fn.themePluginGDPRWrapper = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginGDPRWrapper($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Hover Effect
(((theme = {}, $) => {
    const instanceName = '__hoverEffect';

    class PluginHoverEffect {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginHoverEffect.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			if(self.$el.hasClass('hover-effect-3d')) {
				self.options.effect = '3d';
			}

			// Magnetic
			if(self.options.effect == 'magnetic') {
				self.magnetic();
			}

			// 3d
			if(self.options.effect == '3d') {
				self.hover3d();
			}

			return this;
		}

        magnetic() {
			const self = this;

			self.$el.mousemove(function({clientX, clientY}) {

				const pos = this.getBoundingClientRect();
				const mx = clientX - pos.left - pos.width/2; 
				const my = clientY - pos.top - pos.height/2;

				this.style.transform = 'translate('+ mx * self.options.magneticMx +'px, '+ my * self.options.magneticMx +'px)';

			});

			self.$el.mouseleave(function(e) {

				this.style.transform = 'translate3d(0px, 0px, 0px)';

			});

			return this;

		}

        hover3d() {
			const self = this;

			if ($.isFunction($.fn['hover3d'])) {

				self.$el.hover3d({
					selector: self.options.selector,
					sensitivity: self.options.sensitivity
				});

			}

			return this;

		}
    }

    PluginHoverEffect.defaults = {
		effect: 'magnetic',
		magneticMx: 0.15,
		magneticMy: 0.3,
		magneticDeg: 12,
		selector: '.thumb-info, .hover-effect-3d-wrapper',
		sensitivity: 20
	};

    // expose to scope
    $.extend(theme, {
		PluginHoverEffect
	});

    // jquery plugin
    $.fn.themePluginHoverEffect = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginHoverEffect($this, opts);
			}

		});
	};
})).apply(this, [window.theme, jQuery]);

// Icon
(((theme = {}, $) => {
    const instanceName = '__icon';

    class PluginIcon {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginIcon.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self  	 = this;
            let $el   	 = this.options.wrapper;
            const color 	 = self.options.color;
            const elTopDistance = $el.offset().top;
            const windowTopDistance = $(window).scrollTop();
            let duration = ( self.options.animated && !self.options.strokeBased ) ? 200 : 100;

            // Check origin
            if( window.location.protocol === 'file:' ) {
				$el.css({
					opacity: 1,
					width: $el.attr('width')
				});

				if( self.options.extraClass ) {
					$el.addClass( self.options.extraClass );
				}

				if( self.options.extraClass.indexOf('-color-light') > 0 ) {
					$el.css({
						filter: 'invert(1)'
					});
				}

				$(window).trigger('icon.rendered');
				return;
			}

            // Duration
            if( self.options.duration ) {
				duration = self.options.duration;
			}

            // SVG Content
			const fileName = $el.attr('src');

			if(fileName.split('.').pop() == 'svg') {
				const SVGContent = $.get({
					url: fileName, 
					success(data, status, {responseText}) {
						const iconWrapper = self.options.fadeIn ? $('<div class="animated-icon animated fadeIn">'+ responseText +'</div>') : $('<div class="animated-icon animated">'+ responseText +'</div>'), uniqid = 'icon_' + Math.floor(Math.random() * 26) + Date.now();

						// Add ID
						iconWrapper.find('svg').attr('id', uniqid);

						// Identify with filename
						iconWrapper.find('svg').attr('data-filename', $el.attr('src').split(/(\\|\/)/g).pop());

						if( $el.attr('width') ) {
							iconWrapper.find('svg')
								.attr('width', $el.attr('width'))
								.attr('height', $el.attr('width'));						
						}

						if( $el.attr('height') ) {
							iconWrapper.find('svg')
								.attr('height', $el.attr('height'));	
						}

						if( self.options.svgViewBox ) {
							iconWrapper.find('svg')
								.attr('viewBox', self.options.svgViewBox);
						}

						$el.replaceWith(iconWrapper);

						if( self.options.extraClass ) {
							iconWrapper.addClass( self.options.extraClass );
						}

						if( self.options.removeClassAfterInit ) {
							iconWrapper.removeClass(self.options.removeClassAfterInit);
						}

						if( self.options.onlySVG ) {
							$(window).trigger('icon.rendered');
							return this;
						}

						$el = iconWrapper;

						const icon = new Vivus(uniqid, {start: 'manual', type: 'sync', selfDestroy: true, duration, onReady({el}) {
							const styleElement = document.createElementNS("http://www.w3.org/2000/svg", "style");
							let animateStyle = '';

							// SVG Fill Based
							if( self.options.animated && !self.options.strokeBased || !self.options.animated && color && !self.options.strokeBased ) {
								animateStyle = 'stroke-width: 0.1px; fill-opacity: 0; transition: ease fill-opacity 300ms;';
								
								// Set Style on SVG inside object
								styleElement.textContent = '#' + uniqid + ' path, #' + uniqid + ' line, #' + uniqid + ' rect, #' + uniqid + ' circle, #' + uniqid + ' polyline { fill: '+ color +'; stroke: '+ color +'; '+ animateStyle + (self.options.svgStyle ? self.options.svgStyle : "") + ' } .finished path { fill-opacity: 1; }';
								el.appendChild(styleElement);
							}

							// SVG Stroke Based
							if( self.options.animated && self.options.strokeBased || !self.options.animated && color && self.options.strokeBased ) {

								// Set Style on SVG inside object
								styleElement.textContent = '#' + uniqid + ' path, #' + uniqid + ' line, #' + uniqid + ' rect, #' + uniqid + ' circle, #' + uniqid + ' polyline { stroke: '+ color +'; ' + (self.options.svgStyle ? self.options.svgStyle : "") + '}';
								el.appendChild(styleElement);
							}

							$.event.trigger('theme.plugin.icon.svg.ready');
						}});

						// Isn't animated
						if( !self.options.animated ) {
							setTimeout(() => {
								icon.finish();
							}, 10);
							$el.css({ opacity: 1 });
						}

						// Animated
						if( self.options.animated && $(window).width() > 767 ) {
							
							// First Load
							if( $el.visible( true ) ) {
								self.startIconAnimation( icon, $el );
							} else if( elTopDistance < windowTopDistance ) {
								self.startIconAnimation( icon, $el );
							}

							// On Scroll
							$(window).on('scroll', () => {
								if( $el.visible( true ) ) {
									self.startIconAnimation( icon, $el );
								}
							});

						} else {
							
							$el.css({ opacity: 1 });
							icon.finish();
							
							$(window).on('theme.plugin.icon.svg.ready', () => {
								setTimeout(() => {
									icon.el.setAttribute('class', 'finished');
									icon.finish();
								}, 300);
							});
							
						}

						$(window).trigger('icon.rendered');
					}
				});
			} else {
				$el.removeAttr('data-icon');
			}

            return this;
        }

        startIconAnimation(icon, $el) {
			const self = this;

			// Animate for better performance
			$({to:0}).animate({to:1}, ((self.options.strokeBased) ? self.options.delay : self.options.delay + 300 ), () => {
				$el.css({ opacity: 1 });
			});

			$({to:0}).animate({to:1}, self.options.delay, () => {
				icon.play(1);

				setTimeout(() => {
					icon.el.setAttribute('class', 'finished');
				}, icon.duration * 5 );
			});
		}
    }

    PluginIcon.defaults = {
		color: '#2388ED',
		animated: false,
		delay: 300,
		onlySVG: false,
		removeClassAfterInit: false,
		fadeIn: true,
		accY: 0
	};

    // expose to scope
    $.extend(theme, {
		PluginIcon
	});

    // jquery plugin
    $.fn.themePluginIcon = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginIcon($this, opts);
			}

		});
	};
})).apply(this, [window.theme, jQuery]);

// In Viewport Style
(((theme = {}, $) => {
    const instanceName = '__inviewportstyle';

    class PluginInViewportStyle {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginInViewportStyle.defaults, opts, {});

			return this;
		}

        build() {
			const self = this, el = self.$el.get(0);

			self.$el.css(self.options.style);

		    if (typeof window.IntersectionObserver === 'function') {
			    const un = observeElementInViewport.observeElementInViewport(
			        el, () => {
			        	self.$el.css(self.options.styleIn);
			        	self.$el
			        		.addClass(self.options.classIn)
			        		.removeClass(self.options.classOut);
			        }, () => {
			        	self.$el.css(self.options.styleOut);
			        	self.$el
			        		.addClass(self.options.classOut)
			        		.removeClass(self.options.classIn);
			        }, {
			        	viewport: self.options.viewport, 
			            threshold: self.options.threshold,
						modTop: self.options.modTop,
						modBottom: self.options.modBottom
			        }
			    )
		    };

			return this;
		}
    }

    PluginInViewportStyle.defaults = {
		viewport: window, 
		threshold: [0],
		modTop: '-200px',
		modBottom: '-200px',
		style: {'transition': 'all 1s ease-in-out'},
		styleIn: '',
		styleOut: '',
		classIn: '',
		classOut: ''
	};

    // expose to scope
    $.extend(theme, {
		PluginInViewportStyle
	});

    // jquery plugin
    $.fn.themePluginInViewportStyle = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginInViewportStyle($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Lightbox
(((theme = {}, $) => {
    const instanceName = '__lightbox';

    class PluginLightbox {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginLightbox.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.magnificPopup))) {
				return this;
			}

			this.options.wrapper.magnificPopup(this.options);

			return this;
		}
    }

    PluginLightbox.defaults = {
		tClose: 'Close (Esc)', // Alt text on close button
		tLoading: 'Loading...', // Text that is displayed during loading. Can contain %curr% and %total% keys
		gallery: {
			tPrev: 'Previous (Left arrow key)', // Alt text on left arrow
			tNext: 'Next (Right arrow key)', // Alt text on right arrow
			tCounter: '%curr% of %total%' // Markup for "1 of 7" counter
		},
		image: {
			tError: '<a href="%url%">The image</a> could not be loaded.' // Error message when image could not be loaded
		},
		ajax: {
			tError: '<a href="%url%">The content</a> could not be loaded.' // Error message when ajax request failed
		},
		callbacks: {
			open() {
				$('html').addClass('lightbox-opened');
			},
			close() {
				$('html').removeClass('lightbox-opened');
			}
		}
	};

    // expose to scope
    $.extend(theme, {
		PluginLightbox
	});

    // jquery plugin
    $.fn.themePluginLightbox = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginLightbox($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Loading Overlay
(((theme = {}, $) => {
    // Default
    const loadingOverlayDefaultTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>',
		'</div>'
	].join('');

    // Percentage
    const loadingOverlayPercentageTemplate = [
		'<div class="loading-overlay loading-overlay-percentage">',
			'<div class="page-loader-progress-wrapper"><span class="page-loader-progress">0</span><span class="page-loader-progress-symbol">%</span></div>',
		'</div>'
	].join('');

    // Cubes
    const loadingOverlayCubesTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-thecube"><div class="cssload-cube cssload-c1"></div><div class="cssload-cube cssload-c2"></div><div class="cssload-cube cssload-c4"></div><div class="cssload-cube cssload-c3"></div></div></div>',
		'</div>'
	].join('');

    // Cube Progress
    const loadingOverlayCubeProgressTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><span class="cssload-cube-progress"><span class="cssload-cube-progress-inner"></span></span></div>',
		'</div>'
	].join('');

    // Float Rings
    const loadingOverlayFloatRingsTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-float-rings-loader"><div class="cssload-float-rings-inner cssload-one"></div><div class="cssload-float-rings-inner cssload-two"></div><div class="cssload-float-rings-inner cssload-three"></div></div></div>',
		'</div>'
	].join('');

    // Floating Bars
    const loadingOverlayFloatBarsTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-float-bars-container"><ul class="cssload-float-bars-flex-container"><li><span class="cssload-float-bars-loading"></span></li></div></div></div>',
		'</div>'
	].join('');

    // Speeding Wheel
    const loadingOverlaySpeedingWheelTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-speeding-wheel-container"><div class="cssload-speeding-wheel"></div></div></div>',
		'</div>'
	].join('');

    // Zenith
    const loadingOverlayZenithTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-zenith-container"><div class="cssload-zenith"></div></div></div>',
		'</div>'
	].join('');

    // Spinning Square
    const loadingOverlaySpinningSquareTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="cssload-spinning-square-loading"></div></div>',
		'</div>'
	].join('');

    // Pulse
    const loadingOverlayPulseTemplate = [
		'<div class="loading-overlay">',
			'<div class="bounce-loader"><div class="wrapper-pulse"><div class="cssload-pulse-loader"></div></div></div>',
		'</div>'
	].join('');

    const LoadingOverlay = function( $wrapper, options, noInheritOptions ) {
		return this.initialize( $wrapper, options, noInheritOptions );
	};

    LoadingOverlay.prototype = {

		options: {
			css: {},
			hideDelay: 500,
			progressMinTimeout: 0,
			effect: 'default'
		},

		initialize($wrapper, options, noInheritOptions) {
			this.$wrapper = $wrapper;

			this
				.setVars()
				.setOptions( options, noInheritOptions )
				.build()
				.events()
				.dynamicShowHideEvents();

			this.$wrapper.data( 'loadingOverlay', this );
		},

		setVars() {
			this.$overlay = this.$wrapper.find('.loading-overlay');
			this.pageStatus = null;
			this.progress = null;
			this.animationInterval = 33;

			return this;
		},

		setOptions(options, noInheritOptions) {
			if ( !this.$overlay.get(0) ) {
				this.matchProperties();
			}
			
			if( noInheritOptions ) {
				this.options     = $.extend( true, {}, this.options, options );
			} else {
				this.options     = $.extend( true, {}, this.options, options, theme.fn.getOptions(this.$wrapper.data('plugin-options')) );
			}

			this.loaderClass = this.getLoaderClass( this.options.css.backgroundColor );

			return this;
		},

		build() {
			const _self = this;

			if ( !this.$overlay.closest(document.documentElement).get(0) ) {
				if ( !this.$cachedOverlay ) {

					switch ( _self.options.effect ) {
						case 'percentageProgress1':
							this.$overlay = $( loadingOverlayPercentageTemplate ).clone();
							break;

						case 'percentageProgress2':
							this.$overlay = $( loadingOverlayPercentageTemplate ).clone();
							this.$overlay
								.addClass('loading-overlay-percentage-effect-2')
								.prepend('<div class="loading-overlay-background-layer"></div>');
							break;

						case 'cubes':
							this.$overlay = $( loadingOverlayCubesTemplate ).clone();
							break;

						case 'cubeProgress':
							this.$overlay = $( loadingOverlayCubeProgressTemplate ).clone();
							break;

						case 'floatRings':
							this.$overlay = $( loadingOverlayFloatRingsTemplate ).clone();
							break;

						case 'floatBars':
							this.$overlay = $( loadingOverlayFloatBarsTemplate ).clone();
							break;

						case 'speedingWheel':
							this.$overlay = $( loadingOverlaySpeedingWheelTemplate ).clone();
							break;

						case 'zenith':
							this.$overlay = $( loadingOverlayZenithTemplate ).clone();
							break;

						case 'spinningSquare':
							this.$overlay = $( loadingOverlaySpinningSquareTemplate ).clone();
							break;

						case 'pulse':
							this.$overlay = $( loadingOverlayPulseTemplate ).clone();
							break;

						case 'default':
						default:
							this.$overlay = $( loadingOverlayDefaultTemplate ).clone();
							break;
					}
					
					if ( this.options.css ) {
						this.$overlay.css( this.options.css );
						this.$overlay.find( '.loader' ).addClass( this.loaderClass );
					}
				} else {
					this.$overlay = this.$cachedOverlay.clone();
				}

				this.$wrapper.prepend( this.$overlay );
			}

			if ( !this.$cachedOverlay ) {
				this.$cachedOverlay = this.$overlay.clone();
			}

			if( ['percentageProgress1', 'percentageProgress2'].includes(_self.options.effect) ) {
				_self.updateProgress();

				if( _self.options.isDynamicHideShow ) {
					setTimeout(() => {
						_self.progress = 'complete';
						
						$('.page-loader-progress').text(100);

						if( ['percentageProgress2'].includes(_self.options.effect) ) {
			            	$('.loading-overlay-background-layer').css({
			            		width: '100%'
			            	});
			            }
					}, 2800);
				}
			}

			return this;
		},

		events() {
			const _self = this;

			if ( this.options.startShowing ) {
				_self.show();
			}

			if ( this.$wrapper.is('body') || this.options.hideOnWindowLoad ) {
				$( window ).on( 'load error', () => {
					setTimeout(() => {
						_self.hide();
					}, _self.options.progressMinTimeout);
				});
			}

			if ( this.options.listenOn ) {
				$( this.options.listenOn )
					.on( 'loading-overlay:show beforeSend.ic', e => {
						e.stopPropagation();
						_self.show();
					})
					.on( 'loading-overlay:hide complete.ic', e => {
						e.stopPropagation();
						_self.hide();
					});
			}

			this.$wrapper
				.on( 'loading-overlay:show beforeSend.ic', e => {
					if ( e.target === _self.$wrapper.get(0) ) {
						e.stopPropagation();
						_self.show();
						return true;
					}
					return false;
				})
				.on( 'loading-overlay:hide complete.ic', e => {
					if ( e.target === _self.$wrapper.get(0) ) {
						e.stopPropagation();
						_self.hide();
						return true;
					}
					return false;
				});

			if( ['percentageProgress1', 'percentageProgress2'].includes(_self.options.effect) ) {
				$(window).on('load', () => {
		            setTimeout(() => {
			            _self.pageStatus = "complete";

			            $('.page-loader-progress').text(100);

			            if( ['percentageProgress2'].includes(_self.options.effect) ) {
			            	$('.loading-overlay-background-layer').css({
			            		width: '100%'
			            	});
			            }
		            }, _self.options.progressMinTimeout);
				});
			}
		        
			return this;
		},

		show() {
			this.build();

			this.position = this.$wrapper.css( 'position' ).toLowerCase();
			if ( this.position != 'relative' || this.position != 'absolute' || this.position != 'fixed' ) {
				this.$wrapper.css({
					position: 'relative'
				});
			}
			this.$wrapper.addClass( 'loading-overlay-showing' );
		},

		hide() {
			const _self = this;

			setTimeout(function() {
				_self.$wrapper.removeClass( 'loading-overlay-showing' );
				
				if ( this.position != 'relative' || this.position != 'absolute' || this.position != 'fixed' ) {
					_self.$wrapper.css({ position: '' });
				}

				$(window).trigger('loading.overlay.ready');
			}, _self.options.hideDelay);
		},

		updateProgress() {
			const _self = this;

			const render = () => {
				if(_self.pageStatus == "complete"){
		            $('.page-loader-progress').text(100);
		            setTimeout(() => {
		                $('.page-loader-progress').addClass('d-none');    
		            }, 700);
		        }
		        else{            
		            if(_self.progress == null){
		                _self.progress = 1;
		            }
		           
		            _self.progress = _self.progress + 1;
		            if(_self.progress >= 0 && _self.progress <= 30){
		                _self.animationInterval += 1;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress > 30 && _self.progress <= 60){
		                _self.animationInterval += 2;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress > 60 && _self.progress <= 80){
		                _self.animationInterval += 40;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress > 80 && _self.progress <= 90){
		                _self.animationInterval += 80;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress > 90 && _self.progress <= 95){
		                _self.animationInterval += 150;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress > 95 && _self.progress <= 99){
		                _self.animationInterval += 400;
		                $('.page-loader-progress').text(_self.progress);
		            }
		            else if(_self.progress >= 100){
		                $('.page-loader-progress').text(99);
		            }

		            if( ['percentageProgress2'].includes(_self.options.effect) ) {
		            	$('.loading-overlay-background-layer').css({
		            		width: _self.progress + '%'
		            	});
		            }
		              
					self.loopInside = setTimeout(render, _self.animationInterval);
		        }

			};
			render();

			return this;
		},

		matchProperties() {
			let i, l, properties;

			properties = [
				'backgroundColor',
				'borderRadius'
			];

			l = properties.length;

			for( i = 0; i < l; i++ ) {
				const obj = {};
				obj[ properties[ i ] ] = this.$wrapper.css( properties[ i ] );

				$.extend( this.options.css, obj );
			}
		},

		getLoaderClass(backgroundColor) {
			if ( !backgroundColor || backgroundColor === 'transparent' || backgroundColor === 'inherit' ) {
				return 'black';
			}

			let hexColor, r, g, b, yiq;

			const colorToHex = color => {
				let hex, rgb;

				if( color.includes('#') ){
					hex = color.replace('#', '');
				} else {
					rgb = color.match(/\d+/g);
					hex = ('0' + parseInt(rgb[0], 10).toString(16)).slice(-2) + ('0' + parseInt(rgb[1], 10).toString(16)).slice(-2) + ('0' + parseInt(rgb[2], 10).toString(16)).slice(-2);
				}

				if ( hex.length === 3 ) {
					hex = hex + hex;
				}

				return hex;
			};

			hexColor = colorToHex( backgroundColor );

			r = parseInt( hexColor.substr( 0, 2), 16 );
			g = parseInt( hexColor.substr( 2, 2), 16 );
			b = parseInt( hexColor.substr( 4, 2), 16 );
			yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;

			return ( yiq >= 128 ) ? 'black' : 'white';
		},

		dynamicShowHide(effect) {
			const _self = this;

			// Remove Loading Overlay Data
			$('body').removeData('loadingOverlay');

			// Remove Html Of Loading Overlay
			$('.loading-overlay').remove();

			if( effect == '' ) {
				return this;
			}

			// Initialize New Loading Overlay (second parameter is to NO inherit data-plugin-options)
			$('body').loadingOverlay({
				effect: effect ? effect : 'pulse',
				isDynamicHideShow: true
			}, true);

			// Show Loading Overlay Loader
			$('body').data('loadingOverlay').show();

			// Hide Loading Overlay Loader
			setTimeout(() => {
				$('body').data('loadingOverlay').hide();
			}, 3000);

			return this;
		},

		dynamicShowHideEvents() {
			const _self = this;

			// Button
			$(document).off('click.loading-overlay-button').on('click.loading-overlay-button', '.loading-overlay-button', function(e){
				e.preventDefault();

				_self.dynamicShowHide( $(this).data('effect') );
			});

			// Select
			$(document).off('change.loading-overlay-select').on('change.loading-overlay-select', '.loading-overlay-select', function(){
				_self.dynamicShowHide( $(this).val() );
			});

			return this;
		}

	};

    // expose to scope
    $.extend(theme, {
		LoadingOverlay
	});

    // expose as a jquery plugin
    $.fn.loadingOverlay = function( opts, noInheritOptions ) {
		return this.each(function() {
			const $this = $( this );

			const loadingOverlay = $this.data( 'loadingOverlay' );
			if ( loadingOverlay ) {
				return loadingOverlay;
			} else {
				const options = opts || $this.data( 'loading-overlay-options' ) || {};
				return new LoadingOverlay( $this, options, noInheritOptions );
			}
		});
	}

    // auto init
    $('[data-loading-overlay]').loadingOverlay();
})).apply(this, [window.theme, jQuery]);

// Masonry
(((theme = {}, $) => {
    const instanceName = '__masonry';

    class PluginMasonry {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginMasonry.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.isotope))) {
				return this;
			}

			const self = this, $window = $(window);

			self.$loader = false;

			if (self.options.wrapper.parents('.masonry-loader').get(0)) {
				self.$loader = self.options.wrapper.parents('.masonry-loader');
				self.createLoader();
			}

			self.options.wrapper.one('layoutComplete', (event, laidOutItems) => {
				self.removeLoader();
			});

			self.options.wrapper.waitForImages(() => {
				self.options.wrapper.isotope(self.options);	
			});

			$(window).on('resize', () => {
				setTimeout(() => {
					self.options.wrapper.isotope('layout');
				}, 300);
			});

			setTimeout(() => {
				self.removeLoader();
			}, 3000);

			return this;
		}

        createLoader() {
			const self = this;

			const loaderTemplate = [
				'<div class="bounce-loader">',
					'<div class="bounce1"></div>',
					'<div class="bounce2"></div>',
					'<div class="bounce3"></div>',
				'</div>'
			].join('');

			self.$loader.append(loaderTemplate);

			return this;
		}

        removeLoader() {

			const self = this;

			if (self.$loader) {

				self.$loader.removeClass('masonry-loader-showing');

				setTimeout(() => {
					self.$loader.addClass('masonry-loader-loaded');
				}, 300);

			}

		}
    }

    PluginMasonry.defaults = {

	};

    // expose to scope
    $.extend(theme, {
		PluginMasonry
	});

    // jquery plugin
    $.fn.themePluginMasonry = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginMasonry($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Match Height
(((theme = {}, $) => {
    const instanceName = '__matchHeight';

    class PluginMatchHeight {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginMatchHeight.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.matchHeight))) {
				return this;
			}

			const self = this;

			self.options.wrapper.matchHeight(self.options);

			return this;
		}
    }

    PluginMatchHeight.defaults = {
		byRow: true,
		property: 'height',
		target: null,
		remove: false
	};

    // expose to scope
    $.extend(theme, {
		PluginMatchHeight
	});

    // jquery plugin
    $.fn.themePluginMatchHeight = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginMatchHeight($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Parallax
(((theme = {}, $) => {
    const instanceName = '__parallax';

    class PluginParallax {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginParallax.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this;
            const $window = $(window);
            let offset;
            let yPos;
            let plxPos;
            let background;
            let rotateY;

            // Mouse Parallax
            if( self.options.mouseParallax ) {

				$window.mousemove(({clientX, clientY}) => {

					$('.parallax-mouse-object', self.options.wrapper).each(function() {

				        const moving_value = $( this ).attr('data-value');
				        const x = (clientX * moving_value) / 250;
				        const y = (clientY * moving_value) / 250;

				        $( this ).css('transform', 'translateX(' + x + 'px) translateY(' + y + 'px)');
				    });

				});

				return this;

			}

            // Scrollable
            if( self.options.scrollableParallax && $(window).width() > self.options.scrollableParallaxMinWidth ) {
				const $scrollableWrapper = self.options.wrapper.find('.scrollable-parallax-wrapper');

				if( $scrollableWrapper.get(0) ) {
                    let progress 	 = ( $(window).scrollTop() > ( self.options.wrapper.offset().top + $(window).outerHeight() ) ) ? self.options.cssValueEnd : self.options.cssValueStart;
                    const cssValueUnit = self.options.cssValueUnit ? self.options.cssValueUnit : '';

                    $scrollableWrapper.css({
						'background-image' : 'url(' + self.options.wrapper.data('image-src') + ')',
						'background-size' : 'cover',
						'background-position' : 'center',
						'background-attachment' : 'fixed',
						'transition' : 'ease '+ self.options.cssProperty +' '+ self.options.transitionDuration,
						'width' : progress + '%'
					});

                    $(window).on('scroll', e => {
						if( self.options.wrapper.visible( true ) ) {
							const $window = $(window), scrollTop = $window.scrollTop(), elementOffset = self.options.wrapper.offset().top, currentElementOffset = (elementOffset - scrollTop);

						   	const scrollPercent = Math.abs( +( currentElementOffset - $window.height() ) / (self.options.startOffset ? self.options.startOffset : 7) );
						 	
						 	// Increment progress value according scroll position
						 	if( scrollPercent <= self.options.cssValueEnd && progress <= self.options.cssValueEnd ) {
						 		progress = self.options.cssValueStart + scrollPercent;
						 	}

						 	// Adjust CSS end value
						 	if( progress > self.options.cssValueEnd ) {
						 		progress = self.options.cssValueEnd;
						 	}

						 	// Adjust CSS start value
						 	if( progress < self.options.cssValueStart ) {
						 		progress = self.options.cssValueStart;
						 	}

						 	const styles = {};
						 	styles[self.options.cssProperty] = progress + cssValueUnit;

							$scrollableWrapper.css(styles);
						}
					});
                }

				return;
			}

            // Create Parallax Element
            if( self.options.fadeIn ) {
				background = $('<div class="parallax-background fadeIn animated"></div>');
			} else {
				background = $('<div class="parallax-background"></div>');
			}

            // Set Style for Parallax Element
            background.css({
				'background-image' : 'url(' + self.options.wrapper.data('image-src') + ')',
				'background-size' : 'cover',
				'position' : 'absolute',
				'top' : 0,
				'left' : 0,
				'width' : '100%',
				'height' : self.options.parallaxHeight
			});

            if( self.options.parallaxScale ) {
				background.css({
					'transition' : 'transform 500ms ease-out'
				});
			}

            // Add Parallax Element on DOM
            self.options.wrapper.prepend(background);

            // Set Overlfow Hidden and Position Relative to Parallax Wrapper
            self.options.wrapper.css({
				'position' : 'relative',
				'overflow' : 'hidden'
			});

            // Parallax Effect on Scroll & Resize
            const parallaxEffectOnScrolResize = () => {
				$window.on('scroll resize', () => {
					offset  = self.options.wrapper.offset();
					yPos    = -($window.scrollTop() - (offset.top - 100)) / ((self.options.speed + 2 ));
					plxPos  = (yPos < 0) ? Math.abs(yPos) : -Math.abs(yPos);
					rotateY = ( $('html[dir="rtl"]').get(0) ) ? ' rotateY(180deg)' : ''; // RTL
					
					offset  = self.options.wrapper.offset();
					yPos    = -($window.scrollTop() - (offset.top - 100)) / ((self.options.speed + 2 ));
					plxPos  = (yPos < 0) ? Math.abs(yPos) : -Math.abs(yPos);
					rotateY = ( $('html[dir="rtl"]').get(0) ) ? ' rotateY(180deg)' : ''; // RTL

					if( !self.options.parallaxScale ) {

						if( self.options.parallaxDirection == 'bottom' ) {
							self.options.offset = 250;
						}

						let y = ( (plxPos - 50) + (self.options.offset) );
						if( self.options.parallaxDirection == 'bottom' ) {
							y = ( y < 0 ) ? Math.abs( y ) : -Math.abs( y );
						}

						background.css({
							'transform' : 'translate3d(0, '+ y +'px, 0)' + rotateY,
							'background-position-x' : self.options.horizontalPosition
						});

					} else {
                        const scrollTop = $window.scrollTop();
                        const elementOffset = self.options.wrapper.offset().top;
                        const currentElementOffset = (elementOffset - scrollTop);
                        let scrollPercent = Math.abs( +( currentElementOffset - $window.height() ) / (self.options.startOffset ? self.options.startOffset : 7) );

                        scrollPercent = parseInt((scrollPercent >= 100) ? 100 : scrollPercent);

                        const currentScale = (scrollPercent / 100) * 50;

                        if ( !self.options.parallaxScaleInvert ) {
							background.css({
								'transform' : 'scale(1.' + String(currentScale).padStart(2, '0') + ', 1.' + String(currentScale).padStart(2, '0') + ')'
							});
						} else {
							background.css({
								'transform' : 'scale(1.' + String(50 - currentScale).padStart(2, '0') + ', 1.' + String(50 - currentScale).padStart(2, '0') + ')'
							});
						}
                    }
				});

				$window.trigger('scroll');
			};

            if (!$.browser.mobile) {
				parallaxEffectOnScrolResize();
			} else {
				if( self.options.enableOnMobile == true ) {
					parallaxEffectOnScrolResize();
				} else {
					self.options.wrapper.addClass('parallax-disabled');
				}
			}

            return this;
        }
    }

    PluginParallax.defaults = {
		speed: 1.5,
		horizontalPosition: '50%',
		offset: 0,
		parallaxDirection: 'top',
		parallaxHeight: '180%',
		parallaxScale: false,
		parallaxScaleInvert: false,
		scrollableParallax: false,
		scrollableParallaxMinWidth: 991,
		startOffset: 7,
		transitionDuration: '200ms',
		cssProperty: 'width',
		cssValueStart: 40,
		cssValueEnd: 100,
		cssValueUnit: 'vw',
		mouseParallax: false,
		enableOnMobile: true
	};

    // expose to scope
    $.extend(theme, {
		PluginParallax
	});

    // jquery plugin
    $.fn.themePluginParallax = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginParallax($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Progress Bar
(((theme = {}, $) => {
    const instanceName = '__progressBar';

    class PluginProgressBar {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginProgressBar.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this;
            const $el = this.options.wrapper;
            let delay = 1;

            delay = ($el.attr('data-appear-animation-delay') ? $el.attr('data-appear-animation-delay') : self.options.delay);

            $el.addClass($el.attr('data-appear-animation'));

            setTimeout(() => {

				$el.animate({
					width: $el.attr('data-appear-progress-animation')
				}, 1500, 'easeOutQuad', () => {
					$el.find('.progress-bar-tooltip').animate({
						opacity: 1
					}, 500, 'easeOutQuad');
				});

			}, delay);

            return this;
        }
    }

    PluginProgressBar.defaults = {
		accX: 0,
		accY: -50,
		delay: 1
	};

    // expose to scope
    $.extend(theme, {
		PluginProgressBar
	});

    // jquery plugin
    $.fn.themePluginProgressBar = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginProgressBar($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Random Images
(((theme = {}, $) => {
    const instanceName = '__randomimages';

    class PluginRandomImages {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			this.$el = $el;
            this.st = '';
			this.times = 0;
			this.perImageIndex = 0;

            if( $el.is('img') && typeof opts.imagesListURL == 'undefined' ) {
                return false;
            }

            this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginRandomImages.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this;
			
			// Control the screens size we want to have the plugin working
			if( $(window).width() < self.options.minWindowWidth  ) {
				return false;
			}

			// Check if is single image or wrapper with images inside
            if( self.$el.is('img') ) {
				
				// Check it's inside a lightbox
				self.isInsideLightbox = self.$el.closest('.lightbox').length ? true : false;

				// Push the initial image to lightbox list/array
				if( self.isInsideLightbox && self.options.lightboxImagesListURL ) {
					self.options.lightboxImagesListURL.push( self.$el.closest('.lightbox').attr('href') );
				}
	
				// Push the current image src to the array
				self.options.imagesListURL.push( self.$el.attr('src') );

				// Start with lastIndex as the first image loaded on the page
				self.lastIndex = self.options.imagesListURL.length - 1;

				// Identify the last random image element (if has more than one on the page)
				if( self.options.random == false ) {
					$('.plugin-random-images').each(function(i){
						if( i == $('.plugin-random-images').length - 1 ) {
							$(this).addClass('the-last');
						}
					});
				}

				// Start the recursive timeout
				setTimeout(() => {
					self.recursiveTimeout( 
						self.perImageTag, 
						self.options.delay == null ? 3000 : self.options.delay
					);
				}, self.options.delay == null ? 300 : self.options.delay / 3);

			} else {
				
				// Start the recursive timeout
				setTimeout( self.recursiveTimeout( 
					self.perWrapper, 
					self.options.delay ? self.options.delay : getPerWrapperHighDelay(), 
					false 
				), 300);

			}

			// Stop After Few Seconds
			if( self.options.stopAfterFewSeconds ) {
				setTimeout(() => {
					clearTimeout(self.st);
				}, self.options.stopAfterFewSeconds);
			}
			
			return this;

		}

        perImageTag() {
			const self = this;

			// Generate a random index to make the images rotate randomly
			let index = self.options.random ? Math.floor(Math.random() * self.options.imagesListURL.length) : self.lastIndex;

			// Avoid repeat the same image
			if( self.lastIndex !== '' && self.lastIndex == index ) {
				if( self.options.random ) {
					while( index == self.lastIndex ) {
						index = Math.floor(Math.random() * self.options.imagesListURL.length);
					}
				} else {
					index = index - 1;
					if( index == -1 ) {
						index = self.options.imagesListURL.length - 1;
					}
				}
			}

			// Turn the image ready for animations
			self.$el.addClass('animated');

			// Remove the entrance animation class and add the out animation class
			self.$el.removeClass( self.options.animateIn ).addClass( self.options.animateOut );
			
			// Change the image src and add the class for entrance animation
			setTimeout( () => {
				self.$el.attr('src', self.options.imagesListURL[index]).removeClass( self.options.animateOut ).addClass(self.options.animateIn);

				if( self.isInsideLightbox && self.options.lightboxImagesListURL ) {
					self.$el.closest('.lightbox').attr('href', self.options.lightboxImagesListURL[index]);
				}
			}, 1000);
			
			// Save the last index for future checks
			self.lastIndex = index;
			
			// Increment the times var
			self.times++;

			// Save the index for stopAtImageIndex option
			self.perImageIndex = index;

			return this;
		}

        // Iterate the imaes loop and get the higher value
        getPerWrapperHighDelay() {
            const self = this;
            const $wrapper = self.$el;
            let delay = 0;

            $wrapper.find('img').each(function(){
				const $image = $(this);
				
				if( $image.data('rimage-delay') && parseInt( $image.data('rimage-delay') ) > delay ) {
					delay = parseInt( $image.data('rimage-delay') );
				}
			});

            return delay;
        }

        perWrapper() {
			const self = this, $wrapper = self.$el;

			// Turns the imageLlistURL into an array
			self.options.imagesListURL = [];

			// Find all images inside the element wrapper and push their sources to image list array
			$wrapper.find('img').each(function(){
				const $image = $(this);
				self.options.imagesListURL.push( $image.attr('src') ); 
			});

			// Shuffle the images list array (random effect)
			self.options.imagesListURL = self.shuffle( self.options.imagesListURL );

			// Iterate over each image and make some checks like delay for each image, animations, etc...
			$wrapper.find('img').each(function(index){
				const $image = $(this), animateIn  = $image.data('rimage-animate-in') ? $image.data('rimage-animate-in') : self.options.animateIn, animateOut = $image.data('rimage-animate-out') ? $image.data('rimage-animate-out') : self.options.animateOut, delay      = $image.data('rimage-delay') ? $image.data('rimage-delay') : 2000;

				$image.addClass('animated');

				setTimeout( () => {
					$image.removeClass( animateIn ).addClass( animateOut );
				}, delay / 2);

				setTimeout( () => {
					$image.attr('src', self.options.imagesListURL[index]).removeClass( animateOut ).addClass(animateIn);
				}, delay);

			});
			
			// Increment the times variable
			self.times++;

			return this;
		}

        recursiveTimeout(callback, delay) {
			const self = this;

			const timeout = () => {

				if( callback !== null ) {
					callback.call(self);
				}

				// Recursive
				self.st = setTimeout(timeout, delay == null ? 1000 : delay);

				if( self.options.random == false ) {
					if( self.$el.hasClass('the-last') ) {
						$('.plugin-random-images').trigger('rimages.start');
					} else {
						clearTimeout(self.st);
					}
				}

				// Stop At Image Index
				if( self.options.stopAtImageIndex && parseInt(self.options.stopAtImageIndex) == self.perImageIndex ) {
					clearTimeout(self.st);
				}

				// Stop After X Timers
				if( self.options.stopAfterXTimes == self.times ) {
					clearTimeout(self.st);
				}
			};
			timeout();

			self.$el.on('rimages.start', () => {
				clearTimeout(self.st);
				self.st = setTimeout(timeout, delay == null ? 1000 : delay);
			});

		}

        shuffle(array) {
			for (let i = array.length - 1; i > 0; i--) {
				const j = Math.floor(Math.random() * (i + 1));
				const temp = array[i];
				array[i] = array[j];
				array[j] = temp;
			}

			return array;
		}
    }

    PluginRandomImages.defaults = {
		minWindowWidth: 0,
		random: true,
		imagesListURL: null,
		lightboxImagesListURL: null,
        delay: null,
        animateIn: 'fadeIn',
		animateOut: 'fadeOut',
		stopAtImageIndex: false, // The value shoudl be the index value of array with images as string. Eg: '2' 
		stopAfterFewSeconds: false, // The value should be in mili-seconds. Eg: 10000 = 10 seconds
		stopAfterXTimes: false,
		accY: 0
	};

    // expose to scope
    $.extend(theme, {
		PluginRandomImages
	});

    // jquery plugin
    $.fn.themePluginRandomImages = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginRandomImages($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Read More
(((theme = {}, $) => {
    const instanceName = '__readmore';

    class PluginReadMore {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			const self = this;

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			if( self.options.startOpened ) {
				self.options.wrapper.find('.readmore-button-wrapper > a').trigger('click');
			}

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginReadMore.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			self.options.wrapper.addClass('position-relative');

			// Overlay
			self.options.wrapper.append( '<div class="readmore-overlay"></div>' );

			// Check if is Safari
			let backgroundCssValue = 'linear-gradient(180deg, rgba(2, 0, 36, 0) 0%, '+ self.options.overlayColor +' 100%)';
			if( $('html').hasClass('safari') ) {
				backgroundCssValue = '-webkit-linear-gradient(top, rgba(2, 0, 36, 0) 0%, '+ self.options.overlayColor +' 100%)'
			}
			
			self.options.wrapper.find('.readmore-overlay').css({
				background: backgroundCssValue,
				position: 'absolute',
				bottom: 0,
				left: 0,
				width: '100%',
				height: self.options.overlayHeight,
				'z-index': 1
			});

			// Read More Button
			self.options.wrapper.find('.readmore-button-wrapper').removeClass('d-none').css({
				position: 'absolute',
				bottom: 0,
				left: 0,
				width: '100%',
				'z-index': 2
			});	

			// Button Label
			self.options.wrapper.find('.readmore-button-wrapper > a').html( self.options.buttonOpenLabel );

			self.options.wrapper.css({
				'height': self.options.maxHeight,
				'overflow-y': 'hidden'
			});

			// Alignment
			switch ( self.options.align ) {
				case 'center':
					self.options.wrapper.find('.readmore-button-wrapper').addClass('text-center');
					break;

				case 'end':
					self.options.wrapper.find('.readmore-button-wrapper').addClass('text-end');
					break;

				case 'start':
				default:
					self.options.wrapper.find('.readmore-button-wrapper').addClass('text-start');
					break;
			}

			return this;

		}

        events() {
			const self = this;

			// Read More
			self.readMore = () => {
				self.options.wrapper.find('.readmore-button-wrapper > a:not(.readless)').on('click', function(e){
					e.preventDefault();

					const $this = $(this);

					setTimeout(() => {
						self.options.wrapper.animate({
							'height': self.options.wrapper[0].scrollHeight
						}, () => {
							if( !self.options.enableToggle ) {
								$this.fadeOut();
							}

							$this.html( self.options.buttonCloseLabel ).addClass('readless').off('click');

							self.readLess();

							self.options.wrapper.find('.readmore-overlay').fadeOut();
							self.options.wrapper.css({
								'max-height': 'none',
								'overflow': 'visible'
							});

							self.options.wrapper.find('.readmore-button-wrapper').animate({
								bottom: -20
							});
						});
					}, 200);
				});
			}

			// Read Less
			self.readLess = () => {
				self.options.wrapper.find('.readmore-button-wrapper > a.readless').on('click', function(e){
					e.preventDefault();

					const $this = $(this);

					// Button
					self.options.wrapper.find('.readmore-button-wrapper').animate({
						bottom: 0
					});

					// Overlay
					self.options.wrapper.find('.readmore-overlay').fadeIn();

					setTimeout(() => {
						self.options.wrapper.height(self.options.wrapper[0].scrollHeight).animate({
							'height': self.options.maxHeight
						}, () => {
							$this.html( self.options.buttonOpenLabel ).removeClass('readless').off('click');

							self.readMore();

							self.options.wrapper.css({
								'overflow': 'hidden'
							});
						});
					}, 200);
				});
			}

			// First Load
			self.readMore();

			return this;
		}
    }

    PluginReadMore.defaults = {
		buttonOpenLabel: 'Read More <i class="fas fa-chevron-down text-2 ms-1"></i>',
		buttonCloseLabel: 'Read Less <i class="fas fa-chevron-up text-2 ms-1"></i>',
		enableToggle: true,
		maxHeight: 110,
		overlayColor: '#FFF',
		overlayHeight: 100,
		startOpened: false,
		align: 'left'
	};

    // expose to scope
    $.extend(theme, {
		PluginReadMore
	});

    // jquery plugin
    $.fn.themePluginReadMore = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginReadMore($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Revolution Slider
(((theme = {}, $) => {
    const instanceName = '__revolution';

    class PluginRevolutionSlider {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginRevolutionSlider.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.revolution))) {
				return this;
			}

			// Single Slider Class
			if(this.options.wrapper.find('> ul > li').length == 1) {
				this.options.wrapper.addClass('slider-single-slide');

				// Remove Bullets
				// this.options.navigation.bullets.enable = false;
				$.extend(this.options.navigation, {
					bullets: {
						enable: false
					}
				});

			}

			// Full Screen Class
			if(this.options.sliderLayout == 'fullscreen') {
				this.options.wrapper.closest('.slider-container').addClass('fullscreen-slider');
			}
			
			// Initialize Revolution Slider
			this.options.wrapper.revolution(this.options);

			// Addon Init - Typewriter
			if(this.options.addOnTypewriter.enable) {
				RsTypewriterAddOn($, this.options.wrapper);
			}

			// Addon Init - Whiteboard
			if(this.options.addOnWhiteboard.enable) {
				this.options.wrapper.rsWhiteBoard();
			}

			// Addon Init - Particles
			if(this.options.addOnParticles.enable) {
				RsParticlesAddOn(this.options.wrapper);
			}

			// Addon Init - Countdown
			if(this.options.addOnCountdown.enable) {
				tp_countdown(this.options.wrapper, this.options.addOnCountdown.targetdate, this.options.addOnCountdown.slidechanges);
			}

			// Addon Init - Slicey
			if(this.options.addOnSlicey.enable) {
				this.options.wrapper.revSliderSlicey();
			}

			// Addon Init - Filmstrip
			if(this.options.addOnFilmstrip.enable) {
				RsFilmstripAddOn($, this.options.wrapper, '../vendor/rs-plugin/revolution-addons/filmstrip/', false);
			}

			// Addon Init - Before After
			if(this.options.addOnBeforeAfter.enable) {
				RevSliderBeforeAfter($, this.options.wrapper, this.options.addOnBeforeAfter.options);
			}

			// Addon Init - Panorama
			if(this.options.addOnPanorama.enable) {
				RsAddonPanorama($, this.options.wrapper);
			}

			// Addon Init - Revealer
			if(this.options.addOnRevealer.enable) {
				RsRevealerAddOn($, this.options.wrapper, this.options.revealer.spinnerHtml);
			}

			// Addon Init - Duotone
			if(this.options.addOnDuotone.enable) {
				RsAddonDuotone($, this.options.wrapper, true, "cubic-bezier(0.645, 0.045, 0.355, 1.000)", "1000");
			}

			// Addon Init - Bubblemorph
			if(this.options.addOnBubblemorph.enable) {
				BubbleMorphAddOn($, this.options.wrapper, false);
			}

			// Addon Init - Distortion
			if(this.options.addOnDistortion.enable) {
				RsLiquideffectAddOn($, this.options.wrapper);
			}

			return this;
		}

        events() {

			return this;
		}
    }

    PluginRevolutionSlider.defaults = {
		sliderType: 'standard',
		sliderLayout: 'fullwidth',
		delay: 9000,
		gridwidth: 1170,
		gridheight: 500,
		spinner: 'spinner3',
		disableProgressBar: 'on',
		parallax: {
			type: 'off',
			bgparallax: 'off'
		},
		navigation: {
			keyboardNavigation: 'off',
			keyboard_direction: 'horizontal',
			mouseScrollNavigation: 'off',
			onHoverStop: 'off',
			touch: {
				touchenabled: 'on',
				swipe_threshold: 75,
				swipe_min_touches: 1,
				swipe_direction: 'horizontal',
				drag_block_vertical: false
			},
			arrows: {
				enable: true,
				hide_onmobile: false,
				hide_under: 0,
				hide_onleave: true,
				hide_delay: 200,
				hide_delay_mobile: 1200,
				left: {
					h_align: 'left',
					v_align: 'center',
					h_offset: 30,
					v_offset: 0
				},
				right: {
					h_align: 'right',
					v_align: 'center',
					h_offset: 30,
					v_offset: 0
				}
			}
		},

		/* ADDONS */
	    addOnTypewriter: {
			enable: false
		},
		addOnWhiteboard: {
			enable: false,

		},
	    whiteboard: {
	        movehand: {
	            src: '../vendor/rs-plugin/revolution-addons/whiteboard/assets/images/hand_point_right.png',
	            width: 400,
	            height: 1000,
	            handtype: 'right',
	            transform: {
	                transformX: 50,
	                transformY: 50
	            },
	            jittering: {
	                distance: '80',
	                distance_horizontal: '100',
	                repeat: '5',
	                offset: '10',
	                offset_horizontal: '0'
	            },
	            rotation: {
	                angle: '10',
	                repeat: '3'
	            }
	        },
	        writehand: {
	            src: '../vendor/rs-plugin/revolution-addons/whiteboard/assets/images/write_right_angle.png',
	            width: 572,
	            height: 691,
	            handtype: 'right',
	            transform: {
	                transformX: 50,
	                transformY: 50
	            },
	            jittering: {
	                distance: '80',
	                distance_horizontal: '100',
	                repeat: '5',
	                offset: '10',
	                offset_horizontal: '0'
	            },
	            rotation:{
	                angle: '10',
	                repeat: '3'
	            }
	        }
	    },
	    addOnParticles: {
	    	enable: false
	    },
	    particles: {
			startSlide: "first", 
			endSlide: "last", 
			zIndex: "1",
			particles: {
				number: {value: 80}, color: {value: "#ffffff"},
				shape: {
					type: "circle", stroke: {width: 0, color: "#ffffff", opacity: 1},
					image: {src: ""}
				},
				opacity: {value: 0.5, random: true, min: 0.25, anim: {enable: false, speed: 3, opacity_min: 0, sync: false}},
				size: {value: 2, random: false, min: 30, anim: {enable: false, speed: 40, size_min: 1, sync: false}},
				line_linked: {enable: true, distance: 150, color: "#ffffff", opacity: 0.4, width: 1},
				move: {enable: true, speed: 6, direction: "none", random: true, min_speed: 6, straight: false, out_mode: "out"}
			},
			interactivity: {
				events: {onhover: {enable: false, mode: "repulse"}, onclick: {enable: false, mode: "repulse"}},
				modes: {grab: {distance: 400, line_linked: {opacity: 0.5}}, bubble: {distance: 400, size: 40, opacity: 0.4}, repulse: {distance: 200}}
			}
		},
		addOnCountdown: {
			enable: false,
			targetdate: new Date().getTime() + 864000000, // http://www.freeformatter.com/epoch-timestamp-to-date-converter.html
			slidechanges: [{days: 0, hours: 0, minutes: 0, seconds: 0, slide: 2}]
		},
		addOnSlicey: {
			enable: false
		},
		addOnFilmstrip: {
			enable: false
		},
		addOnBeforeAfter : {
			enable: false,
			options: {
				cursor: "move",
			    carousel: false,
			    arrowStyles: {
			        leftIcon: "fa-icon-caret-left",
			        rightIcon: "fa-icon-caret-right",
			        topIcon: "fa-icon-caret-up",
			        bottomIcon: "fa-icon-caret-down",
			        size: "35",
			        color: "#ffffff",
			        spacing: "10",
			        bgColor: "transparent",
			        padding: "0",
			        borderRadius: "0"
			    },
			    dividerStyles: {
			        width: "1",
			        color: "rgba(255, 255, 255, 0.5)"
			    }
			}
		},
		addOnPanorama: {
			enable: false
		},
		addOnRevealer: {
			enable: false,
		},
		revealer: {
			direction: "open_horizontal",
			color: "#ffffff",
			duration: "1500",
			delay: "0",
			easing: "Power2.easeInOut",
			overlay_enabled: true,
			overlay_color: "#000000",
			overlay_duration: "1500",
			overlay_delay: "0",
			overlay_easing: "Power2.easeInOut",
			spinner: "1",
			spinnerColor: "#006dd2",
			spinnerHtml: "<div class='rsaddon-revealer-spinner rsaddon-revealer-spinner-1'><div class='rsaddon-revealer-1'><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><span style='background: {{color}}'><\/span><\/div><\/div \/>"
		},
		addOnDuotone: {
			enable: false
		},
		addOnBubblemorph: {
			enable: false
		},
		addOnDistortion: {
			enable: false
		}
		
	};

    // expose to scope
    $.extend(theme, {
		PluginRevolutionSlider
	});

    // jquery plugin
    $.fn.themePluginRevolutionSlider = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginRevolutionSlider($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Scroll Spy
(((theme = {}, $) => {
    const instanceName = '__scrollSpy';

    class PluginScrollSpy {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {

			if( document.querySelector( opts.target ) == null ) {
				return false;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts);
			
			this.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginScrollSpy.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this, target = document.querySelector( self.options.target ) != null ? document.querySelector( self.options.target ) : false, navItems = target == '#header' || target == '.wrapper-spy' ? target.querySelectorAll('.header-nav .nav > li a') : target.querySelectorAll('.nav > li a');

			// Get all section ID's
			let sectionIDs = Object.keys(navItems).map((key, index) => navItems[key].hash);

			// Remove empty values from sectionIDs array
			sectionIDs = sectionIDs.filter(value => value != '');

			// Store in a global variable
			self.sectionIDs = sectionIDs;

			for( let i = 0; i < sectionIDs.length; i++ ) {

				// Default Root Margin
				let rootMargin = '-20% 0px -79.9% 0px';
				
				// Spy Offset
				if( $( sectionIDs[i] ).data('spy-offset') ) {
					const rootMarginOffset = $( sectionIDs[i] ).data('spy-offset'), isNegativeOffset = parseInt( rootMarginOffset ) < 0 ? true : false;

					// Mount a new rootMargin based on offset value
					rootMargin = rootMargin.split(' ').map((element, index) => {
						if( element.indexOf('%') > 0 ) {
                            const valueToInt = parseInt( element.replace('%','') );
                            let newValue = 0;

                            switch ( index ) {
								case 0:
									if( isNegativeOffset ) {
										newValue = valueToInt - rootMarginOffset;
									} else {
										newValue = Math.abs(valueToInt) + rootMarginOffset;
									}
									break;

								case 2:
									if( isNegativeOffset ) {
										newValue = valueToInt + rootMarginOffset;
									} else {
										newValue = Math.abs(valueToInt) - rootMarginOffset;
									}
									break;
							
							}

                            if( isNegativeOffset ) {
								newValue = newValue + '%';
							} else {
								newValue = '-' + newValue + '%';
							}

                            return newValue;
                        } else {
							return element;
						}
					}).join(' ');
				}

				const selector = sectionIDs[i],
                      callback = function() {
                          const $section = $(this);

                          if( target == '#header' || target == '.wrapper-spy' ) {
                              $('#header .header-nav .nav > li a').removeClass('active');
                              $('#header .header-nav .nav > li a[href="#'+ $section[0].id +'"]').addClass('active');
                          } else {
                              $( target ).find('.nav > li a').removeClass('active');
                              $( target ).find('.nav > li a[href="#'+ $section[0].id +'"]').addClass('active');
                          }
                          
                      };

				this.scrollSpyIntObs( selector, callback, { 
					rootMargin,
					threshold: 0
				}, true, i, true);

            }

            return this;

		}

        scrollSpyIntObs(selector, functionName, intObsOptions, alwaysObserve, index, firstLoad) {
			const self = this;

			const $el = document.querySelectorAll( selector );
			let intersectionObserverOptions = {
				rootMargin: '0px 0px 200px 0px'
			};

			if( Object.keys(intObsOptions).length ) {
				intersectionObserverOptions = $.extend(intersectionObserverOptions, intObsOptions);
			}

			const observer = new IntersectionObserver(entries => {
                for (const entry of entries) {
                    if (entry.intersectionRatio > 0 ) {
						if( typeof functionName === 'string' ) {
							const func = Function( 'return ' + functionName )();
						} else {
							const callback = functionName;

							callback.call( $(entry.target) );
						}

						// Unobserve
						if( !alwaysObserve ) {
							observer.unobserve(entry.target);   
						}

					} else {
						if( firstLoad == false ) {
							if( index == self.sectionIDs.length - 1 ) {
								$('#header .header-nav .nav > li a').removeClass('active');
								$('#header .header-nav .nav > li a[href="#'+ entry.target.id +'"]').parent().prev().find('a').addClass('active');
							}
						}
						firstLoad = false;

					}
                }
            }, intersectionObserverOptions);
			
			$( $el ).each(function(){
				observer.observe( $(this)[0] );
			});

			return this;
		}
    }

    PluginScrollSpy.defaults = {
		target: '#header'
	};

    // expose to scope
    $.extend(theme, {
		PluginScrollSpy
	});

    // jquery plugin
    $.fn.themePluginScrollSpy = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginScrollSpy($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Scroll to Top
(((theme = {}, $) => {
    $.extend(theme, {

		PluginScrollToTop: {

			defaults: {
				wrapper: $('body'),
				offset: 150,
				buttonClass: 'scroll-to-top',
				buttonAriaLabel: 'Scroll To Top',
				iconClass: 'fas fa-chevron-up',
				delay: 1000,
				visibleMobile: false,
				label: false,
				easing: 'easeOutBack'
			},

			initialize(opts) {
				initialized = true;

				// Don't initialize if the page has Section Scroll
				if( $('body[data-plugin-section-scroll]').get(0) ) {
					return;
				}

				this
					.setOptions(opts)
					.build()
					.events();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts);

				return this;
			},

			build() {
                const self = this;
                let $el;

                // Base HTML Markup
                $el = $('<a />')
					.addClass(self.options.buttonClass)
					.attr({
						'href': '#',
						'aria-label': self.options.buttonAriaLabel
					})
					.append(
						$('<i />')
						.addClass(self.options.iconClass)
				);

                // Visible Mobile
                if (!self.options.visibleMobile) {
					$el.addClass('hidden-mobile');
				}

                // Label
                if (self.options.label) {
					$el.append(
						$('<span />').html(self.options.label)
					);
				}

                this.options.wrapper.append($el);

                this.$el = $el;

                return this;
            },

			events() {
                const self = this;
                let _isScrolling = false;

                // Click Element Action
                self.$el.on('click', e => {
					e.preventDefault();
					$('html').animate({
						scrollTop: 0
					}, self.options.delay, self.options.easing);
					return false;
				});

                // Show/Hide Button on Window Scroll event.
                $(window).scroll(() => {

					if (!_isScrolling) {

						_isScrolling = true;

						if ($(window).scrollTop() > self.options.offset) {

							self.$el.stop(true, true).addClass('visible');
							_isScrolling = false;

						} else {

							self.$el.stop(true, true).removeClass('visible');
							_isScrolling = false;

						}

					}

				});

                return this;
            }

		}

	});
})).apply(this, [window.theme, jQuery]);

// Scrollable
(((theme = {}, $) => {
    const instanceName = '__scrollable';

    class PluginScrollable {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        static updateModals() {
            PluginScrollable.updateBootstrapModal();
        }

        static updateBootstrapModal() {
            let updateBoostrapModal;

            updateBoostrapModal = typeof $.fn.modal !== 'undefined';
            updateBoostrapModal = updateBoostrapModal && typeof $.fn.modal.Constructor !== 'undefined';
            updateBoostrapModal = updateBoostrapModal && typeof $.fn.modal.Constructor.prototype !== 'undefined';
            updateBoostrapModal = updateBoostrapModal && typeof $.fn.modal.Constructor.prototype.enforceFocus !== 'undefined';

            if ( !updateBoostrapModal ) {
                return false;
            }

            const originalFocus = $.fn.modal.Constructor.prototype.enforceFocus;
            $.fn.modal.Constructor.prototype.enforceFocus = function() {
                originalFocus.apply( this );

                const $scrollable = this.$element.find('.scrollable');
                if ( $scrollable ) {
                    if ( $.isFunction($.fn['themePluginScrollable'])  ) {
                        $scrollable.themePluginScrollable();
                    }

                    if ( $.isFunction($.fn['nanoScroller']) ) {
                        $scrollable.nanoScroller();
                    }
                }
            };
        }

        initialize($el, opts) {
			if ( $el.data( instanceName ) ) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginScrollable.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			this.options.wrapper.nanoScroller(this.options);

			return this;
		}
    }

    PluginScrollable.defaults = {
		contentClass: 'scrollable-content',
		paneClass: 'scrollable-pane',
		sliderClass: 'scrollable-slider',
		alwaysVisible: true,
		preventPageScrolling: true
	};

    // expose to scope
    $.extend(theme, {
		PluginScrollable
	});

    // jquery plugin
    $.fn.themePluginScrollable = function(opts) {
		return this.each(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginScrollable($this, opts);
			}

		});
	};

    $(() => {
		PluginScrollable.updateModals();
	});
})).apply(this, [window.theme, jQuery]);

// Section Scroll
(((theme = {}, $) => {
    const instanceName = '__sectionScroll';

    class PluginSectionScroll {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginSectionScroll.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this, $el = this.options.wrapper;

			// Check type of header and change the target for header (by change header color purpose)
			if( $('html').hasClass('side-header-overlay-full-screen') ) {
				self.$header = $('.sticky-wrapper');
			} else {
				self.$header = $('#header');
			}

			// Turn the section full height or not depeding on the content size
			self.updateSectionsHeight();

			// Wrap all sections in a section wrapper
			$( this.options.targetClass ).wrap('<div class="section-wrapper"></div>');

			// Set the section wrapper height
	  		$('.section-wrapper').each(function(){
	  			$(this).height( $(this).find('.section-scroll').outerHeight() );
	  		});

	  		// Add active class to the first section on page load
	  		$('.section-wrapper').first().addClass('active');
			
	        let flag = false, scrollableFlag = false, touchDirection = '', touchstartY = 0, touchendY = 0;

	        $(window).on('touchstart', ({changedTouches}) => {
			    touchstartY = changedTouches[0].screenY;
			});

	        let wheelEvent = 'onwheel' in document ? 'wheel' : document.onmousewheel !== undefined ? 'mousewheel' : 'DOMMouseScroll';
	        if( $(window).width() < 992 && $('html').hasClass('touch') ) {
	        	wheelEvent = 'onwheel' in document ? 'wheel touchend' : document.onmousewheel !== undefined ? 'mousewheel touchend' : 'DOMMouseScroll touchend';
	        }

        	if( $(window).width() < 992 ) {
	    		$('html').removeClass('overflow-hidden');
			    $(window).on('scroll', () => {

		    		let index = 0;
		    		$('.section-scroll').each(function(){
		    			if( $(this).offset().top <= $(window).scrollTop() + 50 ) {
		    				const $currentSection2 = $('.section-wrapper').eq( index ).find('.section-scroll');

			            	$('.section-scroll-dots-navigation > ul > li').removeClass('active');
							$('.section-scroll-dots-navigation > ul > li').eq( index ).addClass('active');

							$(window).trigger({
								type: 'section.scroll.mobile.change.header.color',
								currentSection: $currentSection2
							});
		    			}

		    			index++;
		    		});
		    		
			    });

			    $(window).on('section.scroll.mobile.change.header.color', ({currentSection}) => {
			    	if( typeof currentSection == 'undefined' ) {
			    		return;
			    	}

			    	const $currentSection = currentSection, headerColor     = $currentSection.data('section-scroll-header-color');
								    	
			    	$('#header .header-nav').removeClass('header-nav-light-text header-nav-dark-text').addClass('header-nav-' + headerColor + '-text');
			    	$('#header .header-nav-features').removeClass('header-nav-features-dark header-nav-features-light').addClass('header-nav-features-' + headerColor);
			    	$('#header .header-social-icons').removeClass('social-icons-icon-dark social-icons-icon-light').addClass('social-icons-icon-' + headerColor);

			    	// Change Logo
			    	if( self.options.changeHeaderLogo && headerColor != undefined ) {
				    	if( headerColor == 'light' ) {
				    		$('#header .header-logo img').attr('src', self.options.headerLogoLight);
				    	} else if( headerColor == 'dark' ) {
				    		$('#header .header-logo img').attr('src', self.options.headerLogoDark);
				    	}
			    	}

			    	self.$header.css({
			    		opacity: 1
			    	});

			    });
        	}

	        $(window).on(wheelEvent, ({target, originalEvent}) => {
                if( $(window).width() < 992 ) {
	        		return;
	        	}

                if( $(window).width() < 992 && $('html').hasClass('touch') ) {
		        	if( $(target).closest('.section-scroll-dots-navigation').get(0) || $(target).closest('.header-body').get(0) || $(target).closest('.owl-carousel').get(0) ) {
		        		return;
		        	}
		        }

                // Side Header Overlay Full Screen
                if( $('html.side-header-overlay-full-screen.side-header-hide').get(0) ) {
		        	return;
		        }

                const wheelDirection = originalEvent.wheelDelta == undefined ? originalEvent.deltaY > 0 : originalEvent.wheelDelta < 0;
                if( $(window).width() < 992 && $('html').hasClass('touch') ) {
		        	touchendY = event.changedTouches[0].screenY;
	        		
				    if( touchendY <= touchstartY ) {
				    	touchDirection = 'up';
				    }

				    if( touchendY >= touchstartY ) {
				    	touchDirection = 'down';
				    }

				    if( touchendY == touchstartY ) {
				    	return;
				    }
	        	}

                const $currentSection = $('.section-wrapper').eq( self.getCurrentIndex() ).find('.section-scroll');
                const $nextSection = self.getNextSection(wheelDirection, touchDirection);
                let nextSectionOffsetTop;

                // If is the last section, then change the offsetTop value
                if( self.getCurrentIndex() == $('.section-wrapper').length - 1 ) {
            		nextSectionOffsetTop = $(document).height();
            	} else {
            		nextSectionOffsetTop = $nextSection.offset().top;
            	}

                if( $(window).width() < 992 && $('html').hasClass('touch') ) {
				    setTimeout(() => {
					    if( $('.section-wrapper').eq( self.getCurrentIndex() ).find('.section-scroll').hasClass('section-scroll-scrollable') ) {
					    	$('html').removeClass('overflow-hidden');
					    } else {
					    	$('html').addClass('overflow-hidden');
					    }
				    }, 1200);
				}

                // For non full height sections
                if( $currentSection.hasClass('section-scroll-scrollable') ) {
	        		if( !flag && !scrollableFlag ) {

		        		// Scroll Direction
		        		if(wheelDirection || touchDirection == 'up') {
		        			if( ( $(window).scrollTop() + $(window).height() ) >= nextSectionOffsetTop ) {
		        				flag = true;
								setTimeout(() => {
									$(window).trigger('section.scroll.change.header.color');

					            	setTimeout(() => {
										flag = false;
									}, 500);
								}, 1000);

		        				if( self.getCurrentIndex() == ( $('.section-wrapper').length - 1 )  ) {
						    		return false;
						    	}

		        				// Move to the next section
		        				self.moveTo( $currentSection.offset().top + $currentSection.outerHeight() );

		        				// Change Section Active Class
					   			self.changeSectionActiveState( $nextSection );

					   			self.$header.css({
							    	opacity: 0,
							    	transition: 'ease opacity 500ms'
							    });
					        }

			        		if( !$('html').hasClass('touch') ) {
				        		for( var i = 1; i < 100; i++ ) {
					        		$('body, html').scrollTop( $(window).scrollTop() + 1 );

					        		if( ( $(window).scrollTop() + $(window).height() ) >= nextSectionOffsetTop ) {
					        			scrollableFlag = true;
										setTimeout(() => {
											$(window).trigger('section.scroll.change.header.color');
							            	scrollableFlag = false;
										}, 500);
					        			break;
					        		}
				        		}
				        	}
					    } else {
					    	if( $(window).scrollTop() <= $currentSection.offset().top ) {
					    		flag = true;
								setTimeout(() => {
									$(window).trigger('section.scroll.change.header.color');

					            	setTimeout(() => {
										flag = false;
									}, 500);
								}, 1000);

					    		if( self.getCurrentIndex() == 0  ) {
						    		return false;
						    	}

					   			// Move to the next section
		        				self.moveTo( $currentSection.offset().top - $(window).height() );

		        				// Change Section Active Class
					   			self.changeSectionActiveState( $nextSection );

					   			self.$header.css({
							    	opacity: 0,
							    	transition: 'ease opacity 500ms'
							    });
					        }

					    	if( !$('html').hasClass('touch') ) {
				        		for( var i = 1; i < 100; i++ ) {
					        		$('body, html').scrollTop( $(window).scrollTop() - 1 );

					        		if( $(window).scrollTop() <= $currentSection.offset().top ) {
					        			scrollableFlag = true;
										setTimeout(() => {
											$(window).trigger('section.scroll.change.header.color');
							            	scrollableFlag = false;
										}, 500);
					        			break;
					        		}
				        		}
				        	}
					    }

			   			// Change Dots Active Class
				        self.changeDotsActiveState();

		        		return;

		        	}
	        	}

                // For full height sections
                if( !flag && !scrollableFlag ) {
				    if(wheelDirection || touchDirection == 'up') {
				    	if( self.getCurrentIndex() == ( $('.section-wrapper').length - 1 )  ) {
				    		return false;
				    	}

				   		// Change Section Active Class
			   			self.changeSectionActiveState( $nextSection );

				   		setTimeout(() => {
				   			// Move to the next section
	        				self.moveTo( $nextSection.offset().top );

				   		}, 150);
				    } else {
				    	if( self.getCurrentIndex() == 0  ) {
				    		return false;
				    	}

				   		// Change Section Active Class
			   			self.changeSectionActiveState( $nextSection );

				   		if( $nextSection.height() > $(window).height() ) {
				   			// Move to the next section
	        				self.moveTo( $currentSection.offset().top - $(window).height() );
				   		} else {
					        setTimeout(() => {
					   			// Move to the next section
		        				self.moveTo( $nextSection.offset().top );

					   		}, 150);
				   		}
				    }

				    // Change Dots Active Class
			        self.changeDotsActiveState();

				    self.$header.css({
				    	opacity: 0,
				    	transition: 'ease opacity 500ms'
				    });

				    // Style next section
	            	$nextSection.css({
	            		position: 'relative',
	            		opacity: 1,
	            		'z-index': 1,
	            		transform: 'translate3d(0,0,0) scale(1)'
	            	});

	            	// Style previous section
	            	$currentSection.css({
	            		position: 'fixed',
	            		width: '100%',
	            		top: 0,
	            		left: 0,
	            		opacity: 0,
	            		'z-index': 0,
	            		transform: 'translate3d(0,0,-10px) scale(0.7)',
	            		transition: 'ease transform 600ms, ease opacity 600ms',
	            	});

					setTimeout(() => {
						$currentSection.css({
		            		position: 'relative',
		            		opacity: 1,
		            		transform: 'translate3d(0,0,-10px) scale(1)'
		            	});

						$(window).trigger('section.scroll.change.header.color');

		            	setTimeout(() => {
							flag = false;
						}, 500);
					}, 1000);

					flag = true;

				}

                return;
            });

	        // Dots Navigation
	        if( this.options.dotsNav ) {
	        	self.dotsNavigation();
	        }

	        // First Load
	        setTimeout(() => {
		        if( $(window.location.hash).get(0) ) {
		        	self.moveTo( $(window.location.hash).parent().offset().top );

		        	self.changeSectionActiveState( $(window.location.hash) );

		        	// Change Dots Active Class
			        self.changeDotsActiveState();

		        	self.updateHash( true );
		        } else {
                    const hash  = window.location.hash;
                    let index = hash.replace('#','');

                    if( !hash ) {
		        		index = 1;
		        	}

                    self.moveTo( $('.section-wrapper').eq( index - 1 ).offset().top );

                    self.changeSectionActiveState( $('.section-wrapper').eq( index - 1 ).find('.section-scroll') );

                    // Change Dots Active Class
                    self.changeDotsActiveState();

                    self.updateHash( true );
                }

				$(window).trigger('section.scroll.ready');
				$(window).trigger('section.scroll.change.header.color');
			}, 500);

			return this;
		}

        updateSectionsHeight() {
			const self = this;

			$('.section-scroll').css({ height: '' });

			$('.section-scroll').each(function(){
				if( $(this).outerHeight() < ( $(window).height() + 3 ) ) {
					$(this).css({ height: '100vh' });		
				} else {
					$(this).addClass('section-scroll-scrollable');
				}
			});

			// Set the section wrapper height
	  		$('.section-wrapper').each(function(){
	  			$(this).height( $(this).find('.section-scroll').outerHeight() );
	  		});

			return this;
		}

        updateHash(first_load) {
			const self = this;

			if( !window.location.hash ) {
				window.location.hash = 1;
			} else {
				if(!first_load) {
					const $section = $('.section-wrapper').eq( self.getCurrentIndex() ).find('.section-scroll'), section_id = $section.attr('id') ? $section.attr('id') : $section.parent().index() + 1;

					window.location.hash = section_id;
				}
			}

			return this;
		}

        getCurrentIndex() {
            const self = this;
            let currentIndex = 0;

            currentIndex = $('.section-wrapper.active').index();

            return currentIndex;
        }

        moveTo($scrollTopValue, first_load) {
			const self = this;

			$('body, html').animate({
   				scrollTop: $scrollTopValue
   			}, 1000, 'easeOutQuint');

   			setTimeout(() => {
	   			self.updateHash();
   			}, 500);

			return this;
		}

        getNextSection(wheelDirection, touchDirection) {
            const self = this;
            let $nextSection = '';

            // Scroll Direction
            if(wheelDirection || touchDirection == 'up') {
				$nextSection = $('.section-wrapper').eq( self.getCurrentIndex() + 1 ).find('.section-scroll');
        	} else {
        		$nextSection = $('.section-wrapper').eq( self.getCurrentIndex() - 1 ).find('.section-scroll');
        	}

            return $nextSection;
        }

        changeSectionActiveState($nextSection) {
			const self = this;

			$('.section-wrapper').removeClass('active');
	   		$nextSection.parent().addClass('active');

			return this;
		}

        changeDotsActiveState() {
			const self = this;

			$('.section-scroll-dots-navigation > ul > li').removeClass('active');
			$('.section-scroll-dots-navigation > ul > li').eq( self.getCurrentIndex() ).addClass('active');

			return this;
		}

        dotsNavigation() {
			const self = this;

			const dotsNav = $('<div class="section-scroll-dots-navigation"><ul class="list list-unstyled"></ul></div>'), currentSectionIndex = self.getCurrentIndex();

        	if( self.options.dotsClass ) {
        		dotsNav.addClass( self.options.dotsClass );
        	}

        	for( let i = 0; i < $('.section-scroll').length; i++ ) {
        		const title = $('.section-wrapper').eq( i ).find('.section-scroll').data('section-scroll-title');

        		dotsNav.find('> ul').append( '<li'+ ( ( currentSectionIndex == i ) ? ' class="active"' : '' ) +'><a href="#'+ i +'" data-nav-id="'+ i +'"><span>'+ title +'</span></a></li>' );
        	}

        	$('.body').append( dotsNav );

        	dotsNav.find('a[data-nav-id]').on('click touchstart', function(e){
        		e.preventDefault();
        		const $this = $(this);

        		$('.section-scroll').css({
        			opacity: 0,
        			transition: 'ease opacity 300ms'
        		});

        		self.$header.css({
			    	opacity: 0,
			    	transition: 'ease opacity 500ms'
			    });

        		setTimeout(() => {
	        		self.moveTo( $('.section-wrapper').eq( $this.data('nav-id') ).offset().top )

		   			$('.section-wrapper').removeClass('active');
			   		$('.section-wrapper').eq( $this.data('nav-id') ).addClass('active');

	        		$('.section-wrapper').eq( self.getCurrentIndex() ).find('.section-scroll').css({
	        			opacity: 1
	        		});

	        		setTimeout(() => {
		        		$('.section-scroll').css({ opacity: 1 });

		        		$(window).trigger('section.scroll.change.header.color');
	        		}, 500);

	        		if( $(window).width() > 991 ) {
		        		self.changeDotsActiveState();
	        		}
        		}, 500);
        	});

			return this;
		}

        events() {
			const self = this;

			$(window).on('section.scroll.ready', () => {
				$(window).scrollTop(0);
			});

			$(window).on('section.scroll.change.header.color', () => {
		    	const headerColor = $('.section-wrapper').eq( self.getCurrentIndex() ).find('.section-scroll').data('section-scroll-header-color');
		    	
		    	$('#header .header-nav').removeClass('header-nav-light-text header-nav-dark-text').addClass('header-nav-' + headerColor + '-text');
		    	$('#header .header-nav-features').removeClass('header-nav-features-dark header-nav-features-light').addClass('header-nav-features-' + headerColor);
		    	$('#header .header-social-icons').removeClass('social-icons-icon-dark social-icons-icon-light').addClass('social-icons-icon-' + headerColor);

		    	// Change Logo
		    	if( self.options.changeHeaderLogo && headerColor != undefined ) {
			    	if( headerColor == 'light' ) {
			    		$('#header .header-logo img').attr('src', self.options.headerLogoLight);
			    	} else if( headerColor == 'dark' ) {
			    		$('#header .header-logo img').attr('src', self.options.headerLogoDark);
			    	}
		    	}

		    	self.$header.css({
		    		opacity: 1
		    	});
		    });

			$(document).ready(() => {
			    $(window).afterResize(() => {
			    	self.updateSectionsHeight();

			    	if( $(window).width() < 992 ) {
			    		$('html').removeClass('overflow-hidden');
			    	}
			    });
			});

		    return this;
		}
    }

    PluginSectionScroll.defaults = {
		targetClass: '.section',
		dotsNav: true,
		changeHeaderLogo: true,
		headerLogoDark: 'img/logo-default-slim.png',
		headerLogoLight: 'img/logo-default-slim-dark.png'
	};

    // expose to scope
    $.extend(theme, {
		PluginSectionScroll
	});

    // jquery plugin
    $.fn.themePluginSectionScroll = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginSectionScroll($this, opts);
			}

		});
	};
})).apply(this, [window.theme, jQuery]);

// Sort
(((theme = {}, $) => {
    const instanceName = '__sort';

    class PluginSort {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginSort.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.isotope))) {
				return this;
			}

			const self = this, $source = this.options.wrapper, $destination = $('.sort-destination[data-sort-id="' + $source.attr('data-sort-id') + '"]'), $window = $(window);

			if ($destination.get(0)) {

				self.$source = $source;
				self.$destination = $destination;
				self.$loader = false;

				self.setParagraphHeight($destination);

				if (self.$destination.parents('.sort-destination-loader').get(0)) {
					self.$loader = self.$destination.parents('.sort-destination-loader');
					self.createLoader();
				}

				$destination.attr('data-filter', '*');

				$destination.one('layoutComplete', (event, laidOutItems) => {
					self.removeLoader();

					// If has data-plugin-sticky on the page we need recalculate sticky position
					if( $('[data-plugin-sticky]').length ) {
						setTimeout(() => {
							$('[data-plugin-sticky]').each(function(){
								$(this).data('__sticky').build();
								$(window).trigger('resize');
							});
						}, 500);
					}
				});

				if ( $('#' + self.options.filterFieldId).length ) {

					const $filterField = $('#' + self.options.filterFieldId);

					$filterField.keyup(function() {
						self.options.filterFieldText = $(this).val();
						self.setFilter(self.options.filter);
					});

				}

				$destination.waitForImages(() => {
					$destination.isotope(self.options);
					self.events();
				});

				setTimeout(() => {
					self.removeLoader();
				}, 3000);

			}

			return this;
		}

        events() {
            const self = this;
            let filter = null;
            const $window = $(window);

            self.$source.find('a').click(function(e) {
				e.preventDefault();

				filter = $(this).parent().data('option-value');

				self.setFilter(filter);

				if (e.originalEvent) {
					self.$source.trigger('filtered');
				}

				return this;
			});

            self.$destination.trigger('filtered');
            self.$source.trigger('filtered');

            if (self.options.useHash) {
				self.hashEvents();
			}

            $window.on('resize sort.resize', () => {
				setTimeout(() => {
					self.$destination.isotope('layout');
				}, 300);
			});

            setTimeout(() => {
				$window.trigger('sort.resize');
			}, 300);

            return this;
        }

        setFilter(filter) {
            const self = this;
            const page = false;
            let currentFilter = filter;

            self.$source.find('.active').removeClass('active');
            self.$source.find('li[data-option-value="' + filter + '"], li[data-option-value="' + filter + '"] > a').addClass('active');

            self.options.filter = currentFilter;

            if (self.$destination.attr('data-current-page')) {
				currentFilter = currentFilter + '[data-page-rel=' + self.$destination.attr('data-current-page') + ']';
			}

            if (self.options.filterFieldText != '') {
				currentFilter = currentFilter + '[data-sort-search*=' + self.options.filterFieldText.toLowerCase() + ']';
			}

            self.$destination.attr('data-filter', filter).isotope({
				filter: currentFilter
			}).one('arrangeComplete', (event, filteredItems) => {
				
				if (self.options.useHash) {
					if (window.location.hash != '' || self.options.filter.replace('.', '') != '*') {
						window.location.hash = self.options.filter.replace('.', '');
					}
				}
				
				$(window).trigger('scroll');

			}).trigger('filtered');

            return this;
        }

        hashEvents() {
            const self = this;
            let hash = null;
            let hashFilter = null;
            let initHashFilter = '.' + location.hash.replace('#', '');

            // Check if has scroll to section trough URL hash and prevent the sort plugin from show nothing
            if( $(location.hash).length ) {
				initHashFilter = '.';
			}

            if (initHashFilter != '.' && initHashFilter != '.*') {
				self.setFilter(initHashFilter);
			}

            $(window).on('hashchange', e => {

				hashFilter = '.' + location.hash.replace('#', '');
				hash = (hashFilter == '.' || hashFilter == '.*' ? '*' : hashFilter);

				self.setFilter(hash);

			});

            return this;
        }

        setParagraphHeight() {
            const self = this;
            let minParagraphHeight = 0;
            const paragraphs = $('span.thumb-info-caption p', self.$destination);

            paragraphs.each(function() {
				if ($(this).height() > minParagraphHeight) {
					minParagraphHeight = ($(this).height() + 10);
				}
			});

            paragraphs.height(minParagraphHeight);

            return this;
        }

        createLoader() {
			const self = this;

			const loaderTemplate = [
				'<div class="bounce-loader">',
					'<div class="bounce1"></div>',
					'<div class="bounce2"></div>',
					'<div class="bounce3"></div>',
				'</div>'
			].join('');

			self.$loader.append(loaderTemplate);

			return this;
		}

        removeLoader() {

			const self = this;

			if (self.$loader) {

				self.$loader.removeClass('sort-destination-loader-showing');

				setTimeout(() => {
					self.$loader.addClass('sort-destination-loader-loaded');
				}, 300);

			}

		}
    }

    PluginSort.defaults = {
		useHash: true,
		itemSelector: '.isotope-item',
		layoutMode: 'masonry',
		filter: '*',
		filterFieldId: false,
		filterFieldText: '',
		hiddenStyle: {
			opacity: 0
		},
		visibleStyle: {
			opacity: 1
		},
		stagger: 30,
		isOriginLeft: ($('html').attr('dir') == 'rtl' ? false : true)
	};

    // expose to scope
    $.extend(theme, {
		PluginSort
	});

    // jquery plugin
    $.fn.themePluginSort = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginSort($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Star Rating
(((theme = {}, $) => {
    const instanceName = '__starrating';

    class PluginStarRating {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginStarRating.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {

			if (!($.isFunction($.fn.rating))) {
				return this;
			}

			const self = this;

			self.options.wrapper
				.rating(self.options);

			self.options.wrapper.parents('.rating-container')
				.addClass('rating-' + self.options.color);

			if( self.options.extraClass ) {
				self.options.wrapper.parents('.rating-container')
					.addClass(self.options.extraClass);
			}

			return this;

		}
    }

    PluginStarRating.defaults = {
		theme: 'krajee-fas',
		color: 'primary',
		showClear: false,
		showCaption: false
	};

    // expose to scope
    $.extend(theme, {
		PluginStarRating
	});

    // jquery plugin
    $.fn.themePluginStarRating = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginStarRating($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Sticky
(((theme = {}, $) => {
    const instanceName = '__sticky';

    class PluginSticky {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ( $el.data( instanceName ) ) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginSticky.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
			if (!($.isFunction($.fn.pin))) {
				return this;
			}

			const self = this, $window = $(window);
			
			self.options.wrapper.pin(self.options);

			if( self.options.wrapper.hasClass('sticky-wrapper-transparent') ) {
				self.options.wrapper.parent().addClass('position-absolute w-100');
			}

			$window.afterResize(() => {
				self.options.wrapper.removeAttr('style').removeData('pin');
				self.options.wrapper.pin(self.options);
				$window.trigger('scroll');
			});

			// Change Logo Src
			if( self.options.wrapper.find('img').attr('data-change-src') ) {
				const $logo = self.options.wrapper.find('img'), logoSrc = $logo.attr('src'), logoNewSrc = $logo.attr('data-change-src');

				self.changeLogoSrc = activate => {
					if(activate) {
						$logo.attr('src', logoNewSrc);
					} else {
						$logo.attr('src', logoSrc);
					}
				}
			}
			
			return this;
		}

        events() {
            const self = this;
            const $window = $(window);
            const $logo = self.options.wrapper.find('img');
			const classToCheck = ( self.options.wrapper.hasClass('sticky-wrapper-effect-1') ) ? 'sticky-effect-active' : 'sticky-active';
            let stickyActivateFlag = true;
            let stickyDeactivateFlag = false;

            $window.on('scroll sticky.effect.active', () => {
				if( self.options.wrapper.hasClass( classToCheck ) ) {		
					if( stickyActivateFlag ) {			
						if( $logo.attr('data-change-src') ) {
							self.changeLogoSrc(true);
						}

						stickyActivateFlag = false;
						stickyDeactivateFlag = true;
					}
				} else {	
					if( stickyDeactivateFlag ) {				
						if( $logo.attr('data-change-src') ) {
							self.changeLogoSrc(false);
						}

						stickyDeactivateFlag = false;
						stickyActivateFlag = true;
					}
				}
			});

            let isGoingUp = false;
            if( self.options.stickyStartEffectAt ) {

				// First Load
				if( self.options.stickyStartEffectAt < $window.scrollTop() ) {
					self.options.wrapper.addClass('sticky-effect-active');

					$window.trigger('sticky.effect.active');
				}

				$window.on('scroll', () => {
					if( self.options.stickyStartEffectAt < $window.scrollTop() ) {	
						self.options.wrapper.addClass('sticky-effect-active');
						isGoingUp = true;

						$window.trigger('sticky.effect.active');
					} else {	
						if( isGoingUp ) {
							self.options.wrapper.find('.sticky-body').addClass('position-fixed');
							isGoingUp = false;
						}

						if( $window.scrollTop() == 0 ) {
							self.options.wrapper.find('.sticky-body').removeClass('position-fixed');
						}

						self.options.wrapper.removeClass('sticky-effect-active');
					}
				});
			}

            // Refresh Sticky Plugin if click in a data-toggle="collapse"
            if( $('[data-bs-toggle="collapse"]').get(0) ) {

				$('[data-bs-toggle="collapse"]').on('click', () => {
					setTimeout(() => {
						self.build();
						$(window).trigger('scroll');
					}, 1000);
				});

			}

			// Visibility Issue
			document.addEventListener('visibilitychange', () => {
				$(window).trigger('resize');
			});

			setInterval(() => {
				$(window).trigger('resize');
			}, 1000);

        }
    }

    PluginSticky.defaults = {
		minWidth: 991,
		activeClass: 'sticky-active'
	};

    // expose to scope
    $.extend(theme, {
		PluginSticky
	});

    // jquery plugin
    $.fn.themePluginSticky = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginSticky($this, opts);
			}
			
		});
	}
})).apply(this, [ window.theme, jQuery ]);

// Toggle
(((theme = {}, $) => {
    const instanceName = '__toggle';

    class PluginToggle {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			if ($el.data(instanceName)) {
				return this;
			}

			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginToggle.defaults, opts, {
				wrapper: this.$el
			});

			return this;
		}

        build() {
            const self = this;
            const $wrapper = this.options.wrapper;
            const $items = $wrapper.find('> .toggle');
            let $el = null;

            $items.each(function() {
				$el = $(this);

				if ($el.hasClass('active')) {
					$el.find('> p').addClass('preview-active');
					$el.find('> .toggle-content').slideDown(self.options.duration);
				}

				self.events($el);
			});

            if (self.options.isAccordion) {
				self.options.duration = self.options.duration / 2;
			}

            return this;
        }

        events($el) {
            const self = this;
            let previewParCurrentHeight = 0;
            let previewParAnimateHeight = 0;
            let toggleContent = null;

            $el.find('> label, > .toggle-title').click(function({originalEvent}) {
                const $this = $(this);
                const parentSection = $this.parent();
                const parentWrapper = $this.parents('.toggle');
                let previewPar = null;
                let closeElement = null;

                if (self.options.isAccordion && typeof(originalEvent) != 'undefined') {
					closeElement = parentWrapper.find('.toggle.active > label, .toggle.active > .toggle-title');

					if (closeElement[0] == $this[0]) {
						return;
					}
				}

                parentSection.toggleClass('active');

                // Preview Paragraph
                if (parentSection.find('> p').get(0)) {

					previewPar = parentSection.find('> p');
					previewParCurrentHeight = previewPar.css('height');
					previewPar.css('height', 'auto');
					previewParAnimateHeight = previewPar.css('height');
					previewPar.css('height', previewParCurrentHeight);

				}

                // Content
                toggleContent = parentSection.find('> .toggle-content');

                if (parentSection.hasClass('active')) {

					$(previewPar).animate({
						height: previewParAnimateHeight
					}, self.options.duration, function() {
						$(this).addClass('preview-active');
					});

					toggleContent.slideDown(self.options.duration, () => {
						if (closeElement) {
							closeElement.trigger('click');
						}
					});

				} else {

					$(previewPar).animate({
						height: 0
					}, self.options.duration, function() {
						$(this).removeClass('preview-active');
					});

					toggleContent.slideUp(self.options.duration);

				}
            });
        }
    }

    PluginToggle.defaults = {
		duration: 350,
		isAccordion: false
	};

    // expose to scope
    $.extend(theme, {
		PluginToggle
	});

    // jquery plugin
    $.fn.themePluginToggle = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginToggle($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Validation
(((theme = {}, $) => {
    $.extend(theme, {

		PluginValidation: {

			defaults: {
				formClass: 'needs-validation',
				validator: {
					highlight(element) {
						$(element)
							.addClass('is-invalid')
							.removeClass('is-valid')
							.parent()
							.removeClass('has-success')
							.addClass('has-danger');
					},
					success(label, element) {
						$(element)
							.removeClass('is-invalid')
							.addClass('is-valid')
							.parent()
							.removeClass('has-danger')
							.addClass('has-success')
							.find('label.error')
							.remove();
					},
					errorPlacement(error, element) {
						if (element.attr('type') == 'radio' || element.attr('type') == 'checkbox') {
							error.appendTo(element.parent().parent());
						} else {
							error.insertAfter(element);
						}
					}
				}
			},

			initialize(opts) {
				initialized = true;

				this
					.setOptions(opts)
					.build();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts);

				return this;
			},

			build() {
				const self = this;

				if (!($.isFunction($.validator))) {
					return this;
				}

				self.setMessageGroups();

				$.validator.setDefaults(self.options.validator);

				$('.' + self.options.formClass).validate();

				return this;
			},

			setMessageGroups() {

				$('.checkbox-group[data-msg-required], .radio-group[data-msg-required]').each(function() {
					const message = $(this).data('msg-required');
					$(this).find('input').attr('data-msg-required', message);
				});

			}

		}

	});
})).apply(this, [window.theme, jQuery]);

// Video Background
(((theme = {}, $) => {
    const instanceName = '__videobackground';

    class PluginVideoBackground {
        constructor($el, opts) {
            return this.initialize($el, opts);
        }

        initialize($el, opts) {
			this.$el = $el;

			this
				.setData()
				.setOptions(opts)
				.build()
				.events();

			return this;
		}

        setData() {
			this.$el.data(instanceName, this);

			return this;
		}

        setOptions(opts) {
			this.options = $.extend(true, {}, PluginVideoBackground.defaults, opts, {
				path: this.$el.data('video-path'),
				wrapper: this.$el
			});

			return this;
		}

        build() {
			const self = this;

			if (!($.isFunction($.fn.vide)) || (!this.options.path)) {
				return this;
			}

			if (this.options.overlay) {

				const overlayClass = this.options.overlayClass;

				this.options.wrapper.prepend(
					$('<div />').addClass(overlayClass)
				);
			}

			this.options.wrapper
				.vide(this.options.path, this.options)
				.first()
				.css('z-index', 0);

			// Change Poster
			self.changePoster();

			// Initialize Vide inside a carousel
			if( self.options.wrapper.closest('.owl-carousel').get(0) ) {
				self.options.wrapper.closest('.owl-carousel').on('initialized.owl.carousel', () => {
					$('.owl-item.cloned')
						.find('[data-plugin-video-background] .vide-video-wrapper')
						.remove();

					$('.owl-item.cloned')
						.find('[data-plugin-video-background]')
						.vide(self.options.path, self.options)
						.first()
						.css('z-index', 0);

					self.changePoster( self.options.wrapper.closest('.owl-carousel') );
				});
			}

			// Play Video Button
			const $playButton = self.options.wrapper.find('.video-background-play');

			if( $playButton.get(0) ) {
				const $playWrapper = self.options.wrapper.find('.video-background-play-wrapper');

				self.options.wrapper.find('.video-background-play').on('click', e => {
					e.preventDefault();

					if( $playWrapper.get(0) ) {
						$playWrapper.animate({
							opacity: 0
						}, 300, () => {
							$playWrapper.parent().height( $playWrapper.outerHeight() );
							$playWrapper.remove();
						});
					} else {
						$playButton.animate({
							opacity: 0
						}, 300, () => {
							$playButton.remove();
						});
					}

					setTimeout(() => {
						self.options.wrapper.find('video')[0].play();
					}, 500)
				});
			}

			$(window).trigger('vide.video.inserted.on.dom');

			return this;
		}

        changePoster($carousel) {
			const self = this;

			// If it's inside carousel
			if( $carousel && self.options.changePoster ) {
				$carousel.find('.owl-item [data-plugin-video-background] .vide-video-wrapper').css({
					'background-image': 'url(' + self.options.changePoster + ')'
				});

				return this;
			}

			if( self.options.changePoster ) {
				self.options.wrapper.find('.vide-video-wrapper').css({
					'background-image': 'url(' + self.options.changePoster + ')'
				});
			}

			return this;
		}

        events() {
			const self = this;

			// Initialize
			self.options.wrapper.on('video.background.initialize', () => {
				self.build();
			});

			return this;
		}
    }

    PluginVideoBackground.defaults = {
		overlay: false,
		volume: 1,
		playbackRate: 1,
		muted: true,
		loop: true,
		autoplay: true,
		position: '50% 50%',
		posterType: 'detect',
		className: 'vide-video-wrapper'
	};

    // expose to scope
    $.extend(theme, {
		PluginVideoBackground
	});

    // jquery plugin
    $.fn.themePluginVideoBackground = function(opts) {
		return this.map(function() {
			const $this = $(this);

			if ($this.data(instanceName)) {
				return $this.data(instanceName);
			} else {
				return new PluginVideoBackground($this, opts);
			}

		});
	}
})).apply(this, [window.theme, jQuery]);

// Account
(((theme = {}, $) => {
    let initialized = false;

    $.extend(theme, {

		Account: {

			defaults: {
				wrapper: $('#headerAccount')
			},

			initialize($wrapper, opts) {
				if (initialized) {
					return this;
				}

				initialized = true;
				this.$wrapper = ($wrapper || this.defaults.wrapper);

				this
					.setOptions(opts)
					.events();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts, theme.fn.getOptions(this.$wrapper.data('plugin-options')));

				return this;
			},

			events() {
				const self = this;

				$(window).on('load', () => {
					$(document).ready(() => {
						setTimeout(() => {

							self.$wrapper.find('input').on('focus', () => {
								self.$wrapper.addClass('open');

								$(document).mouseup(({target}) => {
									if (!self.$wrapper.is(target) && self.$wrapper.has(target).length === 0) {
										self.$wrapper.removeClass('open');
									}
								});
							});

						}, 1500);
					});
				});

				$('#headerSignUp').on('click', e => {
					e.preventDefault();
					self.$wrapper.addClass('signup').removeClass('signin').removeClass('recover');
					self.$wrapper.find('.signup-form input:first').focus();
				});

				$('#headerSignIn').on('click', e => {
					e.preventDefault();
					self.$wrapper.addClass('signin').removeClass('signup').removeClass('recover');
					self.$wrapper.find('.signin-form input:first').focus();
				});

				$('#headerRecover').on('click', e => {
					e.preventDefault();
					self.$wrapper.addClass('recover').removeClass('signup').removeClass('signin');
					self.$wrapper.find('.recover-form input:first').focus();
				});

				$('#headerRecoverCancel').on('click', e => {
					e.preventDefault();
					self.$wrapper.addClass('signin').removeClass('signup').removeClass('recover');
					self.$wrapper.find('.signin-form input:first').focus();
				});
			}

		}

	});
})).apply(this, [window.theme, jQuery]);

// Nav
(((theme = {}, $) => {
    let initialized = false;

    $.extend(theme, {

		Nav: {

			defaults: {
				wrapper: $('#mainNav'),
				scrollDelay: 600,
				scrollAnimation: 'easeOutQuad'
			},

			initialize($wrapper, opts) {
				if (initialized) {
					return this;
				}

				initialized = true;
				this.$wrapper = ($wrapper || this.defaults.wrapper);

				this
					.setOptions(opts)
					.build()
					.events();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts, theme.fn.getOptions(this.$wrapper.data('plugin-options')));

				return this;
			},

			build() {
                const self = this;
                const $html = $('html');
                const $header = $('#header');
                const $headerNavMain = $('#header .header-nav-main');
                let thumbInfoPreview;

                // Preview Thumbs
                if( self.$wrapper.find('a[data-thumb-preview]').length ) {
					self.$wrapper.find('a[data-thumb-preview]').each(function() {
						thumbInfoPreview = $('<span />').addClass('thumb-info thumb-info-preview')
												.append($('<span />').addClass('thumb-info-wrapper')
													.append($('<span />').addClass('thumb-info-image').css('background-image', 'url(' + $(this).data('thumb-preview') + ')')
											   )
										   );

						$(this).append(thumbInfoPreview);
					});
				}

                // Side Header / Side Header Hamburguer Sidebar (Reverse Dropdown)
                if($html.hasClass('side-header') || $html.hasClass('side-header-hamburguer-sidebar')) {
					
					// Side Header Right / Side Header Hamburguer Sidebar Right
					if($html.hasClass('side-header-right') || $html.hasClass('side-header-hamburguer-sidebar-right')) {
						if(!$html.hasClass('side-header-right-no-reverse')) {
							$header.find('.dropdown-submenu').addClass('dropdown-reverse');
						}
					}

				} else {
					
					// Reverse
					let checkReverseFlag = false;
					self.checkReverse = () => {
						if( !checkReverseFlag ) {
							self.$wrapper.find('.dropdown, .dropdown-submenu').removeClass('dropdown-reverse');

							self.$wrapper.find('.dropdown:not(.manual):not(.dropdown-mega), .dropdown-submenu:not(.manual)').each(function() {
								if(!$(this).find('.dropdown-menu').visible( false, true, 'horizontal' )  ) {
									$(this).addClass('dropdown-reverse');
								}
							});

							checkReverseFlag = true;
						}
					}

					$(window).on('resize', () => {
						checkReverseFlag = false;
					});

					$header.on('mouseover', () => {
						self.checkReverse();
					});

				}

                // Clone Items
                if($headerNavMain.hasClass('header-nav-main-clone-items')) {

			    	$headerNavMain.find('nav > ul > li > a').each(function(){
				    	const parent = $(this).parent(), clone  = $(this).clone(), clone2 = $(this).clone(), wrapper = $('<span class="wrapper-items-cloned"></span>');

				    	// Config Classes
				    	$(this).addClass('item-original');
				    	clone2.addClass('item-two');

				    	// Insert on DOM
				    	parent.prepend(wrapper);
				    	wrapper.append(clone).append(clone2);
				    });

				}

                // Floating
                if($('#header.header-floating-icons').length && $(window).width() > 991) {

					const menuFloatingAnim = {
						$menuFloating: $('#header.header-floating-icons .header-container > .header-row'),

						build() {
							const self = this;

							self.init();
						},
						init() {
                            const self  = this;
                            let divisor = 0;

                            $(window).scroll(function() {
							    const scrollPercent = 100 * $(window).scrollTop() / ($(document).height() - $(window).height()), st = $(this).scrollTop();

								divisor = $(document).height() / $(window).height();

							    self.$menuFloating.find('.header-column > .header-row').css({
							    	transform : 'translateY( calc('+ scrollPercent +'vh - '+ st / divisor +'px) )' 
							    });
							});
                        }
					};

					menuFloatingAnim.build();

				}

                // Slide
                if($('.header-nav-links-vertical-slide').length) {
					const slideNavigation = {
						$mainNav: $('#mainNav'),
						$mainNavItem: $('#mainNav li'),

						build() {
							const self = this;

							self.menuNav();
						},
						menuNav() {
							const self = this;

							self.$mainNavItem.on('click', function(e){
								const currentMenuItem 	= $(this), currentMenu 		= $(this).parent(), nextMenu        	= $(this).find('ul').first(), prevMenu        	= $(this).closest('.next-menu'), isSubMenu       	= currentMenuItem.hasClass('dropdown') || currentMenuItem.hasClass('dropdown-submenu'), isBack          	= currentMenuItem.hasClass('back-button'), nextMenuHeightDiff  = ( ( nextMenu.find('> li').length * nextMenu.find('> li').outerHeight() ) - nextMenu.outerHeight() ), prevMenuHeightDiff  = ( ( prevMenu.find('> li').length * prevMenu.find('> li').outerHeight() ) - prevMenu.outerHeight() );

								if( isSubMenu ) {
									currentMenu.addClass('next-menu');
									nextMenu.addClass('visible');
									currentMenu.css({
										overflow: 'visible',
										'overflow-y': 'visible'
									});
									
									if( nextMenuHeightDiff > 0 ) {
										nextMenu.css({
											overflow: 'hidden',
											'overflow-y': 'scroll'
										});
									}

									for( i = 0; i < nextMenu.find('> li').length; i++ ) {
										if( nextMenu.outerHeight() < ($('.header-row-side-header').outerHeight() - 100) ) {
											nextMenu.css({
												height: nextMenu.outerHeight() + nextMenu.find('> li').outerHeight()
											});
										}
									}

									nextMenu.css({
										'padding-top': nextMenuHeightDiff + 'px'
									});
								}

								if( isBack ) {
									currentMenu.parent().parent().removeClass('next-menu');
									currentMenu.removeClass('visible');

									if( prevMenuHeightDiff > 0 ) {
										prevMenu.css({
											overflow: 'hidden',
											'overflow-y': 'scroll'
										});
									}
								}

								e.stopPropagation();
							});
						}
					};

					$(window).trigger('resize');
					
					if( $(window).width() > 991 ) {
						slideNavigation.build();
					}

					$(document).ready(() => {
						$(window).afterResize(() => {
							if( $(window).width() > 991 ) {
								slideNavigation.build();
							}
						});
					});
				}

                // Header Nav Main Mobile Dark
                if($('.header-nav-main-mobile-dark').length) {
					$('#header:not(.header-transparent-dark-bottom-border):not(.header-transparent-light-bottom-border)').addClass('header-no-border-bottom');
				}

                // Keyboard Navigation / Accessibility
                if( $(window).width() > 991 ) {
					let focusFlag = false;
					$header.find('.header-nav-main nav > ul > li > a').on('focus', function(){
						
						if( $(window).width() > 991 ) {
							if( !focusFlag ) {
								focusFlag = true;
								$(this).trigger('blur');
								
								self.focusMenuWithChildren();
							}
						}

					});
				}

                return this;
            },

			focusMenuWithChildren() {
                // Get all the link elements within the primary menu.
                let links;

                let i;
                let len;
                const menu = document.querySelector( 'html:not(.side-header):not(.side-header-hamburguer-sidebar):not(.side-header-overlay-full-screen) .header-nav-main > nav' );

                if ( ! menu ) {
					return false;
				}

                links = menu.getElementsByTagName( 'a' );

                // Each time a menu link is focused or blurred, toggle focus.
                for ( i = 0, len = links.length; i < len; i++ ) {
					links[i].addEventListener( 'focus', toggleFocus, true );
					links[i].addEventListener( 'blur', toggleFocus, true );
				}

                //Sets or removes the .focus class on an element.
                function toggleFocus() {
					let self = this;

					// Move up through the ancestors of the current link until we hit .primary-menu.
					while ( !self.className.includes('header-nav-main') ) {
						// On li elements toggle the class .focus.
						if ( 'li' === self.tagName.toLowerCase() ) {
							if ( self.className.includes('accessibility-open') ) {
								self.className = self.className.replace( ' accessibility-open', '' );
							} else {
								self.className += ' accessibility-open';
							}
						}
						self = self.parentElement;
					}
				}
            },

			events() {
                const self    = this;
                const $html   = $('html');
                let $header = $('#header');
                const $window = $(window);
                let headerBodyHeight = $('.header-body').outerHeight();

                if( $header.hasClass('header') ) {
					$header = $('.header');
				}

                $header.find('a[href="#"]').on('click', e => {
					e.preventDefault();
				});

                // Mobile Arrows
                if( $html.hasClass('side-header-hamburguer-sidebar') ) {
					$header.find('.dropdown-toggle, .dropdown-submenu > a')
						.append('<i class="fas fa-chevron-down fa-chevron-right"></i>');
				} else {
					$header.find('.dropdown-toggle, .dropdown-submenu > a')
						.append('<i class="fas fa-chevron-down"></i>');
				}

                $header.find('.dropdown-toggle[href="#"], .dropdown-submenu a[href="#"], .dropdown-toggle[href!="#"] .fa-chevron-down, .dropdown-submenu a[href!="#"] .fa-chevron-down').on('click', function(e) {
					e.preventDefault();
					if ($window.width() < 992) {
						$(this).closest('li').toggleClass('open');

						// Adjust Header Body Height
						const height = ( $header.hasClass('header-effect-shrink') && $html.hasClass('sticky-header-active') ) ? theme.StickyHeader.options.stickyHeaderContainerHeight : headerBodyHeight;
						$('.header-body').animate({
					 		height: ($('.header-nav-main nav').outerHeight(true) + height) + 10
					 	}, 0);
					}
				});

                $header.find('li a.active').addClass('current-page-active');

                // Add Open Class
                $header.find('.header-nav-click-to-open .dropdown-toggle[href="#"], .header-nav-click-to-open .dropdown-submenu a[href="#"], .header-nav-click-to-open .dropdown-toggle > i').on('click', function(e) {
					if( !$('html').hasClass('side-header-hamburguer-sidebar') && $window.width() > 991 ) {
						e.preventDefault();
						e.stopPropagation();
					}

					if ($window.width() > 991) {
						e.preventDefault();
						e.stopPropagation();

						$header.find('li a.active').removeClass('active');

						if( $(this).prop('tagName') == 'I' ) {
							$(this).parent().addClass('active');
						} else {
							$(this).addClass('active');
						}

						if (!$(this).closest('li').hasClass('open')) {
                            const $li = $(this).closest('li');
                            let isSub = false;

                            if( $(this).prop('tagName') == 'I' ) {
								$('#header .dropdown.open').removeClass('open');
								$('#header .dropdown-menu .dropdown-submenu.open').removeClass('open');
							}

                            if ( $(this).parent().hasClass('dropdown-submenu') ) {
								isSub = true;
							}

                            $(this).closest('.dropdown-menu').find('.dropdown-submenu.open').removeClass('open');
                            $(this).parent('.dropdown').parent().find('.dropdown.open').removeClass('open');

                            if (!isSub) {
								$(this).parent().find('.dropdown-submenu.open').removeClass('open');
							}

                            $li.addClass('open');

                            $(document).off('click.nav-click-to-open').on('click.nav-click-to-open', ({target}) => {
								if (!$li.is(target) && $li.has(target).length === 0) {
									$li.removeClass('open');
									$li.parents('.open').removeClass('open');
									$header.find('li a.active').removeClass('active');
									$header.find('li a.current-page-active').addClass('active');
								}
							});
                        } else {
							$(this).closest('li').removeClass('open');
							$header.find('li a.active').removeClass('active');
							$header.find('li a.current-page-active').addClass('active');
						}

						$window.trigger({
							type: 'resize',
							from: 'header-nav-click-to-open'
						});
					}
				});

                // Collapse Nav
                $header.find('[data-collapse-nav]').on('click', function(e) {
					$(this).parents('.collapse').removeClass('show');
				});

                // Top Features
                $header.find('.header-nav-features-toggle').on('click', function(e) {
					e.preventDefault();

					const $toggleParent = $(this).parent();

					if (!$(this).siblings('.header-nav-features-dropdown').hasClass('show')) {

						const $dropdown = $(this).siblings('.header-nav-features-dropdown');

						$('.header-nav-features-dropdown.show').removeClass('show');

						$dropdown.addClass('show');

						$(document).off('click.header-nav-features-toggle').on('click.header-nav-features-toggle', ({target}) => {
							if (!$toggleParent.is(target) && $toggleParent.has(target).length === 0) {
								$('.header-nav-features-dropdown.show').removeClass('show');
							}
						});

						if ($(this).attr('data-focus')) {
							$('#' + $(this).attr('data-focus')).focus();
						}

					} else {
						$(this).siblings('.header-nav-features-dropdown').removeClass('show');
					}
				});

                // Hamburguer Menu
                const $hamburguerMenuBtn = $('.hamburguer-btn:not(.side-panel-toggle)'), $hamburguerSideHeader = $('#header.side-header, #header.side-header-overlay-full-screen');

                $hamburguerMenuBtn.on('click', function(){
					if($(this).attr('data-set-active') != 'false') {
						$(this).toggleClass('active');
					}
					$hamburguerSideHeader.toggleClass('side-header-hide');
					$html.toggleClass('side-header-hide');

					$window.trigger('resize');
				});

                // Toggle Side Header
                $('.toggle-side-header').on('click', () => {
					$('.hamburguer-btn-side-header.active').trigger('click');
				});

                $('.hamburguer-close:not(.side-panel-toggle)').on('click', () => {
					$('.hamburguer-btn:not(.hamburguer-btn-side-header-mobile-show)').trigger('click');
				});

                // Set Header Body Height when open mobile menu
                $('.header-nav-main nav').on('show.bs.collapse', function () {
				 	$(this).removeClass('closed');

				 	// Add Mobile Menu Opened Class
				 	$('html').addClass('mobile-menu-opened');

			 		$('.header-body').animate({
				 		height: ($('.header-body').outerHeight() + $('.header-nav-main nav').outerHeight(true)) + 10
				 	});

				 	// Header Below Slider / Header Bottom Slider - Scroll to menu position
				 	if( $('#header').is('.header-bottom-slider, .header-below-slider') && !$('html').hasClass('sticky-header-active') ) {
				 		self.scrollToTarget( $('#header'), 0 );
				 	}
				});

                // Set Header Body Height when collapse mobile menu
                $('.header-nav-main nav').on('hide.bs.collapse', function () {
				 	$(this).addClass('closed');

				 	// Remove Mobile Menu Opened Class
				 	$('html').removeClass('mobile-menu-opened');

			 		$('.header-body').animate({
				 		height: ($('.header-body').outerHeight() - $('.header-nav-main nav').outerHeight(true))
				 	}, function(){
				 		$(this).height('auto');
				 	});
				});

                // Header Effect Shrink - Adjust header body height on mobile
                $window.on('stickyHeader.activate', () => {
					if( $window.width() < 992 && $header.hasClass('header-effect-shrink') ) {
						if( $('.header-btn-collapse-nav').attr('aria-expanded') == 'true' ) {
							$('.header-body').animate({
						 		height: ( $('.header-nav-main nav').outerHeight(true) + theme.StickyHeader.options.stickyHeaderContainerHeight ) + ( ($('.header-nav-bar').length) ? $('.header-nav-bar').outerHeight() : 0 ) 
						 	});
						}
					}
				});

                $window.on('stickyHeader.deactivate', () => {
					if( $window.width() < 992 && $header.hasClass('header-effect-shrink') ) {
						if( $('.header-btn-collapse-nav').attr('aria-expanded') == 'true' ) {
							$('.header-body').animate({
						 		height: headerBodyHeight + $('.header-nav-main nav').outerHeight(true) + 10
						 	});
						}
					}
				});

                // Remove Open Class on Resize		
                $window.on('resize.removeOpen', ({from}) => {
					if( from == 'header-nav-click-to-open' ) {
						return;
					}
					
					setTimeout(() => {
						if( $window.width() > 991 ) {
							$header.find('.dropdown.open').removeClass('open');
						}
					}, 100);
				});

                // Side Header - Change value of initial header body height
                $(document).ready(() => {
					if( $window.width() > 991 ) {
						let flag = false;
						
						$window.on('resize', ({from}) => {
							if( from == 'header-nav-click-to-open' ) {
								return;
							}

							$header.find('.dropdown.open').removeClass('open');

							if( $window.width() < 992 && flag == false ) {
								headerBodyHeight = $('.header-body').outerHeight();
								flag = true;

								setTimeout(() => {
									flag = false;
								}, 500);
							}
						});
					}
				});

                // Side Header - Set header height on mobile
                if( $html.hasClass('side-header') ) {
					if( $window.width() < 992 ) {
						$header.css({
							height: $('.header-body .header-container').outerHeight() + (parseInt( $('.header-body').css('border-top-width') ) + parseInt( $('.header-body').css('border-bottom-width') ))
						});
					}

					$(document).ready(() => {
						$window.afterResize(() => {
							if( $window.width() < 992 ) {
								$header.css({
									height: $('.header-body .header-container').outerHeight() + (parseInt( $('.header-body').css('border-top-width') ) + parseInt( $('.header-body').css('border-bottom-width') ))
								});
							} else {
								$header.css({
									height: ''
								});
							}
						});
					});
				}

                // Anchors Position
                if( $('[data-hash]').length ) {
					$('[data-hash]').on('mouseover', function(){
						const $this = $(this);

						if( !$this.data('__dataHashBinded') ) {
                            let target = $this.attr('href');
                            let offset = ($this.is("[data-hash-offset]") ? $this.data('hash-offset') : 0);
                            const delay  = ($this.is("[data-hash-delay]") ? $this.data('hash-delay') : 0);
                            const force  = ($this.is("[data-hash-force]") ? true : false);
                            const windowWidth = $(window).width();

                            // Hash Offset SM
                            if ($this.is("[data-hash-offset-sm]") && windowWidth > 576) {
								offset = $this.data('hash-offset-sm');
							}

                            // Hash Offset MD
                            if ($this.is("[data-hash-offset-md]") && windowWidth > 768) {
								offset = $this.data('hash-offset-md');
							}

                            // Hash Offset LG
                            if ($this.is("[data-hash-offset-lg]") && windowWidth > 992) {
								offset = $this.data('hash-offset-lg');
							}

                            // Hash Offset XL
                            if ($this.is("[data-hash-offset-xl]") && windowWidth > 1200) {
								offset = $this.data('hash-offset-xl');
							}

                            // Hash Offset XXL
                            if ($this.is("[data-hash-offset-xxl]") && windowWidth > 1400) {
								offset = $this.data('hash-offset-xxl');
							}

                            if( !$(target).length ) {
								target = target.split('#');
								target = '#'+target[1];
							}

                            if( target.includes('#') && $(target).length) {
								$this.on('click', e => {
									e.preventDefault();

									if( !$(e.target).is('i') || force ) {

										setTimeout(() => {

											// Close Collapse if open
											$this.parents('.collapse.show').collapse('hide');

											// Close Side Header
											$hamburguerSideHeader.addClass('side-header-hide');
											$html.addClass('side-header-hide');
											
											$window.trigger('resize');

											self.scrollToTarget(target, offset);

											// Data Hash Trigger Click
											if( $this.data('hash-trigger-click') ) {

												const $clickTarget = $( $this.data('hash-trigger-click') ), clickDelay = $this.data('hash-trigger-click-delay') ? $this.data('hash-trigger-click-delay') : 0;

												if( $clickTarget.length ) {

													setTimeout(() => {
														// If is a "Tabs" plugin link
														if( $clickTarget.closest('.nav-tabs').length ) {
															new bootstrap.Tab( $clickTarget[0] ).show();
														} else {
															$clickTarget.trigger('click');
														}
														
													}, clickDelay);
												}

											}

										}, delay);
										
									}

									return;
								});
							}

                            $(this).data('__dataHashBinded', true);
                        }
					});
				}

                // Floating
                if($('#header.header-floating-icons').length) {

					$('#header.header-floating-icons [data-hash]').off().each(function() {

						const target = $(this).attr('href'), offset = ($(this).is("[data-hash-offset]") ? $(this).data('hash-offset') : 0);

						if($(target).length) {
							$(this).on('click', e => {
								e.preventDefault();

									$('html, body').animate({
										scrollTop: $(target).offset().top - offset
									}, 600, 'easeOutQuad', () => {

									});

								return;
							});
						}

					});

				}

                // Side Panel Toggle
                if( $('.side-panel-toggle').length ) {
					const init_html_class = $('html').attr('class');

					$('.side-panel-toggle').on('click', function(e){
						const extra_class = $(this).data('extra-class'), delay       = ( extra_class ) ? 100 : 0, isActive    = $(this).data('is-active') ? $(this).data('is-active') : false;

						e.preventDefault();

						if( isActive ) {
							$('html').removeClass('side-panel-open');
							$(this).data('is-active', false);
							return false;
						}

						if( extra_class ) {
							$('.side-panel-wrapper').css('transition','none');
							$('html')
								.removeClass()
								.addClass( init_html_class )
								.addClass( extra_class );
						}
						setTimeout(() => {
							$('.side-panel-wrapper').css('transition','');
							$('html').toggleClass('side-panel-open');
						}, delay);

						$(this).data('is-active', true);						
					});

					$(document).on('click', ({target}) => {
						if( !$(target).closest('.side-panel-wrapper').length && !$(target).hasClass('side-panel-toggle') ) {
							$('.hamburguer-btn.side-panel-toggle:not(.side-panel-close)').removeClass('active');
							$('html').removeClass('side-panel-open');
							$('.side-panel-toggle').data('is-active', false);
						}
					});
				}

				// OffCanvas
				self.offCanvasMenu();

                return this;
            },

			scrollToTarget(target, offset) {
				const self = this, targetPosition = $(target).offset().top;

				$('body').addClass('scrolling');

				$('html, body').animate({
					scrollTop: $(target).offset().top - offset
				}, self.options.scrollDelay, self.options.scrollAnimation, () => {
					$('body').removeClass('scrolling');

					// If by some reason the scroll finishes in a wrong position, this code will run the scrollToTarget() again until get the correct position
					// We need do it just one time to prevent infinite recursive loop at scrollToTarget() function
					if( $(target).offset().top !=  targetPosition) {
						$('html, body').animate({
							scrollTop: $(target).offset().top - offset
						}, 1, self.options.scrollAnimation, () => {});
					}
				});

				return this;

			},

			offCanvasMenu() {
                const $headerNavMain = $('#header .header-nav-main');
				const $offCanvasNav = $('.offcanvas-nav');

				if($offCanvasNav.length > 0) {
					const $navToClone = $headerNavMain.find('nav');

					if($navToClone.length > 0) {
						$offCanvasNav.html($offCanvasNav.html() + $navToClone.html());
						$offCanvasNav.find('#mainNav').removeAttr('id').removeClass().addClass('nav flex-column w-100');

						// Clean Classes
						$offCanvasNav.find('.dropdown').removeClass().addClass('dropdown');

						$offCanvasNav.find('.dropdown-item:not(.dropdown-toggle)').removeClass().addClass('dropdown-item');
						$offCanvasNav.find('.dropdown-item.dropdown-toggle').removeClass().addClass('dropdown-item dropdown-toggle');

						$offCanvasNav.find('.nav-link:not(.dropdown-toggle)').removeClass().addClass('nav-link');
						$offCanvasNav.find('.nav-link.dropdown-toggle').removeClass().addClass('nav-link dropdown-toggle');

						$offCanvasNav.find('.dropdown-menu').removeClass().addClass('dropdown-menu');

						// Dropdowns
						$offCanvasNav.find('.dropdown-toggle').each(function() {
							const $el = $(this);
							const $arrow = $el.find('i');

							$arrow.on('click', function(e) {
								e.preventDefault();
								e.stopPropagation();
								$el.parents('li').toggleClass('open');
							})

							if ($el.attr('href') == '#') {
								$el.on('click', function() {
									$arrow.trigger('click');
								});
							}
						});
					}
				}

				return this;
			}

		}

	});
})).apply(this, [window.theme, jQuery]);


// Newsletter
(((theme = {}, $) => {
    let initialized = false;

    $.extend(theme, {

		Newsletter: {

			defaults: {
				wrapper: $('#newsletterForm')
			},

			initialize($wrapper, opts) {
				if (initialized) {
					return this;
				}

				initialized = true;
				this.$wrapper = ($wrapper || this.defaults.wrapper);

				this
					.setOptions(opts)
					.build();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts, theme.fn.getOptions(this.$wrapper.data('plugin-options')));

				return this;
			},

			build() {
				if (!($.isFunction($.fn.validate))) {
					return this;
				}

				const self = this, $email = self.$wrapper.find('#newsletterEmail'), $success = $('#newsletterSuccess'), $error = $('#newsletterError');

				self.$wrapper.validate({
					submitHandler(form) {

						$.ajax({
							type: 'POST',
							url: self.$wrapper.attr('action'),
							data: {
								'email': $email.val()
							},
							dataType: 'json',
							success({response, message}) {
								if (response == 'success') {

									$success.removeClass('d-none');
									$error.addClass('d-none');

									$email
										.val('')
										.blur()
										.closest('.control-group')
										.removeClass('success')
										.removeClass('error');

								} else {

									$error.html(message);
									$error.removeClass('d-none');
									$success.addClass('d-none');

									$email
										.blur()
										.closest('.control-group')
										.removeClass('success')
										.addClass('error');

								}
							}
						});

					},
					rules: {
						newsletterEmail: {
							required: true,
							email: true
						}
					},
					errorPlacement(error, element) {

					}
				});

				return this;
			}

		}

	});
})).apply(this, [window.theme, jQuery]);

// Search
(((theme = {}, $) => {
    let initialized = false;

    $.extend(theme, {

		Search: {

			defaults: {
				wrapper: $('#searchForm')
			},

			initialize($wrapper, opts) {
				if (initialized) {
					return this;
				}

				initialized = true;
				this.$wrapper = ($wrapper || this.defaults.wrapper);

				this
					.setOptions(opts)
					.build();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts, theme.fn.getOptions(this.$wrapper.data('plugin-options')));

				return this;
			},

			build() {
				if (!($.isFunction($.fn.validate))) {
					return this;
				}

				this.$wrapper.validate({
					errorPlacement(error, element) {}
				});

				// Search Reveal
				theme.fn.execOnceTroughEvent( '#header', 'mouseover.search.reveal', () => {
					$('.header-nav-features-search-reveal').each(function() {
						const $el = $(this), $header = $('#header'), $html = $('html');

						$el.find('.header-nav-features-search-show-icon').on('click', () => {
							$el.addClass('show');
							$header.addClass('search-show');
							$html.addClass('search-show');
							$('#headerSearch').focus();
						});

						$el.find('.header-nav-features-search-hide-icon').on('click', () => {
							$el.removeClass('show');
							$header.removeClass('search-show');
							$html.removeClass('search-show');
						});
					});
				} );

				return this;
			}

		}

	});
})).apply(this, [window.theme, jQuery]);

// Sticky Header
(((theme = {}, $) => {
    let initialized = false;

    $.extend(theme, {

		StickyHeader: {

			defaults: {
				wrapper: $('#header'),
				headerBody: $('#header .header-body'),
				stickyEnabled: true,
				stickyEnableOnBoxed: true,
				stickyEnableOnMobile: false,
				stickyStartAt: 0,
				stickyStartAtElement: false,
				stickySetTop: 0,
				stickyEffect: '',
				stickyHeaderContainerHeight: false,
				stickyChangeLogo: false,
				stickyChangeLogoWrapper: true,
				stickyForce: false,
				stickyScrollUp: false,
				stickyScrollValue: 0
			},

			initialize($wrapper, opts) {
				if (initialized) {
					return this;
				}				

				initialized = true;
				this.$wrapper = ($wrapper || this.defaults.wrapper);

				if( this.$wrapper.hasClass('header') ) {
					this.$wrapper = $('.header[data-plugin-options]');
				}

				this
					.setOptions(opts)
					.build()
					.events();

				return this;
			},

			setOptions(opts) {
				this.options = $.extend(true, {}, this.defaults, opts, theme.fn.getOptions(this.$wrapper.data('plugin-options')));
				return this;
			},

			build() {
                if( $(window).width() < 992 && this.options.stickyEnableOnMobile == false ) {
					$('html').addClass('sticky-header-mobile-disabled');
					return this;
				}

                if (!this.options.stickyEnableOnBoxed && $('html').hasClass('boxed') || $('html').hasClass('side-header-hamburguer-sidebar') && !this.options.stickyForce || !this.options.stickyEnabled) {
					return this;
				}

                const self = this;

                if( self.options.wrapper.hasClass('header') ) {
					self.options.wrapper = $('.header');
					self.options.headerBody = $('.header .header-body');
				}

                const $html = $('html');
                const $window = $(window);
                const sideHeader = $html.hasClass('side-header');
                const initialHeaderTopHeight = self.options.wrapper.find('.header-top').outerHeight();
                const initialHeaderContainerHeight = self.options.wrapper.find('.header-container').outerHeight();
                let minHeight;

                // HTML Classes
                $html.addClass('sticky-header-enabled');

                if (parseInt(self.options.stickySetTop) < 0) {
					$html.addClass('sticky-header-negative');
				}

                if (self.options.stickyScrollUp) {
					$html.addClass('sticky-header-scroll-direction');
				}

                // Notice Top Bar First Load
                if( $('.notice-top-bar').get(0) ) {
					if (parseInt(self.options.stickySetTop) == 1 || self.options.stickyEffect == 'shrink') {
						$('.body').on('transitionend webkitTransitionEnd oTransitionEnd', () => {
						    setTimeout(() => {
								if( !$html.hasClass('sticky-header-active') ) {
								    self.options.headerBody.animate({
								    	top: $('.notice-top-bar').outerHeight()
								    }, 300, () => {
								    	if( $html.hasClass('sticky-header-active') ) {
								    		self.options.headerBody.css('top', 0);
								    	}
								    });
								}
						    }, 0);
						});
					}					
				}

                // Set Start At
                if(self.options.stickyStartAtElement) {

					const $stickyStartAtElement = $(self.options.stickyStartAtElement);

					$(window).on('scroll resize sticky.header.resize', () => {
						self.options.stickyStartAt = $stickyStartAtElement.offset().top;
					});

					$(window).trigger('sticky.header.resize');
				}

                // Define Min Height value
                if( self.options.wrapper.find('.header-top').get(0) ) {
					minHeight = ( initialHeaderTopHeight + initialHeaderContainerHeight );
				} else {
					minHeight = initialHeaderContainerHeight;
				}

                // Set Wrapper Min-Height
                if( !sideHeader ) {
					if( !$('.header-logo-sticky-change').get(0) ) {
						self.options.wrapper.css('height', self.options.headerBody.outerHeight());
					} else {
						$window.on('stickyChangeLogo.loaded', () => {
							self.options.wrapper.css('height', self.options.headerBody.outerHeight());
						});
					}

					if( self.options.stickyEffect == 'shrink' ) {
						
						// Prevent wrong visualization of header when reload on middle of page
						$(document).ready(() => {
							if( $window.scrollTop() >= self.options.stickyStartAt ) {
								self.options.wrapper.find('.header-container').on('transitionend webkitTransitionEnd oTransitionEnd', () => {
									self.options.headerBody.css('position', 'fixed');
								});
							} else {
								if( !$html.hasClass('boxed') ) {
									self.options.headerBody.css('position', 'fixed');
								}
							}
						});

						self.options.wrapper.find('.header-container').css('height', initialHeaderContainerHeight);
						self.options.wrapper.find('.header-top').css('height', initialHeaderTopHeight);
					}
				}

                // Sticky Header Container Height
                if( self.options.stickyHeaderContainerHeight ) {
					self.options.wrapper.find('.header-container').css('height', self.options.wrapper.find('.header-container').outerHeight());
				}

                // Boxed
                if($html.hasClass('boxed') && self.options.stickyEffect == 'shrink') {
					self.boxedLayout();
				}

                // Check Sticky Header / Flags prevent multiple runs at same time
                let activate_flag   	 = true;

                let deactivate_flag 	 = false;
                const initialStickyStartAt = self.options.stickyStartAt;

                self.checkStickyHeader = () => {

					// Notice Top Bar
					const $noticeTopBar = $('.notice-top-bar');
					if ( $noticeTopBar.get(0) ) {
						self.options.stickyStartAt = ( $noticeTopBar.data('sticky-start-at') ) ? $noticeTopBar.data('sticky-start-at') : $('.notice-top-bar').outerHeight();
					} else {
						if( $html.hasClass('boxed') ) {
							self.options.stickyStartAt = initialStickyStartAt + 25;
						} else {
							self.options.stickyStartAt = initialStickyStartAt;
						}
					}

					if( $window.width() > 991 && $html.hasClass('side-header') ) {
						$html.removeClass('sticky-header-active');
						activate_flag = true;
						return;
					}

					if ($window.scrollTop() >= parseInt(self.options.stickyStartAt)) {
						if( activate_flag ) {
							self.activateStickyHeader();
							activate_flag = false;
							deactivate_flag = true;
						}
					} else {
						if( deactivate_flag ) {
							self.deactivateStickyHeader();
							deactivate_flag = false;
							activate_flag = true;
						}
					}

					// Scroll Up
					if (self.options.stickyScrollUp) {
						
					    // Get the new Value
					    self.options.stickyScrollNewValue = window.pageYOffset;

					    //Subtract the two and conclude
					    if(self.options.stickyScrollValue - self.options.stickyScrollNewValue < 0){
					        $html.removeClass('sticky-header-scroll-up').addClass('sticky-header-scroll-down');
					    } else if(self.options.stickyScrollValue - self.options.stickyScrollNewValue > 0){
					        $html.removeClass('sticky-header-scroll-down').addClass('sticky-header-scroll-up');
					    }

					    // Update the old value
					    self.options.stickyScrollValue = self.options.stickyScrollNewValue;

					}
				};

                // Activate Sticky Header
                self.activateStickyHeader = () => {
					if ($window.width() < 992) {
						if (self.options.stickyEnableOnMobile == false) {
							self.deactivateStickyHeader();
							self.options.headerBody.css({
								position: 'relative'
							});
							return false;
						}
					} else {
						if (sideHeader) {
							self.deactivateStickyHeader();
							return;
						}
					}

					$html.addClass('sticky-header-active');

					// Sticky Effect - Reveal
					if( self.options.stickyEffect == 'reveal' ) {

						self.options.headerBody.css('top','-' + self.options.stickyStartAt + 'px');

						self.options.headerBody.animate({
							top: self.options.stickySetTop
						}, 400, () => {});

					}

					// Sticky Effect - Shrink
					if( self.options.stickyEffect == 'shrink' ) {

						// If Header Top
						if( self.options.wrapper.find('.header-top').get(0) ) {
							self.options.wrapper.find('.header-top').css({
								height: 0,
								'min-height': 0,
								overflow: 'hidden'
							});
						}

						// Header Container
						if( self.options.stickyHeaderContainerHeight ) {
							self.options.wrapper.find('.header-container').css({
								height: self.options.stickyHeaderContainerHeight,
								'min-height': 0
							});
						} else {
							self.options.wrapper.find('.header-container').css({
								height: (initialHeaderContainerHeight / 3) * 2, // two third of container height
								'min-height': 0
							});

							const y = initialHeaderContainerHeight - ((initialHeaderContainerHeight / 3) * 2);
							$('.main').css({
								transform: 'translate3d(0, -'+ y +'px, 0)',
								transition: 'ease transform 300ms'
							}).addClass('has-sticky-header-transform');

							if($html.hasClass('boxed')) {
								self.options.headerBody.css('position','fixed');
							}
						}

					}

					self.options.headerBody.css('top', self.options.stickySetTop);

					if (self.options.stickyChangeLogo) {
						self.changeLogo(true);
					}

					// Set Elements Style
					if( $('[data-sticky-header-style]').length ) {
						$('[data-sticky-header-style]').each(function() {
							const $el = $(this), css = theme.fn.getOptions($el.data('sticky-header-style-active')), opts = theme.fn.getOptions($el.data('sticky-header-style'));

							if( $window.width() > opts.minResolution ) {
								$el.css(css);
							}
						});
					}

					$.event.trigger({
						type: 'stickyHeader.activate'
					});
				};

                // Deactivate Sticky Header
                self.deactivateStickyHeader = () => {
					$html.removeClass('sticky-header-active');

					if ( $(window).width() < 992 && self.options.stickyEnableOnMobile == false) {
						return false;
					}

					// Sticky Effect - Shrink
					if( self.options.stickyEffect == 'shrink' ) {

						// Boxed Layout
						if( $html.hasClass('boxed') ) {

							// Set Header Body Position Absolute
							self.options.headerBody.css('position','absolute');

							if( $window.scrollTop() > $('.body').offset().top ) {
								// Set Header Body Position Fixed
								self.options.headerBody.css('position','fixed');								
							}

						} else {
							// Set Header Body Position Fixed
							self.options.headerBody.css('position','fixed');
						}

						// If Header Top
						if( self.options.wrapper.find('.header-top').get(0) ) {
							self.options.wrapper.find('.header-top').css({
								height: initialHeaderTopHeight,
								overflow: 'visible'
							});

							// Fix [data-icon] issue when first load is on middle of the page
							if( self.options.wrapper.find('.header-top [data-icon]').length ) {
								theme.fn.intObsInit( '.header-top [data-icon]:not(.svg-inline--fa)', 'themePluginIcon' );
							}
						}

						// Header Container
						self.options.wrapper.find('.header-container').css({
							height: initialHeaderContainerHeight
						});

					}

					self.options.headerBody.css('top', 0);

					if (self.options.stickyChangeLogo) {
						self.changeLogo(false);
					}

					// Set Elements Style
					if( $('[data-sticky-header-style]').length ) {
						$('[data-sticky-header-style]').each(function() {
							const $el = $(this), css = theme.fn.getOptions($el.data('sticky-header-style-deactive')), opts = theme.fn.getOptions($el.data('sticky-header-style'));

							if( $window.width() > opts.minResolution ) {
								$el.css(css);
							}
						});
					}

					$.event.trigger({
						type: 'stickyHeader.deactivate'
					});
				};

                // Always Sticky
                if (parseInt(self.options.stickyStartAt) <= 0) {
					self.activateStickyHeader();
				}

                // Set Logo
                if (self.options.stickyChangeLogo) {

					const $logoWrapper = self.options.wrapper.find('.header-logo'), $logo = $logoWrapper.find('img'), logoWidth = $logo.attr('width'), logoHeight = $logo.attr('height'), logoSmallTop = parseInt($logo.attr('data-sticky-top') ? $logo.attr('data-sticky-top') : 0), logoSmallWidth = parseInt($logo.attr('data-sticky-width') ? $logo.attr('data-sticky-width') : 'auto'), logoSmallHeight = parseInt($logo.attr('data-sticky-height') ? $logo.attr('data-sticky-height') : 'auto');

					if (self.options.stickyChangeLogoWrapper) {
						$logoWrapper.css({
							'width': $logo.outerWidth(true),
							'height': $logo.outerHeight(true)
						});
					}

					self.changeLogo = activate => {
						if(activate) {
							
							$logo.css({
								'top': logoSmallTop,
								'width': logoSmallWidth,
								'height': logoSmallHeight
							});

						} else {
							
							$logo.css({
								'top': 0,
								'width': logoWidth,
								'height': logoHeight
							});

						}
					}

					$.event.trigger({
						type: 'stickyChangeLogo.loaded'
					});

				}

                // Side Header
                let headerBodyHeight, flag = false;

                self.checkSideHeader = () => {
					if($window.width() < 992 && flag == false) {
						headerBodyHeight = self.options.headerBody.height();
						flag = true;
					}

					if(self.options.stickyStartAt == 0 && sideHeader) {
						self.options.wrapper.css('min-height', 0);
					}

					if(self.options.stickyStartAt > 0 && sideHeader && $window.width() < 992) {
						self.options.wrapper.css('min-height', headerBodyHeight);
					}
				}

                return this;
            },

			events() {
				const self = this;

				if( $(window).width() < 992 && this.options.stickyEnableOnMobile == false ) {
					return this;
				}

				if (!this.options.stickyEnableOnBoxed && $('body').hasClass('boxed') || $('html').hasClass('side-header-hamburguer-sidebar') && !this.options.stickyForce || !this.options.stickyEnabled) {
					return this;
				}

				if (!self.options.alwaysStickyEnabled) {
					$(window).on('scroll resize', () => {
						if ( $(window).width() < 992 && self.options.stickyEnableOnMobile == false) {
							self.options.headerBody.css({
								position: ''
							});

							if( self.options.stickyEffect == 'shrink' ) {
								self.options.wrapper.find('.header-top').css({
									height: ''
								});
							}

							self.deactivateStickyHeader();
						} else {
							self.checkStickyHeader();
						}
					});
				} else {
					self.activateStickyHeader();
				}

				$(window).on('load resize', () => {
					self.checkSideHeader();
				});

				$(window).on('layout.boxed', () => {
					self.boxedLayout();
				});

				return this;
			},

			boxedLayout() {
				const self = this, $window = $(window);

				if($('html').hasClass('boxed') && self.options.stickyEffect == 'shrink') {
					if( (parseInt(self.options.stickyStartAt) == 0) && $window.width() > 991) {
						self.options.stickyStartAt = 30;
					}

					// Set Header Body Position Absolute
					self.options.headerBody.css({
						position: 'absolute',
						top: 0
					});

					// Set position absolute because top margin from boxed layout
					$window.on('scroll', () => {
						if( $window.scrollTop() > $('.body').offset().top ) {
							self.options.headerBody.css({
								'position' : 'fixed',
								'top' : 0
							});								
						} else {
							self.options.headerBody.css({
								'position' : 'absolute',
								'top' : 0
							});
						}
					});
				}

				return this;
			}

		}

	});
})).apply(this, [window.theme, jQuery]);