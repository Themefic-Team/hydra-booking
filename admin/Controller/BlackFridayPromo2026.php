<?php

namespace HydraBooking\Admin\Controller;

defined('ABSPATH') || exit;
// echo "hello world";
// exit;
/**
 * Themefic Black Friday / Cyber Monday 2026 admin banner.
 *
 * Shared by every Themefic plugin: only one of them shows the banner on a site, and dismissing it
 * in any of them hides it in all of them.
 * - The first plugin that renders the banner becomes its owner (shared owner option); the others stay hidden.
 * - Dismissing writes a shared "hidden until" timestamp that every plugin reads.
 *
 * Reusing it in another plugin: copy this file, change the namespace, the "Per-plugin settings"
 * constants and the 'hydra-booking' text domain, then call `new BlackFridayPromo2026();` once on load.
 */
class BlackFridayPromo2026
{

	/* ---------- Campaign settings: keep these identical in every plugin ---------- */

	// Prefix of the shared options; change it to start a fresh campaign.
	const CAMPAIGN_ID = 'bfcm26';

	// Set to false to switch the banner off without removing the class.
	const ENABLED = true;

	// The banner shows only between these dates (UTC).
	const START_DATE = '2026-11-20 00:00:00 UTC';
	const END_DATE   = '2026-12-05 23:59:59 UTC';

	// How many days a dismissal lasts; 0 hides the banner until the campaign ends.
	const DISMISS_DAYS = 0;

	// If the owner plugin has not shown the banner for this long (e.g. it was deactivated), another plugin may take over.
	const OWNER_TIMEOUT_HOURS = 48;

	// Admin screens the banner can appear on.
	const SCREENS = array('dashboard', 'plugins', 'edit-post', 'edit-page');

	const DEAL_URL   = 'https://themefic.com/bfcm26/#single-deal-wrapper';
	const OFFERS_URL = 'https://themefic.com/bfcm26/';

	/* ---------- Per-plugin settings ---------- */

	// Short unique slug of this plugin, stored as the banner owner.
	const PLUGIN_SLUG = 'hydra';

	// The banner is hidden when this constant is defined (Pro users).
	const PRO_CONSTANT = 'THB_VERSION_PRO';

	public function __construct()
	{
		if (! self::ENABLED || ! is_admin() || ! self::is_running()) {
			return;
		}

		add_action('admin_notices', array($this, 'render'));
		add_action('admin_post_' . $this->dismiss_action(), array($this, 'dismiss'));
	}

	public static function is_running()
	{
		$now = time();

		return $now >= strtotime(self::START_DATE) && $now <= strtotime(self::END_DATE);
	}

	public function render()
	{
		if (! $this->should_show()) {
			return;
		}

		$this->claim_ownership();
?>
		<style>
			#themefic-bfcm-notice {
				position: relative;
				display: flex;
				align-items: flex-start;
				gap: 16px;
				padding: 22px 44px 22px 22px;
				border: 1px solid #c9d4f7;
				border-inline-start: 4px solid #0e3dd8;
				border-radius: 8px;
				background: #f5f7ff;
				color: #494d59;
			}

			#themefic-bfcm-notice>.dashicons {
				width: 26px;
				height: 26px;
				font-size: 26px;
				color: #0e3dd8;
			}

			#themefic-bfcm-notice h2 {
				margin: 0;
				color: #1c2130;
				font-size: 18px;
				line-height: 1.4;
			}

			#themefic-bfcm-notice p {
				margin: 6px 0 14px;
			}

			#themefic-bfcm-notice .themefic-bfcm-actions {
				display: flex;
				flex-wrap: wrap;
				gap: 8px 12px;
			}

			#themefic-bfcm-notice .button {
				border-color: #0e3dd8;
				border-radius: 6px;
				padding-inline: 16px;
			}

			#themefic-bfcm-notice .button-primary {
				background: #0e3dd8;
			}

			#themefic-bfcm-notice .button-primary:hover,
			#themefic-bfcm-notice .button-primary:focus {
				background: #0a2b99;
				border-color: #0a2b99;
			}

			#themefic-bfcm-notice .button-secondary {
				background: transparent;
				color: #0e3dd8;
			}

			#themefic-bfcm-notice .button-secondary:hover,
			#themefic-bfcm-notice .button-secondary:focus {
				background: #f3f5fd;
				border-color: #0a2b99;
				color: #0a2b99;
			}

			#themefic-bfcm-notice .notice-dismiss {
				inset-inline: auto 10px;
			}
		</style>
		<div id="themefic-bfcm-notice" class="notice">
			<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
			<div>
				<h2><?php esc_html_e('Themefic Black Friday / Cyber Monday Sale: Up to 80% OFF!', 'hydra-booking'); ?></h2>
				<p><?php esc_html_e('Get 70% OFF Hydra Booking lifetime plans + double the site licenses, or save up to 80% with the Themefic Bundle. Pay once for lifetime updates and support!', 'hydra-booking'); ?></p>
				<div class="themefic-bfcm-actions">
					<a class="button button-primary" target="_blank" rel="noopener" href="<?php echo esc_url(self::DEAL_URL); ?>">
						<?php esc_html_e('Get Lifetime Deals', 'hydra-booking'); ?>
					</a>
					<a class="button button-secondary" target="_blank" rel="noopener" href="<?php echo esc_url(self::OFFERS_URL); ?>">
						<?php esc_html_e('Explore all Offers', 'hydra-booking'); ?>
					</a>
				</div>
			</div>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr($this->dismiss_action()); ?>">
				<?php wp_nonce_field($this->dismiss_action()); ?>
				<button type="submit" class="notice-dismiss">
					<span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'hydra-booking'); ?></span>
				</button>
			</form>
		</div>
