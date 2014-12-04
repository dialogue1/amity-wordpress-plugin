<?php
/*
 * Copyright (c) 2014 - Dialogue1 GmbH - MIT licensed
 */

namespace dialogue1\amity;

use dialogue1\amity\API\Client;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscribeWidget extends \WP_Widget {
	public function __construct() {
		parent::__construct('d1_amity_subscribe', 'amity Subscribe', array(
			'description' => 'A simple input field that allows anyone to subscribe to a newsletter.'
		));

		if (is_active_widget(false, false, $this->id_base)) {
			add_action('wp_head', function() {
				echo '<style type="text/css">.d1-amity-subscribe input { width:100%; }</style>';
			});
		}
	}

	public function widget($args, $instance) {
		global $wp; // sigh

		$title       = empty($instance['title'])       ? 'Recent Comments'     : $instance['title'];
		$button      = empty($instance['button'])      ? 'Subscribe Now'       : $instance['button'];
		$placeholder = empty($instance['placeholder']) ? 'your e-mail address' : $instance['placeholder'];

		$myID       = substr(sha1($this->id), 0, 8);
		$formField  = 'd1a_email_'.$myID;
		$title      = apply_filters('widget_title', $title, $instance, $this->id_base);
		$currentUrl = home_url('/'.add_query_arg($_GET, $wp->request));

		// handle possible form submission

		if (isset($_POST[$formField])) {
			$email     = stripslashes_deep($_POST[$formField]);
			$processes = empty($instance['processes']) ? array() : self::parseProcessList($instance['processes']);

			if (!is_email($email)) {
				$error = 'Invalid e-mail address given.';
			}
			else {
				try {
					$apiClient = Client::create(
						get_option('amity_hostname'),
						!!get_option('amity_ssl'),
						get_option('amity_client_id'),
						get_option('amity_api_key')
					);

					$apiClient->getContactService()->create(array('email' => $email), array(), $processes);

					$success = 'Thanks for subscribing!';
				}
				catch (ConflictException $e) {
					$error = 'You are already subscribed.';
				}
				catch (\Exception $e) {
					$error = 'An error occured, please try again later.';
				}
			}
		}

		echo $args['before_widget'];
			echo $args['before_title'].$title.$args['after_title'];

			if (isset($error)) {
				echo '<p class="error">'.esc_html($error).'</p>';
			}

			if (isset($success)) {
				echo '<p class="success">'.esc_html($success).'</p>';
			}
			else {
				echo '<form method="post" action="'.esc_url($currentUrl).'" class="d1-amity-subscribe">
					<p><input type="email" name="'.$formField.'" placeholder="'.esc_attr($placeholder).'" required /></p>
					<p><input type="submit" value="'.esc_attr($button).'" /></p>
				</form>';
			}
		echo $args['after_widget'];
	}

	public function form($instance) {
		$title       = !empty($instance['title'])       ? $instance['title']                             : 'Newsletter';
		$button      = !empty($instance['button'])      ? $instance['button']                            : 'Subscribe Now';
		$placeholder = !empty($instance['placeholder']) ? $instance['placeholder']                       : 'your e-mail address';
		$processes   = !empty($instance['processes'])   ? self::parseProcessList($instance['processes']) : array();
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('button'); ?>"><?php _e('Button Label:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('button'); ?>" name="<?php echo $this->get_field_name('button'); ?>" type="text" value="<?php echo esc_attr($button); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('placeholder'); ?>"><?php _e('Input field placeholder text:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('placeholder'); ?>" name="<?php echo $this->get_field_name('placeholder'); ?>" type="text" value="<?php echo esc_attr($placeholder); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('processes'); ?>"><?php _e('Processes to execute after adding the contact:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('processes'); ?>" name="<?php echo $this->get_field_name('processes'); ?>" type="text" value="<?php echo esc_attr(implode(', ', $processes)); ?>">
			<small class="description">optionally give a comma separated list of process IDs (e.g. ha9d,ge90,bc27)</small>
		</p>
		<?php
	}

	public static function parseProcessList($string) {
		if (preg_match_all('/\b([a-z0-9]+)\b/', $string, $matches)) {
			return array_unique($matches[1]);
		}

		return array();
	}
}
