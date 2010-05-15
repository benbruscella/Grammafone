/**
 * jQuery Copy-Paste plugin
 *
 * Copyright (c) 2009 Anton Shevchuk
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * @author 	Anton Shevchuk AntonShevchuk@gmail.com
 * @version 0.0.1
 */
// http://the-stickman.com/web-development/javascript/finding-selection-cursor-position-in-a-textarea-in-internet-explorer/
;(function($) {
    $.fn.buffer = function() {
        if (arguments.length) {
            $.fn.buffer.defaults.value = arguments[0];
        }
        return $.fn.buffer.defaults.value;
    };
    $.fn.buffer.defaults = {value:null};
    $.fn.cut = function() {
        this.each(function(){
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
//                sel.execCommand('Cut');
                $.fn.buffer(sel.text);
                sel.text = '';
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                var startPos  = this.selectionStart;
                var endPos    = this.selectionEnd;
                var scrollTop = this.scrollTop;
                $.fn.buffer(this.value.substring(startPos, endPos));
                this.value    = this.value.substring(0, startPos) + this.value.substring(endPos,this.value.length);
                this.focus();
                this.selectionStart = startPos;
                this.selectionEnd = startPos;
                this.scrollTop = scrollTop;
            } else {
                $.fn.buffer = this.value;
                this.focus();
            }
        });
        return this;
    };
    $.fn.copy = function() {
        this.each(function(){
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
//                sel.execCommand('Copy');
                $.fn.buffer(sel.text);
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                var startPos  = this.selectionStart;
                var endPos    = this.selectionEnd;
                var scrollTop = this.scrollTop;
                $.fn.buffer(this.value.substring(startPos, endPos));
                this.focus();
                this.selectionStart = startPos;
                this.selectionEnd = startPos;
                this.scrollTop = scrollTop;
            } else {
                $.fn.buffer = this.value;
                this.focus();
            }
        });
        return this;
    };
    $.fn.paste = function() {
        this.each(function(){
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
//                sel.execCommand('Paste');
                sel.text = $.fn.buffer();
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                var startPos  = this.selectionStart;
                var endPos    = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value    = this.value.substring(0, startPos)+$.fn.buffer()+this.value.substring(endPos,this.value.length);
                this.focus();
                this.selectionStart = startPos + $.fn.buffer()['length'];
                this.selectionEnd   = startPos + $.fn.buffer()['length'];
                this.scrollTop      = scrollTop;
            } else {
                this.value += $.fn.buffer();
                this.focus();
            }
        });
        return this;
    }
})(jQuery);