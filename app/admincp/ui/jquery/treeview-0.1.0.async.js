/*
 * Async Treeview 0.1 - Lazy-loading extension for Treeview
 *
 * http://bassistance.de/jquery-plugins/jquery-plugin-treeview/
 *
 * Copyright (c) 2007 Jörn Zaefferer
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Revision: $Id: jquery.treeview.async.js 179 2013-03-29 03:21:28Z coolmoo $
 *
 */

;
(function($) {
	function load(settings, root, child, container) {
		$.getJSON(settings.url, {
			root: root
		}, function(response) {
			$("#tree-loading").remove();
			function createNode(parent) {
				//	alert(this.text);
				var current = $("<li/>").attr("id", this.id || "").html(this.text).appendTo(parent);
				current.mouseover(function() {
					$(this).css("background-color", "#E7E7E7");
				}).mouseout(function() {
					$(this).css("background-color", "#FFFFFF");
				});
				if (this.expanded) {
					current.addClass("open");
				}
				if (this.hasChildren || this.children && this.children.length) {
					var branch = $("<ul/>").appendTo(current);
					if (this.hasChildren) {
						current.addClass("hasChildren");
						createNode.call({
							text: "数据加载中...请稍候!",
							id: "iCMS-category-Tree-0",
							children: []
						}, branch);
					}
					if (this.children && this.children.length) {
						$.each(this.children, createNode, [branch])
					}
					if (settings.sortable) {
						branch.sortable({
							//items: ".row-fluid",
							helper: "clone",
							placeholder: "ui-state-highlight",
							delay: 100,
							//appendTo:'#tree > li',
							//containment: 'parent',
							start: function(event, ui) {
								var ul = ui.item.parent();
								$(ui.item).show().css({
									'opacity': 0.5
								});
							},
							stop: function(event, ui) {
								$(ui.item).css({
									'opacity': 1
								});
								var ul = ui.item.parent();
								var ord = $(".ordernum > input", ul);
								var ordernum = new Array();
								ord.each(function(i) {
									$(this).val(i);
									var id = $(this).attr("data-id");
									ordernum.push(id);
								});
								$.post(upordurl, {
									ordernum: ordernum
								});
							}
						}).disableSelection();
					}
				}
			}
			$.each(response, createNode, [child]);
			$(container).treeview({
				add: child
			});
		});
	}

	var proxied = $.fn.treeview;
	$.fn.treeview = function(settings) {
		if (!settings.url) {
			return proxied.apply(this, arguments);
		}
		var container = this;
		load(settings, "source", this, container);
		var userToggle = settings.toggle;
		return proxied.call(this, $.extend({}, settings, {
			collapsed: true,
			toggle: function() {
				var $this = $(this);
				if ($this.hasClass("hasChildren")) {
					var childList = $this.removeClass("hasChildren").find("ul");
					childList.empty();
					load(settings, this.id, childList, container);
				}
				if (userToggle) {
					userToggle.apply(this, arguments);
				}
			}
		}));
	};

})(jQuery);
