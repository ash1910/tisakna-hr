<div class="ce-cache">
	<div class="box ce-cache-loader-holder">
		<h1><?php echo $title ?></h1>
		<div class="md-wrap">
			<div data-ce-cache-view-items data-var="CE_CACHE_VIEW_ITEMS_SETTINGS" v-cloak>

				<div class="ce-cache-breadcrumbs" v-if="breadcrumbs.length">
					<ul class="breadcrumb">
						<li v-for="(breadcrumb, index) in breadcrumbs"><a href="#" @click.prevent="getLevel(breadcrumb.path)" :class="index > 0 ? 'ce-cache-folder' : ''">{{ breadcrumb.name }}</a></li>
					</ul>
				</div><!-- .ce-cache-breadcrumbs -->

				<div class="alert inline issue" v-if="!! errorMessage" v-show="! isRequestInProgress">
					<a class="close" href="#"></a>
					<p>{{ errorMessage }}</p>
				</div>

				<div class="no-results" v-if="! items.length" v-show="! isRequestInProgress">
					<p><?php echo lang('ce_cache_no_items'); ?></p>
				</div>

				<div class="tbl-wrap" v-show="! isRequestInProgress">
					<table cellspacing="0" v-if="items.length">
						<thead>
							<tr>
								<th class="ce-cache-vi-table-items"><?php echo lang('ce_cache_items'); ?></th>
								<th class="ce-cache-vi-table-created"><?php echo lang('ce_cache_created'); ?></th>
								<th class="ce-cache-vi-table-expires"><?php echo lang('ce_cache_expires'); ?></th>
								<th class="ce-cache-vi-table-view">&nbsp;</th>
								<th class="ce-cache-vi-table-delete">&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in items">
								<template v-if="item.type == 'folder'">
									<td class="ce-cache-vi-table-items"><a :class="'ce-cache-'+item.type" href="#" @click.prevent="getLevel(item.id_full)">{{ item.id }}</a></td>
									<td class="ce-cache-vi-table-created">&ndash;</td>
									<td class="ce-cache-vi-table-expires">&ndash;</td>
									<td class="ce-cache-vi-table-view">&ndash;</td>
									<td class="ce-cache-vi-table-delete"><a href="#" class="ce-cache-delete" @click.prevent="deleteStarted(item)">Delete</a></td>
								</template>
								<template v-else>
									<td class="ce-cache-vi-table-items"><a :class="'ce-cache-'+item.type" :href="itemViewLink(item.id_full)">{{ item.id }}</a></td>
									<td class="ce-cache-vi-table-created">{{ item.made }}</td>
									<td class="ce-cache-vi-table-expires">{{ item.expiry }}</td>
									<td class="ce-cache-vi-table-view"><a :href="itemViewLink(item.id_full)" class="ce-cache-view-item">View</a></td>
									<td class="ce-cache-vi-table-delete"><a href="#" class="ce-cache-delete" @click.prevent="deleteStarted(item)">Delete</a></td>
								</template>
							</tr>
						</tbody>
					</table>
				</div><!-- .tbl-wrap -->

				<div class="ce-cache-loader" v-show="isRequestInProgress"><!-- --></div>

				<div class="modal-wrap ce-cache-delete-modal">
					<div class="modal">
						<div class="col-group">
							<div class="col w-16">
								<a ref="modalClose" class="m-close" href="#"></a>
								<div class="box">
									<div class="md-wrap" v-if="deleteItemCandidate">
										<!-- show the confirmation question -->
										<p>{{ deleteItemCandidate.type == 'folder' ? lang.delete_child_items_confirmation.replace('%s', deleteItemCandidate.id) :  lang.delete_child_item_confirmation.replace('%s', deleteItemCandidate.id) }}</p>

										<!-- conditionally show the refresh -->
										<template v-if="deleteItemCanRefresh">
											<p><input type="checkbox" id="ce_cache_refresh_items" v-model="deleteRefresh"> <label for="ce_cache_refresh_items">{{ deleteItemCandidate.type == 'folder' ? lang.delete_child_items_refresh : lang.delete_child_item_refresh }}</label></p>
											<template v-if="deleteRefresh && deleteItemCandidate.type == 'folder'">
												<p>
													<label for="ce_cache_refresh_time">{{ lang.delete_child_items_refresh_time }}</label><br>
													<select name="ce_cache_refresh_time" id="ce_cache_refresh_time" v-model="deleteRefreshTime">
														<option value="15"><?php echo sprintf(lang('ce_cache_refresh_delay'), 15); ?></option>
														<option value="14"><?php echo sprintf(lang('ce_cache_refresh_delay'), 14); ?></option>
														<option value="13"><?php echo sprintf(lang('ce_cache_refresh_delay'), 13); ?></option>
														<option value="12"><?php echo sprintf(lang('ce_cache_refresh_delay'), 12); ?></option>
														<option value="11"><?php echo sprintf(lang('ce_cache_refresh_delay'), 11); ?></option>
														<option value="10"><?php echo sprintf(lang('ce_cache_refresh_delay'), 10); ?></option>
														<option value="9"><?php echo sprintf(lang('ce_cache_refresh_delay'), 9); ?></option>
														<option value="8"><?php echo sprintf(lang('ce_cache_refresh_delay'), 8); ?></option>
														<option value="7"><?php echo sprintf(lang('ce_cache_refresh_delay'), 7); ?></option>
														<option value="6"><?php echo sprintf(lang('ce_cache_refresh_delay'), 6); ?></option>
														<option value="5"><?php echo sprintf(lang('ce_cache_refresh_delay'), 5); ?></option>
														<option value="4"><?php echo sprintf(lang('ce_cache_refresh_delay'), 4); ?></option>
														<option value="3"><?php echo sprintf(lang('ce_cache_refresh_delay'), 3); ?></option>
														<option value="2"><?php echo sprintf(lang('ce_cache_refresh_delay'), 2); ?></option>
														<option value="1"><?php echo sprintf(lang('ce_cache_refresh_delay'), 1); ?></option>
														<option value="0"><?php echo lang('ce_cache_refresh_all_at_once'); ?></option>
													</select>
												</p>
											</template>
										</template>

										<!-- show the button -->
										<p><a href="#" @click.prevent="deleteConfirmed()" class="btn action">{{ deleteItemCandidate.type == 'folder' ? lang.delete_child_items_button : lang.delete_child_item_button }}</a></p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div><!-- .modal-wrap -->
				<a ref="modalTrigger" class="m-link ce-cache-hidden" rel="ce-cache-delete-modal" href="#">Show modal</a>

			</div><!-- .ce-cache-view-items -->

			<?php
			/*
			<div id="ce_cache_breadcrumbs"><!-- --></div><!-- #ce_cache_breadcrumbs -->
			<div id="ce_cache_tree">
				<p>
				<span class="ce_cache_items"><?php
					echo lang( 'ce_cache_items' ); ?></span><span class="ce_cache_made"><?php echo lang( 'ce_cache_created' ); ?></span><span class="ce_cache_expiry"><?php echo lang( 'ce_cache_expires' ); ?></span>
				</p>

				<div id="ce_cache_items_holder">
					<div id="ce_cache_items_list"><!-- --></div>
					<div id="ce_cache_loader" class="ce_cache_empty"><!-- --></div>
				</div><!-- #ce_cache_items_holder -->
			</div><!-- #ce_cache_tree -->
			<div id="ce_cache_delete_dialog"><!-- --></div>
			*/
			?>
		</div><!-- .md-wrap -->
	</div><!-- .box -->
</div><!-- .ce-cache -->