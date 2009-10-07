<?php
/**
 * Plugin Name: Twitter Widget Pro
 * Plugin URI: http://xavisys.com/wordpress-twitter-widget/
 * Description: A widget that properly handles twitter feeds, including @username, #hashtag, and link parsing.  It can even display profile images for the users.  Requires PHP5.
 * Version: 2.0.3
 * Author: Aaron D. Campbell
 * Author URI: http://xavisys.com/
 * Text Domain: twitter-widget-pro
 */

/*
	Copyright 2006-current  Aaron D. Campbell  (email : wp_plugins@xavisys.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class wpTwitterWidgetException extends Exception {}

/**
 * WP_Widget_Twitter_Pro is the class that handles the main widget.
 */
class WP_Widget_Twitter_Pro extends WP_Widget {
	public function WP_Widget_Twitter_Pro () {
		$widget_ops = array(
			'classname' => 'widget_twitter',
			'description' => __( 'Follow a Twitter Feed', 'twitter-widget-pro' )
		);
		$control_ops = array(
			'width' => 400,
			'height' => 350,
			'id_base' => 'twitter'
		);
		$name = __( 'Twitter Widget Pro', 'twitter-widget-pro' );

		$this->WP_Widget('twitter', $name, $widget_ops, $control_ops);
	}

	private function _getInstanceSettings ( $instance ) {
		$defaultArgs = array(	'title'				=> '',
								'errmsg'			=> '',
								'fetchTimeOut'		=> '2',
								'username'			=> '',
								'hiderss'			=> false,
								'hidereplies'		=> false,
								'avatar'			=> false,
								'showXavisysLink'	=> false,
								'items'				=> 10,
								'showts'			=> 60 * 60 * 24,
		);

		return wp_parse_args( $instance, $defaultArgs );
	}

