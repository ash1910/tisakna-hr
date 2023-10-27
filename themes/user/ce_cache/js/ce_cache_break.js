var CE_CACHE = CE_CACHE || {};

CE_CACHE.cacheBreak = function(){
	var $appEl = $('[data-ce-cache-break-setting]');
	if ($appEl.length) {

		var appData = {};

		//get the data variable
		var dataVar = $appEl.attr('data-var');
		if (dataVar) {
			appData = window[dataVar];
		}

		var eventHub = new Vue();

		Vue.component('tags', {
			template: '#tags-template',
			props: ['value', 'name', 'id', 'add-text'],
			mounted: function(){
				var that = this;

				$(this.$el)
					.val(this.value)
					.tagsInput({
						delimiter : '|',
						defaultText: that.addText,
						maxChars : 99,
						onChange: function(){
							that.$emit('input', $(this).val())
						}
					});
			}
		});

		Vue.component('item', {
			template: '#item-template',
			props: ['value', 'name'],
			data: function(){
				return {
					itemType: 'local',
					itemPath: '/'
				}
			},
			methods: {
				cleanItemPath: function(){
					//trim the path
					this.itemPath = this.itemPath.trim();

					//make sure the path starts with a slash
					if (this.itemPath.indexOf('/') !== 0) {
						this.itemPath = '/' + this.itemPath;
					}

					if ((this.itemPath.length+this.itemType.length) > 250) {
						this.itemPath = this.itemPath.substring(0, 250 - this.itemType.length);
					}
				},
				updateInput: function(){
					this.cleanItemPath();
					this.$emit('input', this.itemType+this.itemPath);
				},
				updateFromValue: function() {
					if (this.value.indexOf('global/') === 0) {
						this.itemType = 'global';
					} else if (this.value.indexOf('static/') === 0) {
						this.itemType = 'static';
					} else if (this.value.indexOf('any/') === 0) {
						this.itemType = 'any';
					} else {
						this.itemType = 'local';
					}

					if (this.value.length > this.itemType.length) {
						this.itemPath = this.value.substring(this.itemType.length);
					}
				},
				typeChange: function() {
					this.itemType = this.$refs.typeSelect.value;
					this.updateInput();
				}
			},
			mounted: function(){
				this.updateFromValue();
				this.updateInput();
			},
			created: function () {
				eventHub.$on('items-updated', this.updateFromValue);
			},
			beforeDestroy: function () {
				eventHub.$off('items-updated', this.updateFromValue);
			}
		});

		new Vue({
			el: $appEl[0],
			data: {
				items : appData.items,
				tags : appData.tags,
				refresh_time : appData.refresh_time,
				refresh : appData.refresh,
				showTagExamples: false,
				showPathExamples: false
			},
			methods: {
				addItem: function() {
					this.items.push('local/');
				},
				removeItem: function(index) {
					this.items.splice(index,1);

					//needed to get Vue to re-render the components
					Vue.nextTick(function () {
						eventHub.$emit('items-updated');
					})
				},
				toggleTagExamples: function(){
					this.showTagExamples = ! this.showTagExamples;
				},
				togglePathExamples: function(){
					this.showPathExamples = ! this.showPathExamples;
				}
			}
		});
	}
};

CE_CACHE.cacheBreak();