/*
Plugin Name: 	Animated Headlines
Written by: 	Codyhouse - (https://codyhouse.co/demo/animated-headlines/index.html)
*/
jQuery(document).ready(function($) {
	//set animation timing
	var animationDelay = 2500,
		//loading bar effect
		barAnimationDelay = 3800,
		barWaiting = barAnimationDelay - 3000, //3000 is the duration of the transition on the loading bar - set in the scss/css file
		//letters effect
		lettersDelay = 50,
		//type effect
		typeLettersDelay = 150,
		selectionDuration = 500,
		typeAnimationDelay = selectionDuration + 800,
		//clip effect 
		revealDuration = 600,
		revealAnimationDelay = 1500;

	initHeadline();

	function initHeadline() {
		//initialise headline animation
		animateHeadline('.word-rotator', '.word-rotator.letters');
	}

	function animateHeadline($selector) {
		var duration = animationDelay;

		theme.fn.intObs($selector, function(){
			
			// Single Letters - Insert <i> element for each letter of a changing word
			if( $(this).hasClass('letters') ) {
				$(this).find('b').each(function() {
					var word = $(this),
						letters = word.text().split(''),
						selected = word.hasClass('is-visible');
					for (i in letters) {
						if (word.parents('.rotate-2').length > 0) letters[i] = '<em>' + letters[i] + '</em>';
						letters[i] = (selected) ? '<i class="in">' + letters[i] + '</i>' : '<i>' + letters[i] + '</i>';
					}
					var newLetters = letters.join('');
					word.html(newLetters).css('opacity', 1);
				});				
			}

			// Animate the Headline
			var headline = $(this);

			if (headline.hasClass('loading-bar')) {
				duration = barAnimationDelay;
				setTimeout(function() {
					headline.find('.word-rotator-words').addClass('is-loading')
				}, barWaiting);
			} else if (headline.hasClass('clip')) {
				var spanWrapper = headline.find('.word-rotator-words'),
					newWidth = spanWrapper.outerWidth() + 10
				spanWrapper.css('width', newWidth);
			} else if (!headline.hasClass('type')) {
				//assign to .word-rotator-words the width of its longest word
				var words = headline.find('.word-rotator-words b'),
					width = 0;
				words.each(function() {
					var wordWidth = $(this).outerWidth();
					if (wordWidth > width) width = wordWidth;
				});
				headline.find('.word-rotator-words').css('width', width);
			};

			// Trigger animation
			setTimeout(function() {
				hideWord(headline.find('.is-visible').eq(0))
			}, duration);
		}, {});
	}

	function hideWord($word) {
		var nextWord = takeNext($word);

		if ($word.parents('.word-rotator').hasClass('type')) {
			var parentSpan = $word.parent('.word-rotator-words');
			parentSpan.addClass('selected').removeClass('waiting');
			setTimeout(function() {
				parentSpan.removeClass('selected');
				$word.removeClass('is-visible').addClass('is-hidden').children('i').removeClass('in').addClass('out');
			}, selectionDuration);
			setTimeout(function() {
				showWord(nextWord, typeLettersDelay)
			}, typeAnimationDelay);

		} else if ($word.parents('.word-rotator').hasClass('letters')) {
			var bool = ($word.children('i').length >= nextWord.children('i').length) ? true : false;
			hideLetter($word.find('i').eq(0), $word, bool, lettersDelay);
			showLetter(nextWord.find('i').eq(0), nextWord, bool, lettersDelay);

		} else if ($word.parents('.word-rotator').hasClass('clip')) {
			$word.parents('.word-rotator-words').stop( true, true ).animate({
				width: '2px'
			}, revealDuration, function() {
				switchWord($word, nextWord);
				showWord(nextWord);
			});

		} else if ($word.parents('.word-rotator').hasClass('loading-bar')) {
			$word.parents('.word-rotator-words').removeClass('is-loading');
			switchWord($word, nextWord);
			setTimeout(function() {
				hideWord(nextWord)
			}, barAnimationDelay);
			setTimeout(function() {
				$word.parents('.word-rotator-words').addClass('is-loading')
			}, barWaiting);

		} else {
			switchWord($word, nextWord);
			setTimeout(function() {
				hideWord(nextWord)
			}, animationDelay);
		}
	}

	function showWord($word, $duration) {
		if ($word.parents('.word-rotator').hasClass('type')) {
			showLetter($word.find('i').eq(0), $word, false, $duration);
			$word.addClass('is-visible').removeClass('is-hidden');
		} else if ($word.parents('.word-rotator').hasClass('clip')) {
			if (document.hasFocus()) {
				$word.parents('.word-rotator-words').stop( true, true ).animate({
					'width': $word.outerWidth() + 10
				}, revealDuration, function() {
					setTimeout(function() {
						hideWord($word)
					}, revealAnimationDelay);
				});
			} else {
				$word.parents('.word-rotator-words').stop( true, true ).animate({
					width: $word.outerWidth() + 10
				});
				setTimeout(function() {
					hideWord($word)
				}, revealAnimationDelay);
			}
		}
	}

	function hideLetter($letter, $word, $bool, $duration) {
		$letter.removeClass('in').addClass('out');

		if (!$letter.is(':last-child')) {
			setTimeout(function() {
				hideLetter($letter.next(), $word, $bool, $duration);
			}, $duration);
		} else if ($bool) {
			setTimeout(function() {
				hideWord(takeNext($word))
			}, animationDelay);
		}

		if ($letter.is(':last-child') && $('html').hasClass('no-csstransitions')) {
			var nextWord = takeNext($word);
			switchWord($word, nextWord);
		}
	}

	function showLetter($letter, $word, $bool, $duration) {
		$letter.addClass('in').removeClass('out');

		if (!$letter.is(':last-child')) {
			setTimeout(function() {
				showLetter($letter.next(), $word, $bool, $duration);
			}, $duration);
		} else {
			if ($word.parents('.word-rotator').hasClass('type')) {
				setTimeout(function() {
					$word.parents('.word-rotator-words').addClass('waiting');
				}, 200);
			}
			if (!$bool) {
				setTimeout(function() {
					hideWord($word)
				}, animationDelay)
			}

			if (!$word.closest('.word-rotator').hasClass('type')) {
				$word.closest('.word-rotator-words').stop( true, true ).animate({
					width: $word.outerWidth()
				});
			}
		}
	}

	function takeNext($word) {
		return (!$word.is(':last-child')) ? $word.next() : $word.parent().children().eq(0);
	}

	function takePrev($word) {
		return (!$word.is(':first-child')) ? $word.prev() : $word.parent().children().last();
	}

	function switchWord($oldWord, $newWord) {
		$oldWord.removeClass('is-visible').addClass('is-hidden');
		$newWord.removeClass('is-hidden').addClass('is-visible');

		if (!$newWord.closest('.word-rotator').hasClass('clip')) {
			var space = 0,
				delay = ($newWord.outerWidth() > $oldWord.outerWidth()) ? 0 : 600;

			if ($newWord.closest('.word-rotator').hasClass('loading-bar') || $newWord.closest('.word-rotator').hasClass('slide')) {
				space = 3;
				delay = 0;
			}

			setTimeout(function() {
				$newWord.closest('.word-rotator-words').stop( true, true ).animate({
					width: $newWord.outerWidth() + space
				});
			}, delay);
		}
	}
});