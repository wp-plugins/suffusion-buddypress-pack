<?php

/**
 * BuddyPress - Create Blog
 *
 * @package Suffusion BP Pack
 * @subpackage blogs
 */

?>

<?php get_header(); ?>

	<?php do_action('bp_before_directory_blogs_content'); ?>

<div id="main-col">
	<div id="content">
		<div <?php suffusion_bp_content_class(); ?> >

		<?php do_action('template_notices'); ?>

			<h3><?php _e('Create a Site', 'buddypress'); ?> &nbsp;<a class="button" href="<?php echo trailingslashit(bp_get_root_domain() . '/' . bp_get_blogs_root_slug()) ?>"><?php _e('Site Directory', 'buddypress'); ?></a></h3>

		<?php do_action('bp_before_create_blog_content'); ?>

		<?php if (bp_blog_signup_enabled()) : ?>

			<?php bp_show_blog_signup_form(); ?>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e('Site registration is currently disabled', 'buddypress'); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action('bp_after_create_blog_content'); ?>
		</div><!-- post -->
	</div><!-- #content -->
</div><!-- #main-col -->

	<?php do_action('bp_after_directory_blogs_content'); ?>

<?php get_footer(); ?>