<?php
	}

	public function dismiss()
	{
		if ('POST' !== ($_SERVER['REQUEST_METHOD'] ?? '') || ! current_user_can('manage_options')) {
			wp_die(esc_html__('Unauthorized request.', 'hydra-booking'), '', array('response' => 403));
		}

		check_admin_referer($this->dismiss_action());

		$hidden_until = self::DISMISS_DAYS > 0 ? time() + self::DISMISS_DAYS * DAY_IN_SECONDS : strtotime(self::END_DATE);
		update_option($this->dismissed_option(), $hidden_until, false);

		wp_safe_redirect(wp_get_referer() ?: admin_url('index.php'), 303);
		exit;
	}

	private function should_show()
	{
		if (defined(self::PRO_CONSTANT) || ! current_user_can('manage_options')) {
			return false;
		}

		$screen = get_current_screen();
		if (! $screen || ! in_array($screen->id, self::SCREENS, true)) {
			return false;
		}

		if ($this->is_dismissed() || $this->legacy_notice_active()) {
			return false;
		}

		return $this->can_own();
	}

	private function is_dismissed()
	{
		$hidden_until = (int) get_option($this->dismissed_option());

		return $hidden_until && time() < $hidden_until;
	}

	/**
	 * Tourfic 3.0.5 shipped this campaign as its own notice (Tourfic_Notice), which does not use the shared options.
	 * While free Tourfic is active that banner is either showing or was already dismissed, so we stay hidden.
	 * Once Tourfic switches to this class, Tourfic_Notice is no longer loaded and this check turns itself off.
	 */
	private function legacy_notice_active()
	{
		return class_exists('\Tourfic\Admin\Notice\Tourfic_Notice', false) && ! defined('TF_PRO');
	}

	private function can_own()
	{
		$owner = $this->get_owner();
		if (! $owner['plugin'] || self::PLUGIN_SLUG === $owner['plugin']) {
			return true;
		}

		// Take over when the owner has not shown the banner for a while (e.g. it was deactivated).
		return time() - $owner['seen'] > self::OWNER_TIMEOUT_HOURS * HOUR_IN_SECONDS;
	}

	private function claim_ownership()
	{
		$owner = $this->get_owner();

		// Refresh at most hourly so the owner stays current without a database write on every page load.
		if (self::PLUGIN_SLUG !== $owner['plugin'] || time() - $owner['seen'] > HOUR_IN_SECONDS) {
			update_option(
				$this->owner_option(),
				array(
					'plugin' => self::PLUGIN_SLUG,
					'seen'   => time(),
				),
				false
			);
		}
	}

	private function get_owner()
	{
		$owner = get_option($this->owner_option());

		return array(
			'plugin' => is_array($owner) && ! empty($owner['plugin']) ? (string) $owner['plugin'] : '',
			'seen'   => is_array($owner) && ! empty($owner['seen']) ? (int) $owner['seen'] : 0,
		);
	}

	private function owner_option()
	{
		return 'themefic_' . self::CAMPAIGN_ID . '_notice_owner';
	}

	private function dismissed_option()
	{
		return 'themefic_' . self::CAMPAIGN_ID . '_notice_dismissed';
	}

	private function dismiss_action()
	{
		return self::PLUGIN_SLUG . '_' . self::CAMPAIGN_ID . '_dismiss_notice';
	}
}
