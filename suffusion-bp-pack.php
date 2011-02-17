<?php
/**
 * Plugin Name: Suffusion BuddyPress Pack
 * Plugin URI: http://www.aquoid.com/news/plugins/suffusion-buddypress-pack/
 * Description: This plugin is an add-on to the Suffusion WordPress Theme. It is based on the BuddyPress Template Pack, with the markup elements and enhancements specific to Suffusion.
 * Version: 1.01
 * Author: Sayontan Sinha
 * Author URI: http://mynethome.net/blog
 * License: GNU General Public License (GPL), v2 (or newer)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (c) 2009 - 2010 Sayontan Sinha. All rights reserved.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class Suffusion_BP_Pack {
	var $third_party_plugins;
	function Suffusion_BP_Pack() {
		if (!function_exists('bp_is_group')) { return false; }

		add_action('admin_menu', array(&$this, 'add_bp_admin'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_bp_admin_scripts'));

		//Hooks for adding "Activity Stream" to the drop-down for the front page in Settings -> Reading
		add_filter('wp_dropdown_pages', array(&$this, 'bpp_wp_pages_filter'));
		add_action('pre_update_option_page_on_front', array(&$this, 'bpp_page_on_front_update'), 10, 2);
		add_filter('page_template', array(&$this, 'bpp_page_on_front_template'));
		add_action('pre_get_posts', array(&$this, 'bpp_fix_get_posts_on_activity_front'));
		add_filter('the_posts', array(&$this, 'bpp_fix_the_posts_on_activity_front'));

		add_action('wp_ajax_bpp_move_template_files', array(&$this, 'bpp_move_template_files'));
		$this->third_party_plugins = array(
			'album' => array('name' => 'BuddyPress Album+', 'url' => 'http://wordpress.org/extend/plugins/bp-album/'),
			'bp-links-default' => array('name' => 'BuddyPress Links', 'url' => 'http://wordpress.org/extend/plugins/buddypress-links/'),
			'jet-event-system' => array('name' => 'Jet Event System for BuddyPress', 'url' => 'http://wordpress.org/extend/plugins/jet-event-system-for-buddypress/', 'include-root' => false),
		);
	}

	function add_bp_admin() {
		add_theme_page('Suffusion BuddyPress Pack', 'Suffusion BP Pack', 'edit_theme_options', 'suffusion-bp-pack', array(&$this, 'render_bp_options'));
	}

	function add_bp_admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_style('suffusion-bpp-admin', plugins_url('include/css/admin.css', __FILE__), array(), '1.00');
	}

	/**
	 * Prints the list of folders in the plugin options page. The folders are printed with check-boxes to select what the user wants to copy.
	 *
	 * @param bool $other_plugins
	 * @return void
	 */
	function bpp_recurse_print_folders($other_plugins = false) {
		$file_path = plugin_dir_path(__FILE__)."/template";
		$file_path = opendir($file_path);
		echo "<ul>\n";
		while (false !== ($folder = readdir($file_path))) {
			if (!($folder == "." || $folder == "..")) {
				if (!$other_plugins && !array_key_exists($folder, $this->third_party_plugins)) {
					echo "<li><input type='checkbox' checked name='bpp_folder_$folder' value='true'/>".$folder."</li>\n";
				}
				else if ($other_plugins && array_key_exists($folder, $this->third_party_plugins)) {
					echo "<li><input type='checkbox' name='bpp_folder_$folder'/>".$folder." &ndash; <a href='".$this->third_party_plugins[$folder]['url']."'>".$this->third_party_plugins[$folder]['name']."</a></li>\n";
				}
			}
		}
		echo "</ul>\n";
	}

	function bpp_move_template_files() {
		$source_folder = plugin_dir_path(__FILE__)."/template/";
		$target_folder = trailingslashit(STYLESHEETPATH);
		foreach ($_POST as $name => $value) {
			if (strlen($name) > 11 && substr($name, 0, 11) == 'bpp_folder_') {
				$to_copy = substr($name, 11);
				if (isset($this->third_party_plugins[$to_copy]) && isset($this->third_party_plugins[$to_copy]['include-root'])) {
					$include_root = $this->third_party_plugins[$to_copy]['include-root'];
				}
				else {
					$include_root = true;
				}

				if ($include_root) {
					$this->bpp_recurse_copy($source_folder.$to_copy, $target_folder.$to_copy);
				}
				else {
					$this->bpp_recurse_copy($source_folder.$to_copy, $target_folder);
				}
			}
		}
	}

	/**
	 * Recursively copies plugin-specific folders from the template directory to the child theme's folder.
	 *
	 * @param  $source
	 * @param  $target
	 * @return bool
	 */
	function bpp_recurse_copy($source, $target) {
		$dir = @opendir($source);

		if (!file_exists($target)) {
			if (!@mkdir($target)) {
				return false;
			}
		}

		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($source.'/'.$file)) {
					$this->bpp_recurse_copy($source.'/'.$file, $target.'/'.$file);
				}
				else {
					if (!@copy($source.'/'.$file, $target.'/'.$file)) {
						return false;
					}
				}
			}
		}

		@closedir($dir);
		return true;
	}
	/**
	 * Filter the dropdown for selecting the page to show on front to include "Activity Stream".
	 * This method is from the BP default theme.
	 *
	 * @param  $page_html
	 * @return mixed
	 */
	function bpp_wp_pages_filter($page_html) {
		if ('page_on_front' != substr($page_html, 14, 13))
			return $page_html;

		$selected = false;
		$page_html = str_replace('</select>', '', $page_html);

		if ($this->bpp_page_on_front() == 'activity')
			$selected = ' selected="selected"';

		$page_html .= '<option class="level-0" value="activity"'.$selected.'>'.__('Activity Stream', 'buddypress').'</option></select>';
		return $page_html;
	}

	/**
	 * Hijack the saving of page on front setting to save the activity stream setting.
	 * This method is from the BP default theme.
	 *
	 * @param  $oldvalue
	 * @param  $newvalue
	 * @return bool|string
	 */
	function bpp_page_on_front_update($oldvalue, $newvalue) {
		if (!is_admin() || !is_site_admin())
			return false;

		if ('activity' == $_POST['page_on_front'])
			return 'activity';
		else
			return $oldvalue;
	}

	/**
	 * Load the activity stream template if settings allow.
	 * This method is from the BP default theme.
	 *
	 * @param  $template
	 * @return string
	 */
	function bpp_page_on_front_template($template) {
		global $wp_query;

		if (empty($wp_query->post->ID))
			return locate_template(array('activity/index.php'), false);
		else
			return $template;
	}

	/**
	 * Force the page ID as a string to stop the get_posts query from kicking up a fuss.
	 * This method is from the BP default theme.
	 *
	 * @return void
	 */
	function bpp_fix_get_posts_on_activity_front() {
		global $wp_query;

		if (!empty($wp_query->query_vars['page_id']) && 'activity' == $wp_query->query_vars['page_id'])
			$wp_query->query_vars['page_id'] = '"activity"';
	}

	/**
	 * WP 3.0 requires there to be a non-null post in the posts array.
	 * This method is from the BP default theme.
	 *
	 * @param array $posts Posts as retrieved by WP_Query
	 * @global WP_Query $wp_query WordPress query object
	 * @return array
	 */
	function bpp_fix_the_posts_on_activity_front($posts) {
		global $wp_query;

		// NOTE: the double quotes around '"activity"' are thanks to our previous function bp_dtheme_fix_get_posts_on_activity_front()
		if (empty($posts) && !empty($wp_query->query_vars['page_id']) && '"activity"' == $wp_query->query_vars['page_id'])
			$posts = array((object) array('ID' => 'activity'));

		return $posts;
	}

	/**
	 * Return the ID of a page set as the home page.
	 * This method is from the BP default theme.
	 *
	 * @return bool|mixed|void
	 */
	function bpp_page_on_front() {
		if ('page' != get_option('show_on_front'))
			return false;

		return apply_filters('suffusion_bpp_page_on_front', get_option('page_on_front'));
	}

	/**
	 * Checks if you are using a child theme of Suffusion or not.
	 *
	 * @return void
	 */
	function bpp_check_theme() {
		$theme = get_current_theme(); // Need this because a child theme might be getting used.
		$theme_data = get_theme($theme);
		if ($theme_data['Template'] != 'suffusion') {
?>
		<div class="error">
			<p>
				You are not using Suffusion or a child theme. The plugin may still be used, but you might not get the desired results with it.
			</p>
		</div>
<?php
		}
		else if ($theme_data['Template'] == 'suffusion' && $theme_data['Template'] == $theme_data['Stylesheet']) {
?>
		<div class="error">
			<p>
				You are using Suffusion, but not a child theme. Note that any changes made using this plugin will get wiped out the next time you
				update Suffusion. To avoid this, <a href="http://www.aquoid.com/news/plugins/suffusion-buddypress-pack/">create a child theme of Suffusion</a> and use that.
			</p>
		</div>
<?php
		}
		else if ($theme_data['Template'] == 'suffusion' && $theme_data['Template'] != $theme_data['Stylesheet'] &&
				isset($theme_data['Tags']) && !in_array('buddypress', $theme_data['Tags'])) {
?>
		<div class="updated">
			<p>
				You might want to add the a line that says <code>Tags: buddypress</code> to the header comments of your child theme.
				Otherwise you will keep getting a message that says your theme is not BuddyPress capable.
			</p>
		</div>
<?php
		}
	}

	function render_bp_options() {?>
			<script type="text/javascript">
				/* <![CDATA[ */
				$j = jQuery.noConflict();
				$j(document).ready(function() {
					$j('div.suf-loader').hide();
					$j('#suf_bpp_return_message').hide();

					$j('input.button').live("click", function() {
						var name = this.name;
						if (name == 'copy_template') {
							if (confirm("This will overwrite any pre-existing BP-related sub-folders in your current theme. Are you sure you want to proceed?")) {
								var bpp_build_form = $j('form#' + name + '_form');
								var form_values = bpp_build_form.serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');

								$j('div.suf-loader').show();
								$j.post(ajaxurl, 'action=bpp_move_template_files&'+form_values, function(data) {
									$j('#suf_bpp_return_message').html("The template files have been updated.").show().fadeOut(20000);
									$j('div.suf-loader').hide();
								});
							}
						}
					});
				});
				/* ]]> */
			</script>

<div class="suf-loader"><img src='<?php echo plugins_url('include/images/ajax-loader-large.gif', __FILE__); ?>' alt='Processing'></div>
<div class="suf-bpp-wrapper">
	<h1>Welcome to the Suffusion BuddyPress Pack</h1>
	<?php $this->bpp_check_theme(); ?>
	<div id="suf_bpp_return_message" class="updated"></div>
	<p>
		This plugin will help you if you are using BuddyPress and would like to take advantage of all the options offered
		by the <a href="http://www.aquoid.com/news/themes/suffusion">Suffusion</a> WordPress Theme. The plugin makes heavy use
		of design aspects from:
	</p>
	<ol>
		<li><a href="http://wordpress.org/extend/plugins/bp-template-pack/">The BuddyPress template pack</a> &ndash; All the
			template files were originally created using the BP template pack, then modified to fit Suffusion's HTML markup.</li>
		<li>The BuddyPress default theme &ndash; Various filters and actions have been borrowed from the BP default theme,
			because this is not a BP child theme.</li>
	</ol>

	<form method="post" name="copy_template_form" id="copy_template_form">
		<fieldset>
			<legend>(Re)Build BuddyPress Files</legend>
			<p>
				If you are starting out afresh with Suffusion on BuddyPress, this is the first thing you should do.
				Since the default BuddyPress HTML markup is different from Suffusion's markup, this step will help (re)create your templates.
				You should be using a child theme of Suffusion before you start using this theme. Otherwise if you update suffusion from the
				WP themes repository you will lose all the BP-specific files.
			</p>
			<p>
				Please note the following:
			</p>

			<ol>
				<li>
					All your template files will be written to <strong><?php echo STYLESHEETPATH; ?></strong>.
				</li>
				<li>
					 The files in the following <strong>standard</strong> folders will be moved. You can deselect folders that you don't want to move:
					<?php $this->bpp_recurse_print_folders(); ?>
				</li>
				<li>
					In addition the selected folders from the following can be moved for specific plugins.
					<?php $this->bpp_recurse_print_folders(true); ?>
				</li>
			</ol>

			<input name="copy_template" type="button" value="(Re)Build BP Files" class="button"/>
		</fieldset>
	</form>

	<fieldset>
		<legend>Help and Support</legend>
		<h2>Other Plugins</h2>
		<p>
			If you wish to get the support added for other plugins, please use the <a href="http://www.aquoid.com/forum">Support Forum</a>.
			If it is possible to extend support for the plugin I will do so. Alternatively you can contact the plugin's support
			to see if they allow their templates to be overridden by themes. If so, you can create the skeleton for the plugin yourself in a few steps.
		</p>
		<ol>
			<li>
				Copy over the template files to your Suffusion child theme. E.g. For the BuddyPress Album+ plugin copy over
				the folder called <code>album</code> under <code>wp-content/plugins/bp-album/includes/templates</code>
				to your child theme, so that you have the folder <code><?php echo STYLESHEETPATH."/ablum"; ?></code>.
			</li>
			<li>
				Open the main file. Typically it is <code>index.php</code> or <code>single.php</code> or <code>page.php</code>.
				Default BuddyPress markup in that file would look like this:
				<pre><code style="display: block; width: 40%; padding-left: 15px;">
[HEADER]
&lt;div id="container"&gt;
	&lt;div id="content"&gt;
		[PAGE CONTENT]
	&lt;/div&gt;

	&lt;div id="sidebar"&gt;
		[SIDEBAR CONTENT]
	&lt;/div&gt;
&lt;/div&gt;
[FOOTER]
				</code></pre>
			</li>
			<li>
				This will have to be changed appropriately for Suffusion:
				<pre><code style="display: block; width: 40%; padding-left: 15px;">
[HEADER]
&lt;div id="main-col"&gt;
	&lt;div id="content"&gt;
		&lt;div &lt;?php suffusion_bp_content_class(); ?&gt; &gt;
			[PAGE CONTENT]
		&lt;/div&gt;
	&lt;/div&gt;
&lt;/div&gt;
[FOOTER]
				</code></pre>
				Note that you shouldn't include the sidebar code &ndash; Suffusion's functions take care of that.
			</li>
		</ol>

		<h2>Navigation Links</h2>
		<p>
			You may want to add new navigation menu items or links to your site to link to BuddyPress directory pages. The default set of links are:
		</p>
		<ol>
			<li>Activity: <a href="<?php echo get_option('home').'/'.BP_ACTIVITY_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_ACTIVITY_SLUG.'/'; ?></a></li>
			<li>Members: <a href="<?php echo get_option('home').'/'.BP_MEMBERS_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_MEMBERS_SLUG.'/'; ?></a></li>
			<li>Groups: <a href="<?php echo get_option('home').'/'.BP_GROUPS_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_GROUPS_SLUG.'/'; ?></a></li>
			<li>Forums: <a href="<?php echo get_option('home').'/'.BP_FORUMS_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_FORUMS_SLUG.'/'; ?></a></li>
			<li>Register: <a href="<?php echo get_option('home').'/'.BP_REGISTER_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_REGISTER_SLUG.'/'; ?></a> (registration must be enabled)</li>
		<?php if (bp_core_is_multisite()) { ?>
			<li>Blogs: <a href="<?php echo get_option('home').'/'.BP_BLOGS_SLUG.'/'; ?>"><?php echo get_option('home').'/'.BP_BLOGS_SLUG.'/'; ?></a></li>
		<?php } ?>
		</ol>
	</fieldset>
</div>
<?php
	}
}

add_action('init', 'init_suffusion_bp_pack');
function init_suffusion_bp_pack() {
	global $Suffusion_BP_Pack;
	$Suffusion_BP_Pack = new Suffusion_BP_Pack();
}
?>