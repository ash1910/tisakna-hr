<?php if($this->enabled('tools')) : ?>
	<?php if(count($menu['channels']['create'])) : ?>
		<div class="tbl-search da-create-menu">
			<div class="filters right">
				<ul>
					<li>
						<a class="has-sub" href=""><?=lang('create_new')?></a>
						<div class="sub-menu">
							<div class="scroll-wrap">
								<ul>
									<?php foreach ($menu['channels']['create'] as $channel_name => $link): ?>
										<li><a href="<?=$link?>"><?=$channel_name?></a></li>
									<?php endforeach ?>
								</ul>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if (ee()->config->item('enable_comments') == 'y' && $can_moderate_comments): ?>
		<div class="col-group da-col-group">
			<div class="col w-16">
				<div class="box da-comment-alert">
					<div class="alert inline warn">
						<p><?=($number_of_pending_comments == 1 || (!$number_of_pending_comments && $number_of_spam_comments == 1) ) ? lang('da_there_is') : lang('da_there_are')?> 
						<?php if($number_of_pending_comments) : ?>
							<a href="<?=ee('CP/URL')->make('publish/comments', array('filter_by_status' => 'p'))?>"><?=$number_of_pending_comments?> <?=($number_of_pending_comments == 1) ? lang('da_comment') : lang('da_comments')?> <?=lang('da_awaiting_moderation')?></a><?php if ($spam_module_installed && $number_of_spam_comments > 0) : ?> <?=lang('da_and')?> <?php endif; ?><?php endif; ?><?php if ($spam_module_installed && $number_of_spam_comments > 0) : ?><a href="<?=ee('CP/URL')->make('publish/comments', array('filter_by_status' => 's'))?>"><?=$number_of_spam_comments?> <?=($number_of_spam_comments == 1) ? lang('da_comment') : lang('da_comments')?> <?=lang('da_flagged_as_spam')?></a><?php endif ?>.</p>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
	
<?php if($this->enabled('realtime')) : ?>
	<div class="da-realtime-outer" data-url="<?=$realtime_data_url;?>">
		<div class="da-realtime-inner col-group da-col-group">
		<?php if(!empty($realtime)) : ?>
			<div class="col w-4 da-rt-col da-rt-users">
				<div class="box">
					<div class="da-active-users">
						<strong><?=number_format($realtime['active_users']);?></strong>
						<?=($realtime['active_users'] == 1) ? lang('da_active_user') : lang('da_active_users');?>
					</div>
					<?php if(!empty($realtime['devices'])) : ?>
					<div class="da-rt-devices da-group">
					<?php foreach($realtime['devices'] as $row) : ?>
						<span class="<?=strtolower($row['device']);?>" style="width: <?=$row['percentage_precise'];?>;" title="<?=number_format($row['users']);?> <?=strtolower($row['device']);?> <?=($row['users'] == 1) ? strtolower(lang('da_user')) : strtolower(lang('da_users')); ?>"><?= ($row['percentage_numeric'] >= 10) ? $row['percentage'] : NBS; ?></span>
					<?php endforeach; ?>	
					</div>
					<ul class="da-rt-device-legend">
					<?php foreach($realtime['devices'] as $row) : ?>	
						<li><span class="<?=strtolower($row['device']);?>"></span> <?=$row['device'];?></li>
					<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
			</div>
			<div class="col w-4 da-rt-col da-rt-content">
				<div class="box">
					<table class="light da-table" cellspacing="0">
						<tr>
							<th class="da-label-col"><?=lang('da_rt_pages'); ?></th>
							<th class="da-count-col"><?=lang('da_users'); ?></th>
						</tr>
					<?php if(empty($realtime['content'])) : ?>
						<tr>
							<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
						</tr>
					<?php else : foreach($realtime['content'] as $row) : ?>
						<tr>
							<td class="da-label-col" title="<?=$row['page_title'];?>: <?=$row['page_path'];?>"><?=$row['page_title'];?></td>
							<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['users']);?>)</td>
						</tr>
					<?php endforeach; endif; ?>	
					</table>
				</div>
			</div>
			<div class="col w-4 da-rt-col da-rt-referrers">
				<div class="box">
					<table class="light da-table" cellspacing="0">
						<tr>
							<th class="da-label-col"><?=lang('da_rt_referrers'); ?></th>
							<th class="da-count-col"><?=lang('da_users'); ?></th>
						</tr>
					<?php if(empty($realtime['sources'])) : ?>
						<tr>
							<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
						</tr>
					<?php else : foreach($realtime['sources'] as $row) : ?>
						<tr>
							<td class="da-label-col"><?=$row['source'];?></td>
							<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['users']);?>)</td>
						</tr>
					<?php endforeach; endif; ?>	
					</table>
				</div>
			</div>
			<div class="col w-4 da-rt-col da-rt-countries">
				<div class="box">
					<table class="light da-table" cellspacing="0">
						<tr>
							<th class="da-label-col"><?=lang('da_rt_countries'); ?></th>
							<th class="da-count-col"><?=lang('da_users'); ?></th>
						</tr>
					<?php if(empty($realtime['countries'])) : ?>
						<tr>
							<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
						</tr>
					<?php else : foreach($realtime['countries'] as $row) : ?>
						<tr>
							<td class="da-label-col"><?=daFlagIcon($row['country'])?><?=$row['country'];?></td>
							<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['users']);?>)</td>
						</tr>
					<?php endforeach; endif; ?>
					</table>
				</div>
			</div>
		<?php endif ; ?>
		</div>
	</div>

