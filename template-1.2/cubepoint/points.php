<?php get_header() ?>

<div id="main-col">
	<div id="content">

		<div <?php suffusion_bp_content_class(); ?>>
			<div id="item-header">
				<?php locate_template(array('members/single/member-header.php'), true) ?>
			</div>

			<div id="item-nav">
				<div class="item-list-tabs no-ajax" id="object-nav">
					<ul>
						<?php bp_get_displayed_user_nav() ?>
					</ul>
				</div>
			</div>

			<div id="item-body">

				<div class="item-list-tabs no-ajax" id="subnav">
					<ul>
						<?php bp_get_options_nav() ?>
					</ul>
				</div>
				
				<?php global $bp; cp_show_logs($bp->displayed_user->id, get_option('bp_points_logs_per_page_cp_bp'), false); ?>
				
			</div><!-- #item-body -->
		</div><!-- bp_content_class -->
	</div><!-- #content -->
</div><!-- #main-col -->

<?php get_footer() ?>