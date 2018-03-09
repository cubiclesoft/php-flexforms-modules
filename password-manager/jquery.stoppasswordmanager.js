// jQuery plugin to prevent password managers from detecting password fields while visually hiding their contents.
// (C) 2017 CubicleSoft.  All Rights Reserved.

(function($) {
	$.fn.StopPasswordManager = function() {
		return this.each(function() {
			var $this = $(this);

			$this.addClass('no-print');
			$this.attr('data-background-color', $this.css('background-color'));
			$this.css('background-color', $this.css('color'));
			$this.attr('type', 'text');
			$this.attr('autocomplete', 'off');

			$this.focus(function() {
				$this.attr('type', 'password');
				$this.css('background-color', $this.attr('data-background-color'));
			});

			$this.blur(function() {
				$this.css('background-color', $this.css('color'));
				$this.attr('type', 'text');
				$this[0].selectionStart = $this[0].selectionEnd;
			});

			$this.on('keydown', function(e) {
				if (e.keyCode == 13)
				{
					$this.css('background-color', $this.css('color'));
					$this.attr('type', 'text');
					$this[0].selectionStart = $this[0].selectionEnd;
				}
			});
		});
	}
}(jQuery));