	public function form( $instance ) {
		$instance = $this->_getInstanceSettings( $instance );
?>
			<p>
				<label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Twitter username:', 'twitter-widget-pro'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php esc_attr_e($instance['username']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Give the feed a title (optional):', 'twitter-widget-pro'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php esc_attr_e($instance['title']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('items'); ?>"><?php _e('How many items would you like to display?', 'twitter-widget-pro'); ?></label>
				<select id="<?php echo $this->get_field_id('items'); ?>" name="<?php echo $this->get_field_name('items'); ?>">
					<?php
						for ( $i = 1; $i <= 20; ++$i ) {
							echo "<option value='$i' ". selected($instance['items'], $i, false). ">$i</option>";
						}
					?>
				</select>
			</p>
			<p>
				<input class="checkbox" type="checkbox" value="true" id="<?php echo $this->get_field_id('hidereplies'); ?>" name="<?php echo $this->get_field_name('hidereplies'); ?>"<?php checked($instance['hidereplies'], 'true'); ?> />
				<label for="<?php echo $this->get_field_id('hidereplies'); ?>"><?php _e('Hide @replies', 'twitter-widget-pro'); ?></label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('errmsg'); ?>"><?php _e('What to display when Twitter is down (optional):', 'twitter-widget-pro'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('errmsg'); ?>" name="<?php echo $this->get_field_name('errmsg'); ?>" type="text" value="<?php esc_attr_e($instance['errmsg']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('fetchTimeOut'); ?>"><?php _e('Number of seconds to wait for a response from Twitter (default 2):', 'twitter-widget-pro'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('fetchTimeOut'); ?>" name="<?php echo $this->get_field_name('fetchTimeOut'); ?>" type="text" value="<?php esc_attr_e($instance['fetchTimeOut']); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('showts'); ?>"><?php _e('Show date/time of Tweet (rather than 2 ____ ago):', 'twitter-widget-pro'); ?></label>
				<select id="<?php echo $this->get_field_id('showts'); ?>" name="<?php echo $this->get_field_name('showts'); ?>">
					<option value="0" <?php selected($instance['showts'], '0'); ?>><?php _e('Always', 'twitter-widget-pro');?></option>
					<option value="3600" <?php selected($instance['showts'], '3600'); ?>><?php _e('If over an hour old', 'twitter-widget-pro');?></option>
					<option value="86400" <?php selected($instance['showts'], '86400'); ?>><?php _e('If over a day old', 'twitter-widget-pro');?></option>
					<option value="604800" <?php selected($instance['showts'], '604800'); ?>><?php _e('If over a week old', 'twitter-widget-pro');?></option>
					<option value="2592000" <?php selected($instance['showts'], '2592000'); ?>><?php _e('If over a month old', 'twitter-widget-pro');?></option>
					<option value="31536000" <?php selected($instance['showts'], '31536000'); ?>><?php _e('If over a year old', 'twitter-widget-pro');?></option>
					<option value="-1" <?php selected($instance['showts'], '-1'); ?>><?php _e('Never', 'twitter-widget-pro');?></option>
				</select>
			</p>
			<p>
				<input class="checkbox" type="checkbox" value="true" id="<?php echo $this->get_field_id('hiderss'); ?>" name="<?php echo $this->get_field_name('hiderss'); ?>"<?php checked($instance['hiderss'], 'true'); ?> />
				<label for="<?php echo $this->get_field_id('hiderss'); ?>"><?php _e('Hide RSS Icon and Link', 'twitter-widget-pro'); ?></label>
			</p>
			<p>
				<input class="checkbox" type="checkbox" value="true" id="<?php echo $this->get_field_id('avatar'); ?>" name="<?php echo $this->get_field_name('avatar'); ?>"<?php checked($instance['avatar'], 'true'); ?> />
				<label for="<?php echo $this->get_field_id('avatar'); ?>"><?php _e('Show Profile Image', 'twitter-widget-pro'); ?></label>
			</p>
			<p>
				<input class="checkbox" type="checkbox" value="true" id="<?php echo $this->get_field_id('showXavisysLink'); ?>" name="<?php echo $this->get_field_name('showXavisysLink'); ?>"<?php checked($instance['showXavisysLink'], 'true'); ?> />
				<label for="<?php echo $this->get_field_id('showXavisysLink'); ?>"><?php _e('Show Link to Twitter Widget Pro', 'twitter-widget-pro'); ?></label>
			</p>
<?php
		return;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->_getInstanceSettings( $new_instance );

		// Clean up the free-form areas
		$instance['title'] = stripslashes($new_instance['title']);
		$instance['errmsg'] = stripslashes($new_instance['errmsg']);

		// If the current user isn't allowed to use unfiltered HTML, filter it
		if ( !current_user_can('unfiltered_html') ) {
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['errmsg'] = strip_tags($new_instance['errmsg']);
		}

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('widget_twitter_widget_pro', 'widget');
	}

	public function widget( $args, $instance ) {
		$instance = $this->_getInstanceSettings( $instance );

		// Validate our options
		$instance['items'] = (int) $instance['items'];
		if ( $instance['items'] < 1 || 20 < $instance['items'] ) {
			$instance['items'] = 10;
		}
		if (!isset($instance['showts'])) {
			$instance['showts'] = 86400;
		}

		try {
			$tweets = $this->_getTweets($instance);
		} catch (wpTwitterWidgetException $e) {
			$tweets = $e;
		}

		echo $args['before_widget'] . '<div>';

		// If "hide rss" hasn't been checked, show the linked icon
		if ( $instance['hiderss'] != 'true' ) {
			if ( file_exists(dirname(__FILE__) . '/rss.png') ) {
				$icon = str_replace(ABSPATH, get_option('siteurl').'/', dirname(__FILE__)) . '/rss.png';
			} else {
				$icon = get_option('siteurl').'/wp-includes/images/rss.png';
			}
			$feedUrl = $this->_getFeedUrl($instance, 'rss', false);
			$args['before_title'] .= "<a class='twitterwidget' href='{$feedUrl}' title='" . attribute_escape(__('Syndicate this content', 'twitter-widget-pro')) ."'><img style='background:orange;color:white;border:none;' width='14' height='14' src='{$icon}' alt='RSS' /></a> ";
		}
		$twitterLink = 'http://twitter.com/' . $instance['username'];
		$args['before_title'] .= "<a class='twitterwidget' href='{$twitterLink}' title='" . attribute_escape("Twitter: {$instance['username']}") . "'>";
		$args['after_title'] = '</a>' . $args['after_title'];
		if (empty($instance['title'])) {
			$instance['title'] = "Twitter: {$instance['username']}";
		}
		echo $args['before_title'] . $instance['title'] . $args['after_title'];
		if (!is_a($tweets, 'wpTwitterWidgetException') && !empty($tweets[0]) && $instance['avatar'] == 'true') {
			echo '<div class="twitter-avatar">';
			echo $this->_getProfileImage($tweets[0]->user);
			echo '</div>';
		}
		echo '<ul>';
		if (is_a($tweets, 'wpTwitterWidgetException')) {
			echo '<li class="wpTwitterWidgetError">' . $tweets->getMessage() . '</li>';
		} else if (count($tweets) == 0) {
			echo '<li class="wpTwitterWidgetEmpty">' . __('No Tweets Available', 'twitter-widget-pro') . '</li>';
		} else {
			$count = 0;
			foreach ($tweets as $tweet) {
				if ( $instance['hidereplies'] != 'true' || empty($tweet->in_reply_to_user_id)) {
					// Set our "ago" string which converts the date to "# ___(s) ago"
					$tweet->ago = $this->_timeSince(strtotime($tweet->created_at), $instance['showts']);
?>
				<li>
					<span class="entry-content"><?php echo apply_filters( 'widget_twitter_content', $tweet->text ); ?></span>
					<span class="entry-meta">
						<span class="time-meta">
							<a href="http://twitter.com/<?php echo $tweet->user->screen_name; ?>/statuses/<?php echo $tweet->id; ?>"><?php echo $tweet->ago; ?></a>
						</span>
						<span class="from-meta">
							<?php echo sprintf(__('from %s', 'twitter-widget-pro'), str_replace('&', '&amp;', $tweet->source)); ?>
						</span>
						<?php
						if (!empty($tweet->in_reply_to_screen_name)) {
							$rtLinkText = sprintf( __('in reply to %s', 'twitter-widget-pro'), $tweet->in_reply_to_screen_name );
							echo <<<replyTo
							<span class="in-reply-to-meta">
								<a href="http://twitter.com/{$tweet->in_reply_to_screen_name}/statuses/{$tweet->in_reply_to_status_id}" class="reply-to">{$rtLinkText}</a>
							</span>
replyTo;
						} ?>

					</span>
				</li>
<?php
					if (++$count >= $instance['items']) {
						break;
					}
				}
			}
		}

		if ( $instance['showXavisysLink'] == 'true' ) {
?>
				<li class="xavisys-link">
					<span class="xavisys-link-text">
						<?php echo sprintf(__('Powered by <a href="%s" title="Get Twitter Widget for your WordPress site">WordPress Twitter Widget Pro</a>', 'twitter-widget-pro'), 'http://xavisys.com/2008/04/wordpress-twitter-widget/' );?>
					</span>
				</li>
<?php
		}
		echo '</ul></div>' . $args['after_widget'];
	}

	/**
	 * Gets tweets, from cache if possible
	 *
	 * @param array $widgetOptions - options needed to get feeds
	 * @return array - Array of objects
	 */
	private function _getTweets($widgetOptions) {
		$feedHash = sha1($this->_getFeedUrl($widgetOptions));
		$tweets = get_option("wptw-{$feedHash}");
		$cacheAge = get_option("wptw-{$feedHash}-time");
		//If we don't have cache or it's more than 5 minutes old
		if ( empty($tweets) || (time() - $cacheAge) > 300 ) {
			try {
				$tweets = $this->_parseFeed($widgetOptions);
				update_option("wptw-{$feedHash}", $tweets);
				update_option("wptw-{$feedHash}-time", time());
			} catch (wpTwitterWidgetException $e) {
				throw $e;
			}
		}
		return $tweets;
	}

	/**
	 * Pulls the JSON feed from Twitter and returns an array of objects
	 *
	 * @param array $widgetOptions - settings needed to get feed url, etc
	 * @return array
	 */
	private function _parseFeed($widgetOptions) {
		$feedUrl = $this->_getFeedUrl($widgetOptions);
		$resp = wp_remote_request($feedUrl, array('timeout' => $widgetOptions['fetchTimeOut']));

		if ( !is_wp_error($resp) && $resp['response']['code'] >= 200 && $resp['response']['code'] < 300 ) {
			if (function_exists('json_decode')) {
				$decodedResponse = json_decode( $resp['body'] );
			} else {
				global $wp_json;

				if ( !is_a($wp_json, 'Services_JSON') ) {
					require_once( 'class-json.php' );
					$wp_json = new Services_JSON();
				}

				$decodedResponse =  $wp_json->decode( $resp['body'] );
			}
			if ( empty($decodedResponse) ) {
				if (empty($widgetOptions['errmsg'])) {
					$widgetOptions['errmsg'] = __('Invalid Twitter Response.', 'twitter-widget-pro');
				}
				throw new wpTwitterWidgetException($widgetOptions['errmsg']);
			} elseif( !empty($decodedResponse->error) ) {
				if (empty($widgetOptions['errmsg'])) {
					$widgetOptions['errmsg'] = $decodedResponse->error;
				}
				throw new wpTwitterWidgetException($widgetOptions['errmsg']);
			} else {
				return $decodedResponse;
			}
		} else {
			// Failed to fetch url;
			if (empty($widgetOptions['errmsg'])) {
				$widgetOptions['errmsg'] = __('Could not connect to Twitter', 'twitter-widget-pro');
			}
			throw new wpTwitterWidgetException($widgetOptions['errmsg']);
		}
	}

	/**
	 * Gets the URL for the desired feed.
	 *
	 * @param array $widgetOptions - settings needed such as username, feet type, etc
	 * @param string[optional] $type - 'rss' or 'json'
	 * @param bool[optional] $count - If true, it adds the count parameter to the URL
	 * @return string - Twitter feed URL
	 */
	private function _getFeedUrl($widgetOptions, $type = 'json', $count = true) {
		if (!in_array($type, array('rss', 'json'))) {
			$type = 'json';
		}
		if ( $count ) {
			$num = ($widgetOptions['hidereplies'])? 100:$widgetOptions['items'];
			$count = sprintf('?count=%u', $num);
		} else {
			$count = '';
		}
		return sprintf('http://twitter.com/statuses/user_timeline/%1$s.%2$s%3$s', $widgetOptions['username'], $type, $count);
	}

	/**
	 * Twitter displays all tweets that are less than 24 with something like
	 * "about 4 hours ago" and ones older than 24 hours with a time and date.
	 * This function allows us to simulate that functionality, but lets us
	 * choose where the dividing line is.
	 *
	 * @param int $startTimestamp - The timestamp used to calculate time passed
	 * @param int $max - Max number of seconds to conver to "ago" messages.  0 for all, -1 for none
	 * @return string
	 */
	private function _timeSince($startTimestamp, $max) {
		// array of time period chunks
		$chunks = array(
			'year'		=> 60 * 60 * 24 * 365,	// 31,536,000 seconds
			'month'		=> 60 * 60 * 24 * 30,	// 2,592,000 seconds
			'week'		=> 60 * 60 * 24 * 7,	// 604,800 seconds
			'day'		=> 60 * 60 * 24,		// 86,400 seconds
			'hour'		=> 60 * 60,				// 3600 seconds
			'minute'	=> 60,					// 60 seconds
			'second'	=> 1					// 1 second
		);

		$since = time() - $startTimestamp;

		if ($max != '-1' && $since >= $max) {
			return date_i18n('h:i:s A F d, Y', $startTimestamp);
		}

		foreach ( $chunks as $key => $seconds ) {
			// finding the biggest chunk (if the chunk fits, break)
			if (($count = floor($since / $seconds)) != 0) {
				break;
			}
		}

		$messages = array(
			'year'		=> _n('about %s year ago', 'about %s years ago', $count, 'twitter-widget-pro'),
			'month'		=> _n('about %s month ago', 'about %s months ago', $count, 'twitter-widget-pro'),
			'week'		=> _n('about %s week ago', 'about %s weeks ago', $count, 'twitter-widget-pro'),
			'day'		=> _n('about %s day ago', 'about %s days ago', $count, 'twitter-widget-pro'),
			'hour'		=> _n('about %s hour ago', 'about %s hours ago', $count, 'twitter-widget-pro'),
			'minute'	=> _n('about %s minute ago', 'about %s minutes ago', $count, 'twitter-widget-pro'),
			'second'	=> _n('about %s second ago', 'about %s seconds ago', $count, 'twitter-widget-pro'),
		);

		return sprintf($messages[$key], $count);
	}

	/**
	 * Returns the Twitter user's profile image, linked to that user's profile
	 *
	 * @param object $user - Twitter User
	 * @return string - Linked image (XHTML)
	 */
	private function _getProfileImage($user) {
		return <<<profileImage
	<a title="{$user->name}" href="http://twitter.com/{$user->screen_name}"><img alt="{$user->name}" src="{$user->profile_image_url}" /></a>
profileImage;
	}
}


/**
 * wpTwitterWidget is the class that handles everything outside the widget. This
 * includes filters that modify tweet content for things like linked usernames.
 * It also helps us avoid name collisions.
 */
class wpTwitterWidget
{
	/**
	 * @var array Plugin settings
	 */
	private $_settings;

	/**
	 * Plugin file to test against on plugins page
	 *
	 * @var string
	 */
	private $_pluginBasename;

	/**
	 * Repository base url
	 *
	 * @since 1.4.4
	 * @var string
	 */
	private $_reposUrl = 'http://plugins.svn.wordpress.org/';

	public function __construct() {
		/**
		 * Add filters and actions
		 */
		add_action( 'admin_menu', array($this,'admin_menu') );
		add_filter( 'init', array( $this, 'init_locale') );
		add_filter( 'admin_init', array( $this, 'registerSettings') );
		add_filter( 'admin_init', array( $this, 'sendSysInfo') );
		add_action( 'widgets_init', array($this, 'register') );
		add_filter( 'widget_twitter_content', array($this, 'linkTwitterUsers') );
		add_filter( 'widget_twitter_content', array($this, 'linkUrls') );
		add_filter( 'widget_twitter_content', array($this, 'linkHashtags') );
		add_filter( 'widget_twitter_content', 'convert_chars' );
		add_filter( 'plugin_action_links', array($this, 'addSettingLink'), 10, 2 );
		add_action ( 'in_plugin_update_message-'.plugin_basename ( __FILE__ ) , array ( $this , '_changelog' ), null, 2 );
	}

	public function registerSettings() {
		register_setting( 'twitter_widget_pro_options', 'twitter_widget_pro' );
	}

	public function admin_menu() {
		add_options_page(__('Twitter Widget Pro', 'twitter-widget-pro'), __('Twitter Widget Pro', 'twitter-widget-pro'), 'manage_options', 'TwitterWidgetPro', array($this, 'options'));
	}

	public function init_locale() {
		$lang_dir = basename(dirname(__FILE__)) . '/languages';
		load_plugin_textdomain('twitter-widget-pro', 'wp-content/plugins/' . $lang_dir, $lang_dir);
	}

	/**
	 * This is used to display the options page for this plugin
	 */
	public function options() {
		//Get our options
		$this->_getSettings();
?>
		<div class="wrap">
			<h2><?php _e('Twitter Widget Pro Options', 'twitter-widget-pro') ?></h2>
			<form action="options.php" method="post" id="wp_twitter_widget_pro">
				<?php settings_fields( 'twitter_widget_pro_options' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<a title="<?php _e('Click for Help!', 'twitter-widget-pro'); ?>" href="#" onclick="jQuery('#twp_user_agreed_to_send_system_information_help').toggle(); return false;"><?php _e('System Information:', 'twitter-widget-pro') ?></a>
						</th>
						<td>
							<input type="hidden" name="twitter_widget_pro[user_agreed_to_send_system_information]" value="false" />
							<label for="twp_user_agreed_to_send_system_information"><input type="checkbox" name="twitter_widget_pro[user_agreed_to_send_system_information]" value="true" id="twp_user_agreed_to_send_system_information"<?php checked('true', $this->_settings['user_agreed_to_send_system_information']); ?> /> <?php _e('I agree to send anonymous system information', 'twitter-widget-pro'); ?></label><br />
							<small id="twp_user_agreed_to_send_system_information_help" style="display:none;">
								<?php _e('You can help by sending anonymous system information that will help Xavisys make better decisions about new features.', 'twitter-widget-pro'); ?><br />
								<?php _e('The information will be sent anonymously, but a unique identifier will be sent to prevent duplicate entries from the same installation.', 'twitter-widget-pro'); ?>
							</small>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;', 'twitter-widget-pro'); ?>" />
				</p>
			</form>
		</div>
<?php
	}

	public function _changelog ($pluginData, $newPluginData) {
		$url = "{$this->_reposUrl}/{$newPluginData->slug}/tags/{$newPluginData->new_version}/upgrade.html";
		$response = wp_remote_get ( $url );
		$code = (int) wp_remote_retrieve_response_code ( $response );
		if ( $code == 200 ) {
			echo wp_remote_retrieve_body ( $response );
		}
	}

	/**
	 * Returns the user's screen name as a link inside strong tags.
	 *
	 * @param object $user - Twitter user
	 * @return string - Username as link (XHTML)
	 */
	private function _getUserName($user) {
		return <<<profileImage
	<strong>
		<a title="{$user->name}" href="http://twitter.com/{$user->screen_name}">{$user->screen_name}</a>
	</strong>
profileImage;
	}

	/**
	 * if user agrees to send system information and the last sent info is
	 * outdated then send the stats
	 */
	public function sendSysInfo() {
		$this->_getSettings();
		if ($this->_settings['user_agreed_to_send_system_information'] == 'true') {
			$lastSent = get_option('twp-sysinfo');
			$sysinfo = $this->_get_sysinfo();
			if (serialize($lastSent) != serialize($sysinfo)) {
				$params = array(
					'method'	=> 'POST',
					'blocking'	=> false,
					'body'		=> $sysinfo,
				);
				$resp = wp_remote_request( 'http://xavisys.com/plugin-info.php', $params );
				update_option( 'twp-sysinfo', $sysinfo );
			}
		}
	}

	private function _get_sysinfo() {
		global $wpdb;
		$pluginData = get_plugin_data(__FILE__);
		$s = array();
		$s['plugin'] = $pluginData['Name'];
		$s['id'] = $this->_get_id($pluginData['Name']);
		$s['version'] = $pluginData['Version'];

		$s['php_version'] = phpversion();
		$s['mysql_version'] = @mysql_get_server_info($wpdb->dbh);
		$s['server_software'] = $_SERVER["SERVER_SOFTWARE"];
		$s['memory_limit'] = ini_get('memory_limit');

		return $s;
	}

	private function _get_id($name) {
		$this->_getSettings();
		if ( empty($this->_settings['sysinfo-id']) ) {
			$this->_settings['sysinfo-id'] = sha1( get_bloginfo('url') . $name );
			$this->_updateSettings();
		}
		return $this->_settings['sysinfo-id'];
	}

	private function _updateSettings() {
		update_option('twitter_widget_pro', $this->_settings);
	}
	private function _getSettings() {
		$defaults = array(
		);
		$this->_settings = get_option('twitter_widget_pro');
		$this->_settings = wp_parse_args($this->_settings, $defaults);
	}

	public function addSettingLink( $links, $file ){
		if ( empty($this->_pluginBasename) ) {
			$this->_pluginBasename = plugin_basename(__FILE__);
		}

		if ( $file == $this->_pluginBasename ) {
			// Add settings link to our plugin
			$link = '<a href="options-general.php?page=TwitterWidgetPro">' . __('Settings', 'twitter-widget-pro') . '</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	/**
	 * Replace @username with a link to that twitter user
	 *
	 * @param string $text - Tweet text
	 * @return string - Tweet text with @replies linked
	 */
	public function linkTwitterUsers($text) {
		$text = preg_replace('/(^|\s)@(\w*)/i', '$1<a href="http://twitter.com/$2" class="twitter-user">@$2</a>', $text);
		return $text;
	}

	/**
	 * Replace #hashtag with a link to search.twitter.com for that hashtag
	 *
	 * @param string $text - Tweet text
	 * @return string - Tweet text with #hashtags linked
	 */
	public function linkHashtags($text) {
		$text = preg_replace_callback('/(^|\s)(#\w*)/i', array($this, '_hashtagLink'), $text);
		return $text;
	}

	/**
	 * Replace #hashtag with a link to search.twitter.com for that hashtag
	 *
	 * @param array $matches - Tweet text
	 * @return string - Tweet text with #hashtags linked
	 */
	private function _hashtagLink($matches) {
		return "{$matches[1]}<a href='http://search.twitter.com/search?q="
				. urlencode($matches[2])
				. "' class='twitter-hashtag'>{$matches[2]}</a>";
	}

	/**
	 * Turn URLs into links
	 *
	 * @param string $text - Tweet text
	 * @return string - Tweet text with URLs repalced with links
	 */
	public function linkUrls($text) {
		/**
		 * match protocol://address/path/file.extension?some=variable&another=asf%
		 * $1 is a possible space, this keeps us from linking href="[link]" etc
		 * $2 is the whole URL
		 * $3 is protocol://
		 * $4 is the URL without the protocol://
		 * $5 is the URL parameters
		 */
		$text = preg_replace("/(^|\s)(([a-zA-Z]+:\/\/)([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"$2\">$2</a>", $text);

		/**
		 * match www.something.domain/path/file.extension?some=variable&another=asf%
		 * $1 is a possible space, this keeps us from linking href="[link]" etc
		 * $2 is the whole URL that was matched.  The protocol is missing, so we assume http://
		 * $3 is www.
		 * $4 is the URL matched without the www.
		 * $5 is the URL parameters
		 */
		$text = preg_replace("/(^|\s)(www\.([a-z][a-z0-9_\..-]*[a-z]{2,6})([a-zA-Z0-9~\/*-?&%]*))/i", "$1<a href=\"http://$2\">$2</a>", $text);

		return $text;
	}

	function register() {
		register_widget('WP_Widget_Twitter_Pro');
	}
}
// Instantiate our class
$wpTwitterWidget = new wpTwitterWidget();
