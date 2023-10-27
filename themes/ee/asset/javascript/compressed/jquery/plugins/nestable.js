/*!
 * Nestable jQuery Plugin - Copyright (c) 2012 David Bushell - http://dbushell.com/
 * Dual-licensed under the BSD or MIT licenses
 */
!function(t,s,e,i){function a(s,i){this.w=t(e),this.el=t(s),this.options=t.extend({},n,i),this.init()}var o="ontouchstart"in e,l=function(){var t=e.createElement("div"),i=e.documentElement;if(!("pointerEvents"in t.style))return!1;t.style.pointerEvents="auto",t.style.pointerEvents="x",i.appendChild(t);var a=s.getComputedStyle&&"auto"===s.getComputedStyle(t,"").pointerEvents;return i.removeChild(t),!!a}(),n={listNodeName:"ol",itemNodeName:"li",rootClass:"dd",listClass:"dd-list",itemClass:"dd-item",dragClass:"dd-dragel",handleClass:"dd-handle",collapsedClass:"dd-collapsed",placeClass:"dd-placeholder",noDragClass:"dd-nodrag",emptyClass:"dd-empty",expandBtnHTML:'<button data-action="expand" type="button">Expand</button>',collapseBtnHTML:'<button data-action="collapse" type="button">Collapse</button>',group:0,maxDepth:5,threshold:20,constrainToRoot:!1};a.prototype={init:function(){var e=this;e.reset(),0!==this.options.group?e.el.data("nestable-group",this.options.group):e.el.data("nestable-group")!==i&&(this.options.group=e.el.data("nestable-group")),this.options.placeElement!==i?e.placeEl=this.options.placeElement:e.placeEl=t('<div class="'+e.options.placeClass+'"/>'),t.each(this.el.find(e.options.itemNodeName+"."+e.options.itemClass),function(s,i){e.setParent(t(i))}),e.el.on("click","button",function(s){if(!e.dragEl){var i=t(s.currentTarget),a=i.data("action"),o=i.parent(e.options.itemNodeName+"."+e.options.itemClass);"collapse"===a&&e.collapseItem(o),"expand"===a&&e.expandItem(o)}});var a=function(s){var i=t(s.target);if(!i.hasClass(e.options.handleClass)){if(i.closest("."+e.options.noDragClass).length)return;i=i.closest("."+e.options.handleClass)}i.length&&!e.dragEl&&(e.isTouch=/^touch/.test(s.type),e.isTouch&&1!==s.touches.length||(s.preventDefault(),e.dragStart(s.touches?s.touches[0]:s)))},l=function(t){e.dragEl&&(t.preventDefault(),e.dragMove(t.touches?t.touches[0]:t))},n=function(t){e.dragEl&&(t.preventDefault(),e.dragStop(t.touches?t.touches[0]:t))};o&&(e.el[0].addEventListener("touchstart",a,!1),s.addEventListener("touchmove",l,!1),s.addEventListener("touchend",n,!1),s.addEventListener("touchcancel",n,!1)),e.el.on("mousedown",a),e.w.on("mousemove",l),e.w.on("mouseup",n)},serialize:function(){var s,e=0,i=this;return step=function(s,e){var a=[],o=s.children(i.options.itemNodeName+"."+i.options.itemClass);return o.each(function(){var s=t(this),o=t.extend({},s.data()),l=s.children(i.options.listNodeName+"."+i.options.listClass);l.length&&(o.children=step(l,e+1)),a.push(o)}),a},s=step(i.el.find(i.options.listNodeName+"."+i.options.listClass).first(),e)},serialise:function(){return this.serialize()},reset:function(){this.mouse={offsetX:0,offsetY:0,startX:0,startY:0,lastX:0,lastY:0,nowX:0,nowY:0,distX:0,distY:0,dirAx:0,dirX:0,dirY:0,lastDirX:0,lastDirY:0,distAxX:0,distAxY:0},this.isTouch=!1,this.moving=!1,this.dragEl=null,this.dragRootEl=null,this.dragDepth=0,this.hasNewRoot=!1,this.pointEl=null},expandItem:function(t){t.removeClass(this.options.collapsedClass),t.children('[data-action="expand"]').hide(),t.children('[data-action="collapse"]').show(),t.children(this.options.listNodeName+"."+this.options.listClass).show()},collapseItem:function(t){var s=t.children(this.options.listNodeName+"."+this.options.listClass);s.length&&(t.addClass(this.options.collapsedClass),t.children('[data-action="collapse"]').hide(),t.children('[data-action="expand"]').show(),t.children(this.options.listNodeName+"."+this.options.listClass).hide())},expandAll:function(){var s=this;s.el.find(s.options.itemNodeName+"."+s.options.itemClass).each(function(){s.expandItem(t(this))})},collapseAll:function(){var s=this;s.el.find(s.options.itemNodeName+"."+s.options.itemClass).each(function(){s.collapseItem(t(this))})},setParent:function(s){s.children(this.options.listNodeName+"."+this.options.listClass).length&&(s.prepend(t(this.options.expandBtnHTML)),s.prepend(t(this.options.collapseBtnHTML))),s.children('[data-action="expand"]').hide()},unsetParent:function(t){t.removeClass(this.options.collapsedClass),t.children("[data-action]").remove(),t.children(this.options.listNodeName+"."+this.options.listClass).remove()},dragStart:function(s){var a=this.mouse,o=t(s.target),l=o.closest(this.options.itemNodeName+"."+this.options.itemClass);this.placeEl.css("height",l.height()),a.offsetX=s.offsetX!==i?s.offsetX:s.pageX-o.offset().left,a.offsetY=s.offsetY!==i?s.offsetY:s.pageY-o.offset().top,a.startX=a.lastX=s.pageX,a.startY=a.lastY=s.pageY,this.dragRootEl=this.el,this.dragEl=t(e.createElement(this.options.listNodeName)).addClass(this.options.listClass+" "+this.options.dragClass.replace("."," ")),this.dragEl.css("width",l.width()),l.after(this.placeEl),l[0].parentNode.removeChild(l[0]),l.appendTo(this.dragEl);var n=this.options.constrainToRoot?this.el:e.body;t(n).append(this.dragEl),this.options.constrainToRoot?this.dragEl.css({left:s.pageX-this.el.offset().left-a.offsetX,top:s.pageY-this.el.offset().top-a.offsetY}):this.dragEl.css({left:s.pageX-a.offsetX,top:s.pageY-a.offsetY});var d,h,r=this.dragEl.find(this.options.itemNodeName+"."+this.options.itemClass);for(d=0;d<r.length;d++)h=t(r[d]).parents(this.options.listNodeName+"."+this.options.listClass).length,h>this.dragDepth&&(this.dragDepth=h)},dragStop:function(t){var s=this.dragEl.children(this.options.itemNodeName+"."+this.options.itemClass).first();s[0].parentNode.removeChild(s[0]),this.placeEl.replaceWith(s),this.dragEl.remove(),this.el.trigger("change"),this.hasNewRoot&&this.dragRootEl.trigger("change"),this.reset()},dragMove:function(i){var a,o,n,d,h,r=this.options,p=this.mouse;this.options.constrainToRoot?this.dragEl.css({left:i.pageX-this.el.offset().left-p.offsetX,top:i.pageY-this.el.offset().top-p.offsetY}):this.dragEl.css({left:i.pageX-p.offsetX,top:i.pageY-p.offsetY}),p.lastX=p.nowX,p.lastY=p.nowY,p.nowX=i.pageX,p.nowY=i.pageY,p.distX=p.nowX-p.lastX,p.distY=p.nowY-p.lastY,p.lastDirX=p.dirX,p.lastDirY=p.dirY,p.dirX=0===p.distX?0:p.distX>0?1:-1,p.dirY=0===p.distY?0:p.distY>0?1:-1;var c=Math.abs(p.distX)>Math.abs(p.distY)?1:0;if(!p.moving)return p.dirAx=c,void(p.moving=!0);p.dirAx!==c?(p.distAxX=0,p.distAxY=0):(p.distAxX+=Math.abs(p.distX),0!==p.dirX&&p.dirX!==p.lastDirX&&(p.distAxX=0),p.distAxY+=Math.abs(p.distY),0!==p.dirY&&p.dirY!==p.lastDirY&&(p.distAxY=0)),p.dirAx=c,p.dirAx&&p.distAxX>=r.threshold&&(p.distAxX=0,n=this.placeEl.prev(r.itemNodeName+"."+r.itemClass),p.distX>0&&n.length&&!n.hasClass(r.collapsedClass)&&(a=n.find(r.listNodeName+"."+r.listClass).last(),h=this.placeEl.parents(r.listNodeName+"."+r.listClass).length,h+this.dragDepth<=r.maxDepth&&(a.length?(a=n.children(r.listNodeName+"."+r.listClass).last(),a.append(this.placeEl)):(a=t("<"+r.listNodeName+"/>").addClass(r.listClass),a.append(this.placeEl),n.append(a),this.setParent(n)))),p.distX<0&&(d=this.placeEl.next(r.itemNodeName+"."+r.itemClass),d.length||(o=this.placeEl.parent(),this.placeEl.closest(r.itemNodeName+"."+r.itemClass).after(this.placeEl),o.children().length||this.unsetParent(o.parent()))));var f=!1;if(l||(this.dragEl[0].style.visibility="hidden"),this.pointEl=t(e.elementFromPoint(i.pageX-e.body.scrollLeft,i.pageY-(s.pageYOffset||e.documentElement.scrollTop))),l||(this.dragEl[0].style.visibility="visible"),this.pointEl.hasClass(r.handleClass)&&(this.pointEl=this.pointEl.closest(r.itemNodeName+"."+r.itemClass)),this.pointEl.hasClass(r.emptyClass))f=!0;else if(!this.pointEl.length||!this.pointEl.hasClass(r.itemClass))return;var g=this.pointEl.closest("."+r.rootClass),m=this.dragRootEl.data("nestable-id")!==g.data("nestable-id");if(!p.dirAx||m||f){if(m&&0!==r.group&&r.group!==g.data("nestable-group"))return;if(h=this.dragDepth-1+this.pointEl.parents(r.listNodeName+"."+r.listClass).length,h>r.maxDepth)return;var u=i.pageY<this.pointEl.offset().top+this.pointEl.height()/2;o=this.placeEl.parent(),f?(a=t(e.createElement(r.listNodeName)).addClass(r.listClass),a.append(this.placeEl),this.pointEl.replaceWith(a)):u?this.pointEl.before(this.placeEl):this.pointEl.after(this.placeEl),o.children().length||this.unsetParent(o.parent()),this.dragRootEl.find(r.itemNodeName+"."+r.itemClass).length||this.dragRootEl.append('<div class="'+r.emptyClass+'"/>'),m&&(this.dragRootEl=g,this.hasNewRoot=this.el[0]!==this.dragRootEl[0])}}},t.fn.nestable=function(s){var e=this,i=this;return e.each(function(){var e=t(this).data("nestable");e?"string"==typeof s&&"function"==typeof e[s]&&(i=e[s]()):(t(this).data("nestable",new a(this,s)),t(this).data("nestable-id",(new Date).getTime()))}),i||e}}(window.jQuery||window.Zepto,window,document);