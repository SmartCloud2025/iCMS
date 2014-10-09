//插入内容
(function($) {
	$.fn.extend({
		insertContent: function(val, t) {
			//event.preventDefault();
			var $t = $(this)[0];

			if (document.selection) { //ie
				this.focus();
				var sel = document.selection.createRange();
				sel.text = val;
				this.focus();
				sel.moveStart('character', -l);
				var wee = sel.text.length;
				if (arguments.length == 2) {
					var l = $t.value.length;
					sel.moveEnd("character", wee + t);
					t <= 0 ? sel.moveStart("character", wee - 2 * t - val.length) : sel.moveStart("character", wee - t - val.length);
					sel.select();
				}
			} else if ($t.selectionStart || $t.selectionStart == '0') {
				var startPos = $t.selectionStart;
				var endPos = $t.selectionEnd;
				var scrollTop = $t.scrollTop;
				$t.value = $t.value.substring(0, startPos) + val + $t.value.substring(endPos, $t.value.length);
				this.focus();
				$t.selectionStart = startPos + val.length;
				$t.selectionEnd = startPos + val.length;
				$t.scrollTop = scrollTop;
				if (arguments.length == 2) {
					$t.setSelectionRange(startPos - t, $t.selectionEnd + t);
					this.focus();
				}
			} else {
				this.value += val;
				this.focus();
			}
		}
	})
})(jQuery);
