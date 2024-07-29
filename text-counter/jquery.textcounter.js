// jQuery plugin to display a character/word count of text in text boxes.
// (C) 2024 CubicleSoft.  All Rights Reserved.

(function($) {
	$.fn.TextCounter = function(options) {
		this.each(function() {
			var $this = $(this);

			// Remove event handlers.
			$this.off('keydown.textcounter');
			$this.off('keyup.textcounter');
			$this.off('change.textcounter');

			// Remove created element (if any).
			if ($this.data('textcountertarget') && typeof($this.data('textcountertarget')) === 'object')
			{
				$this.data('textcountertarget').remove();
				$this.removeData('textcountertarget');
			}
		});

		if (typeof(options) === 'string' && options === 'destroy')  return this;

		var settings = $.extend({ 'target' : null, 'valueCallback' : null }, $.fn.TextCounter.defaults, options);

		return this.each(function() {
			var $this = $(this);
			var dest = (settings.target === null ? $this.append($('<div>')) : $(settings.target));

			if (settings.target === null)  $this.data('textcountertarget', dest);

			var CounterHandler = function(e) {
				var val = (settings.valueCallback ? settings.valueCallback($this) : $this.val());
				var vallen = (settings.unit === 'words' ? val.split(/\s+/).length : val.length);

				dest.removeClass(settings.okayClass).removeClass(settings.errorClass);

				if (typeof(settings.limit) === 'number')
				{
					var valid = (settings.limit == 0 || vallen <= settings.limit);

					dest.addClass(valid ? settings.okayClass : settings.errorClass);
					dest.html((valid ? '' : settings.errorMsg + '  ') + (vallen == 1 ? settings.mainMsgOne : settings.mainMsg).replace('{x}', vallen).replace('{y}', settings.limit));
				}
				else
				{
					var valid = (vallen >= settings.limit.min && vallen <= settings.limit.max);

					var limitdisp = settings.limitRangeMsg.replace('{min}', settings.limit.min).replace('{max}', settings.limit.max);

					dest.addClass(valid ? settings.okayClass : settings.errorClass);
					dest.html((valid ? '' : (vallen < settings.limit.min ? settings.errorLowerMsg : settings.errorMsg) + '  ') + (vallen == 1 ? settings.mainMsgOne : settings.mainMsg).replace('{x}', vallen).replace('{y}', limitdisp));
				}
			};

			$this.on('keydown.textcounter', CounterHandler).on('keyup.textcounter', CounterHandler).on('change.textcounter', CounterHandler).change();
		});
	}

	$.fn.TextCounter.defaults = {
		'limit' : 0,
		'unit' : 'characters',
		'okayClass' : 'textcounter_okay',
		'errorClass' : 'textcounter_error',
		'limitRangeMsg' : '{min} to {max}',
		'mainMsg' : '{x} of {y} characters entered.',
		'mainMsgOne' : '{x} of {y} characters entered.',
		'errorMsg' : 'Too many characters entered.',
		'errorLowerMsg' : 'Too few characters entered.'
	};
}(jQuery));
