var CE_CACHE = CE_CACHE || {};

CE_CACHE.viewItems = function(){
	var $appEl = $('[data-ce-cache-view-items]');
	if ($appEl.length) {

		var appData = {};

		//get the data variable
		var dataVar = $appEl.attr('data-var');
		if (dataVar) {
			appData = window[dataVar];
		}

		new Vue({
			el: $appEl[0],
			data: {
				//language strings
				lang: appData.lang,

				//data
				items : [],
				breadcrumbs : [],

				//the error message
				errorMessage: '',

				//delete
				deleteItemCandidate: null,
				deleteItemCanRefresh: false,
				deleteRefresh: false,
				deleteRefreshTime: 15,

				//request flag
				isRequestInProgress: false
			},
			methods: {
				/**
				 * Gets the breadcrumbs and items for a given path.
				 *
				 * @param path
				 */
				getLevel: function(path) {
					var that = this;
					this.isRequestInProgress = true;

					$.jsonp({
						url: appData.urls.getLevel,
						data: {
							'path': path,
							'prefix': appData.prefix,
							'driver': appData.driver,
							'secret': appData.secret
						},
						cache: false,
						callbackParameter: 'callback',
						success: function( response ) {
							that.isRequestInProgress = false;
							if ( response && response.success ) { //success, set the data
								if (response.hasOwnProperty('data')) {
									if (response.data.hasOwnProperty('items')) {
										that.items = response.data.items;
									}
									if (response.data.hasOwnProperty('breadcrumbs')) {
										that.breadcrumbs = response.data.breadcrumbs;
									}
								}
								that.errorMessage = '';
							} else { //fail, show the error
								that.errorMessage = (response.hasOwnProperty('message') && response.message) ? response.message :  appData.lang.unknown_error;
							}
						},
						error: function(xOptions, textStatus) {
							that.isRequestInProgress = false;
							that.errorMessage = textStatus;
						}
					});
				},
				/**
				 * Deletes the item or items for a given path.
				 *
				 * @param path
				 * @param item
				 * @param refresh
				 * @param refresh_time
				 */
				deleteItem: function(path, item, refresh, refresh_time) {
					var that = this;
					this.isRequestInProgress = true;

					$.jsonp({
						url: appData.urls.deleteItem,
						data: {
							'path': path,
							'prefix': appData.prefix,
							'driver': appData.driver,
							'secret': appData.secret,
							'refresh': refresh,
							'refresh_time' : refresh_time
						},
						cache: false,
						callbackParameter: 'callback',
						success: function( response ) {
							that.isRequestInProgress = false;
							if ( response.success ) {
								var index = that.items.indexOf(item);
								if (index > -1) {
									that.items.splice(index, 1);
								}

								that.errorMessage = '';
							} else { //a problem occurred
								that.errorMessage = (response.hasOwnProperty('message') && response.message) ? response.message :  appData.lang.unknown_error;
							}
						},
						error:function (xOptions, textStatus) {
							that.isRequestInProgress = false;
							that.errorMessage = textStatus;
						}
					});
				},
				/**
				 * Generates the view link for an item.
				 *
				 * @param fullPath
				 * @returns {string}
				 */
				itemViewLink: function (fullPath) {
					return appData.urls.home+'/view_item/'+appData.driver+'&item='+appData.prefix+fullPath;
				},
				/**
				 * Sets up and opens the delete confirmation modal with sensible defaults.
				 *
				 * @param item
				 */
				deleteStarted: function(item) {
					this.deleteItemCandidate = item;

					//can refresh if not a global
					this.deleteItemCanRefresh = (item.id_full.substring(0,6) !== 'global');

					//reset the refresh settings
					this.deleteRefresh = false;
					this.deleteRefreshTime = (item.type === 'folder') ? 15 : 0;

					//open the modal
					$(this.$refs.modalTrigger).trigger('click');
				},
				/**
				 * Confirms the delete and makes the delete request.
				 */
				deleteConfirmed: function() {
					//close the modal
					$(this.$refs.modalClose).trigger('click');

					//delete the item
					this.deleteItem(this.deleteItemCandidate.id_full, this.deleteItemCandidate, this.deleteRefresh, this.deleteRefreshTime);
				}
			},
			created: function () {
				this.getLevel('/');
			}
		});
	}
};

CE_CACHE.viewItems();