<?php endif; ?>

<?php if($this->enabled('monthly')) : ?>
	<div class="da-monthly-outer" data-url="<?=$monthly_data_url;?>">
		<div class="da-monthly-inner">
			
			<div class="col-group da-col-group">
			<?php if(!empty($hourly)) : ?>
				<div class="col w-5 da-col-today">
					<div class="box">
						<h1><?=lang('da_today')?></h1>
						<table class="da-stat-panel" cellspacing="0">
							<tr>
								<td class="da-stat-col"><strong><?=number_format($hourly['visits'])?></strong> <?=lang('da_visits')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($hourly['visits_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($hourly['visits_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=number_format($hourly['pageviews'])?></strong> <?=lang('da_pageviews')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($hourly['pageviews_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($hourly['pageviews_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$hourly['pages_per_visit']?></strong> <?=lang('da_pages_per_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($hourly['pages_per_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($hourly['pages_per_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$hourly['avg_visit']?></strong> <?=lang('da_avg_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($hourly['avg_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($hourly['avg_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$hourly['bounce_rate']?></strong> <?=lang('da_bounce_rate')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($hourly['bounce_rate_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($hourly['bounce_rate_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
						</table>
						<!-- Last cached <?= ee()->localize->format_date('%h:%i%a', $hourly['cache_time']); ?> -->
					</div>
				</div>
			<?php endif; ?>
			
			<?php if(!empty($daily)) : ?>
				<div class="col w-5">
					<div class="box">
						<h1><?=lang('da_yesterday')?></h1>
						<table class="da-stat-panel" cellspacing="0">
							<tr>
								<td class="da-stat-col"><strong><?=number_format($daily['yesterday']['visits'])?></strong> <?=lang('da_visits')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['yesterday']['visits_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['yesterday']['visits_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=number_format($daily['yesterday']['pageviews'])?></strong> <?=lang('da_pageviews')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['yesterday']['pageviews_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['yesterday']['pageviews_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['yesterday']['pages_per_visit']?></strong> <?=lang('da_pages_per_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['yesterday']['pages_per_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['yesterday']['pages_per_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['yesterday']['avg_visit']?></strong> <?=lang('da_avg_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['yesterday']['avg_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['yesterday']['avg_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['yesterday']['bounce_rate']?></strong> <?=lang('da_bounce_rate')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['yesterday']['bounce_rate_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['yesterday']['bounce_rate_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="col w-6">
					<div class="box">
						<h1><?=lang('da_lastmonth')?></h1>
						<table class="da-stat-panel" cellspacing="0">
							<tr>
								<td class="da-stat-col"><strong><?=number_format($daily['lastmonth']['visits'])?></strong> <?=lang('da_visits')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['lastmonth']['visits_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['lastmonth']['visits_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=number_format($daily['lastmonth']['pageviews'])?></strong> <?=lang('da_pageviews')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['lastmonth']['pageviews_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['lastmonth']['pageviews_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['lastmonth']['pages_per_visit']?></strong> <?=lang('da_pages_per_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['lastmonth']['pages_per_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['lastmonth']['pages_per_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['lastmonth']['avg_visit']?></strong> <?=lang('da_avg_visit')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['lastmonth']['avg_visit_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['lastmonth']['avg_visit_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
							<tr>
								<td class="da-stat-col"><strong><?=$daily['lastmonth']['bounce_rate']?></strong> <?=lang('da_bounce_rate')?></td>
								<td class="da-stat-chart-col"><?php if(!empty($daily['lastmonth']['bounce_rate_sparkline_data'])) : ?><div class="da-sparkline" data-data='<?=json_encode($daily['lastmonth']['bounce_rate_sparkline_data'])?>'></div><?php endif; ?></td>
							</tr>
						</table>
					</div>
				</div>
			<?php endif; ?>
		</div>
		
		<?php if(!empty($daily)) : ?>
			<div class="col-group da-col-group">
				<div class="col w-8">
					<div class="box">
						<h1><?=lang('da_chart_title')?></h1>
						<?php if(!empty($daily['lastmonth']['traffic_chart'])) : ?>
						<div class="da-traffic-chart" data-data='<?=json_encode($daily['lastmonth']['traffic_chart'])?>'></div>
						<?php else : ?>
						<table class="light da-table" cellspacing="0">
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
						</table>
						<?php endif; ?>
					</div>
				</div>
				<div class="col w-4">
					<div class="box">
						<h1><?=lang('da_visitors')?></h1>
						<?php if(!empty($daily['lastmonth']['users_chart'])) : ?>
						<div class="da-user-chart" data-data='<?=json_encode($daily['lastmonth']['users_chart'])?>'></div>
						<?php else : ?>
						<table class="light da-table" cellspacing="0">
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
						</table>
						<?php endif; ?>
					</div>
				</div>
				<div class="col w-4">
					<div class="box">
						<h1><?=lang('da_devices')?></h1>
						<?php if(!empty($daily['lastmonth']['device_chart'])) : ?>
						<div class="da-device-chart" data-data='<?=json_encode($daily['lastmonth']['device_chart'])?>'></div>
						<?php else : ?>
						<table class="light da-table" cellspacing="0">
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
						</table>
						<?php endif; ?>
					</div>
				</div>
			</div>
			
			<div class="col-group da-col-group">
				<div class="col w-6">
					<div class="box">
						<h1><?=lang('da_top_content')?></h1>
						<table class="light da-table" cellspacing="0">
							<tr>
								<th class="da-label-col"><?=lang('da_page')?></th>
								<th class="da-count-col"><?=lang('da_views')?></th>
							</tr>
							<?php if(!empty($daily['lastmonth']['content'])) : 
								foreach($daily['lastmonth']['content'] as $row): ?>
							<tr>
								<td class="da-label-col"><?=daProcessLink($row['url'], $row['title'])?></td>
								<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['count'])?>)</td>
							</tr>
								<?php endforeach;?>
							<?php else : ?>
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
							<?php endif; ?>
						</table>
					</div>
				</div>
				<div class="col w-5">
					<div class="box">
						<h1><?=lang('da_sources')?></h1>
						<table class="light da-table" cellspacing="0">
							<tr>
								<th class="da-label-col"><?=lang('da_source')?></th>
								<th class="da-count-col"><?=lang('da_visits')?></th>
							</tr>
							<?php if(!empty($daily['lastmonth']['sources'])) : 
								foreach($daily['lastmonth']['sources'] as $row): ?>
								<tr>
									<td class="da-label-col"><?=($row['type'] == 'referral') ? daProcessLink($row['url'], $row['title']) : $row['title'] ?></td>
									<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['count'])?>)</td>
								</tr>
								<?php endforeach; ?>
							<?php else : ?>
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
							<?php endif; ?>
						</table>
					</div>
				</div>
				<div class="col w-5">
					<div class="box">
						<h1><?=lang('da_countries')?></h1>
						<table class="light da-table" cellspacing="0">
							<tr>
								<th class="da-label-col"><?=lang('da_country')?></th>
								<th class="da-count-col"><?=lang('da_visits')?></th>
							</tr>
							<?php if(!empty($daily['lastmonth']['countries'])) : 
								foreach($daily['lastmonth']['countries'] as $row): ?>
								<tr>
									<td class="da-label-col"><?=daFlagIcon($row['country'])?><?=$row['country']?></td>
									<td class="da-count-col"><strong><?=$row['percentage'];?></strong> (<?=number_format($row['count'])?>)</td>
								</tr>
								<?php endforeach; ?>
							<?php else : ?>
							<tr>
								<td colspan="2" class="da-no-data"><?=lang('da_no_data')?></td>
							</tr>
							<?php endif; ?>
						</table>
					</div>
				</div>
			</div>
		<?php endif; ?>
	
		<?php if(!empty($profile['segment'])) : ?>
			<div class="col-group">
				<div class="col w-16 da-more">
					<p><a class="btn" href="https://www.google.com/analytics/web/#report/defaultid/<?=$profile['segment']?>" target="_blank"><?=lang('da_more')?></a></p>
				</div>
			</div>
		<?php endif; ?>
	
	</div>
</div>
<?php endif; ?>