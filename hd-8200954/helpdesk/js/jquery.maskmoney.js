/*
* @Copyright (c) 2010 Aurélio Saraiva, Diego Plentz
* @Page http://github.com/plentz/jquery-maskmoney
* try at http://inoveideia.com.br/maskInputMoney/

* Permission is hereby granted, free of charge, to any person
* obtaining a copy of this software and associated documentation
* files (the "Software"), to deal in the Software without
* restriction, including without limitation the rights to use,
* copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the
* Software is furnished to do so, subject to the following
* conditions:
* The above copyright notice and this permission notice shall be
* included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
* EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
* OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
* NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
* HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
* WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
* FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
* OTHER DEALINGS IN THE SOFTWARE.
*/

/*
* @Version: 0.5
* @Release: 2010-10-19
*/
(function($) {
$.fn.maskMoney = function(settings) {
settings = $.extend({
symbol:'',
decimal:'.',
precision:2,
thousands:'',
allowZero:false,
allowNegative:false,
showSymbol:false
}, settings);

settings.symbol=settings.symbol+' ';

return this.each(function() {
var input = $(this);

function keypressEvent(e) {
e = e||window.event;
var k = e.charCode||e.keyCode||e.which;
if (k == undefined) return; //needed to handle an IE "special" event

if (k==8) { // backspace key
preventDefault(e);
var x = input.val().substring(0,input.val().length-1);
input.val(maskValue(x));
return false;
} else if (k==9) { // tab key
return true;
}
if (k<48||k>57) {
if (k==45) { // -(minus) key
input.val(changeSign(input));
return false;
}
if (k==43) { // +(plus) key
input.val(input.val().replace('-',''));
return false;
} else {
preventDefault(e);
return true;
}
} else if (input.val().length==input.attr('maxlength')) {
return false;
}

var key = String.fromCharCode(k);
preventDefault(e);
input.val(maskValue(input.val()+key));
}

function focusEvent(e) {
if (input.val()=='') {
input.val(setSymbol(getDefaultMask()));
} else {
input.val(setSymbol(input.val()));
}
                if (this.createTextRange) {
                    var textRange = this.createTextRange();
                    textRange.collapse(false); // set the cursor at the end of the input
                    textRange.select();
                }
}

function blurEvent(e) {
                if ($.browser.msie) {
                    keypressEvent(e);
                }

if (input.val()==setSymbol(getDefaultMask())) {
if(!settings.allowZero) input.val('');
} else {
input.val(input.val().replace(settings.symbol,''));
}
}

function preventDefault(e) {
if (e.preventDefault) { //standart browsers
e.preventDefault();
} else { // internet explorer
e.returnValue = false
}
}

function maskValue(v) {
v = v.replace(settings.symbol,'');

var strCheck = '0123456789';
var len = v.length;
var a = '', t = '', neg='';

if(len!=0 && v.charAt(0)=='-'){
v = v.replace('-','');
if(settings.allowNegative){
neg = '-';
}
}

if (len==0) {
t = '0.00';
}

for (var i = 0; i<len; i++) {
if ((v.charAt(i)!='0') && (v.charAt(i)!=settings.decimal)) break;
                }

for (; i<len; i++) {
if (strCheck.indexOf(v.charAt(i))!=-1) a+= v.charAt(i);
}

var n = parseFloat(a);
n = isNaN(n) ? 0 : n/Math.pow(10,settings.precision);
t = n.toFixed(settings.precision);

                i = settings.precision == 0 ? 0 : 1;
var p, d = (t=t.split('.'))[i].substr(0,settings.precision);
for (p = (t=t[0]).length; (p-=3)>=1;) {
t = t.substr(0,p)+settings.thousands+t.substr(p);
}

return (settings.precision>0)
                    ? setSymbol(neg+t+settings.decimal+d+Array((settings.precision+1)-d.length).join(0))
                    : setSymbol(neg+t);
}

function getDefaultMask() {
var n = parseFloat('0')/Math.pow(10,settings.precision);
return (n.toFixed(settings.precision)).replace(new RegExp('\\.','g'),settings.decimal);
}

function setSymbol(v) {
if (settings.showSymbol) {
return settings.symbol+v;
}
return v;
}

function changeSign(i){
if (settings.allowNegative) {
var vic = i.val();
if (i.val()!='' && i.val().charAt(0)=='-'){
return i.val().replace('-','');
} else{
return '-'+i.val();
}
} else {
return i.val();
}
}

input.bind('keypress',keypressEvent);
input.bind('blur',blurEvent);
input.bind('focus',focusEvent);

input.one('unmaskMoney',function() {
input.unbind('focus',focusEvent);
input.unbind('blur',blurEvent);
input.unbind('keypress',keypressEvent);

if ($.browser.msie) {
                    this.onpaste= null;
} else if ($.browser.mozilla) {
                    this.removeEventListener('input',blurEvent,false);
                }
});
});
}

$.fn.unmaskMoney=function() {
return this.trigger('unmaskMoney');
};
})(jQuery);