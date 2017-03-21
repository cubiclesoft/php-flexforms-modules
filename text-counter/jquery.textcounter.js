// jQuery plugin to display a character count in text boxes.
// (C) 2017 CubicleSoft.  All Rights Reserved.

(function($) {
	$.fn.TextCounter = function(options) {
		var settings = $.extend({
			'limit' : 0,
			'target' : null,
			'unit' : 'characters',
			'okayClass' : 'textcounter_okay',
			'errorClass' : 'textcounter_error',
			'mainMsg' : '{x} of {y} characters entered.',
			'mainMsgOne' : '{x} of {y} characters entered.',
			'errorMsg' : 'Too many characters entered.'
		}, options);

		return this.each(function() {
			var $this = $(this);
			var dest = (settings.target == null ? $this.append('<div />') : $(settings.target));

			var CounterHandler = function(e) {
				var val = $this.val();
				var vallen = (settings.unit == 'words' ? val.split(/\s+/).length : val.length);
				var valid = vallen <= settings.limit;

				dest.removeClass(settings.okayClass).removeClass(settings.errorClass);
				dest.addClass(valid ? settings.okayClass : settings.errorClass);
				dest.html((valid ? '' : settings.errorMsg + '  ') + (vallen == 1 ? settings.mainMsgOne : settings.mainMsg).replace('{x}', vallen).replace('{y}', settings.limit));
			}

			$this.keydown(CounterHandler).keyup(CounterHandler).change(CounterHandler).change();
		});
	}
}(jQuery));
