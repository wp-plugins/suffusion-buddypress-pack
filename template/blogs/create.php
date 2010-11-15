<?php get_header() ?>

	<?php do_action( 'bp_before_create_blog_content' ) ?>

	<div id="main-col">
		<div id="content">

			<div <?php suffusion_bp_content_class(); ?> >
		<?php do_action( 'template_notices' ) ?>
		<div class='bp-header dir-form'>
		<h3><?php _e( 'Create a Blog', 'buddypress' ) ?> &nbsp;<a class="button" href="<?php echo bp_get_root_domain() . '/' . BP_BLOGS_SLUG . '/' ?>"><?php _e( 'Blogs Directory', 'buddypress' ) ?></a></h3>
		</div>

		<?php do_action( 'bp_before_create_blog_content' ) ?>

		<?php if ( bp_blog_signup_enabled() ) : ?>

			<?php bp_show_blog_signup_form() ?>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'Blog registration is currently disabled', 'buddypress' ); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action( 'bp_after_create_blog_content' ) ?>

			</div><!-- .post -->
		</div><!-- #content -->
	</div><!-- #main-col -->

	<?php do_action( 'bp_after_create_blog_content' ) ?>

<?php get_footer() ?>

