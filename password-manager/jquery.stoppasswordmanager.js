// jQuery plugin to prevent password managers from detecting password fields while visually hiding their contents.
// (C) 2017 CubicleSoft.  All Rights Reserved.

(function($) {
	$.fn.StopPasswordManager = function() {
		return this.each(function() {
			var $this = $(this);

			$this.attr('data-background-color', $this.css('background-color'));
			$this.css('background-color', $this.css('color'));
			$this.attr('type', 'text');

			$this.focus(function() {
				$this.attr('type', 'password');
				$this.css('background-color', $this.attr('data-background-color'));
			});

			$this.blur(function() {
				$this.css('background-color', $this.css('color'));
				$this.attr('type', 'text');
			});
		});
	}
}(jQuery));
