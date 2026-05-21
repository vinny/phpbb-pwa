<?php
/**
 *
 * PWA Enhancer. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 Vinny <https://github.com/vinny>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace vinny\pwa\event;

use Detection\MobileDetect;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\event\data;
use phpbb\extension\manager;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class main_listener implements EventSubscriberInterface
{
	private const PWA_COOKIE_LIFETIME = 31536000;

	/** @var config */
	protected $config;

	/** @var user */
	protected $user;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var helper */
	protected $helper;

	/** @var manager */
	protected $ext_manager;

	/** @var MobileDetect */
	protected $mobile_detect;

	public function __construct(
		config $config,
		user $user,
		request_interface $request,
		template $template,
		helper $helper,
		manager $ext_manager
	) {
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->template = $template;
		$this->helper = $helper;
		$this->ext_manager = $ext_manager;

		$this->mobile_detect = new MobileDetect(null, ['autoInitOfHttpHeaders' => false]);

		$user_agent = (string) $this->request->server('HTTP_USER_AGENT', '');
		$this->mobile_detect->setHttpHeaders([
			'HTTP_USER_AGENT'	=> $user_agent,
			'HTTP_ACCEPT'		=> (string) $this->request->server('HTTP_ACCEPT', ''),
		]);
		$this->mobile_detect->setUserAgent($user_agent);
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'	=> 'user_setup',
			'core.page_header'	=> 'page_header',
		];
	}

	public function user_setup(data $event)
	{
		if (!$this->config['pwa_enabled'])
		{
			return;
		}

		$style_id = (int) $this->config['pwa_mobile_style_id'];
		if ($style_id <= 0)
		{
			return;
		}

		$is_mobile = $this->mobile_detect->isMobile();
		$is_pwa = false;

		if ($this->request->variable('pwa_mode', 0) === 1)
		{
			$is_pwa = true;
			$this->user->set_cookie('pwa_mode', '1', time() + self::PWA_COOKIE_LIFETIME);
		}
		else if ($this->request->variable($this->config['cookie_name'] . '_pwa_mode', '', true, request_interface::COOKIE) === '1')
		{
			$is_pwa = true;
		}

		$should_force_style = $is_mobile && (
			($is_pwa && $this->config['pwa_force_on_pwa'])
			|| $this->config['pwa_force_on_mobile']
		);

		if ($should_force_style)
		{
			$event['style_id'] = $style_id;
		}
	}

	public function page_header(data $event)
	{
		if (!$this->config['pwa_enabled'])
		{
			return;
		}

		$this->user->add_lang_ext('vinny/pwa', 'pwa');

		$manifest_url = $this->helper->route('vinny_pwa_manifest', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
		$sw_url = $this->helper->route('vinny_pwa_sw', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
		$is_mobile = $this->mobile_detect->isMobile();
		$is_webpush_active = $this->ext_manager->is_enabled('phpbb/webpushnotifications');

		$this->template->assign_vars([
			'S_PWA_ENABLED'					=> true,
			'S_PWA_IS_MOBILE'				=> $is_mobile,
			'S_PWA_SHOW_BANNER'				=> (bool) $this->config['pwa_show_install_banner'],
			'S_PWA_WEBPUSH_INTEGRATION_ACTIVE'	=> $is_webpush_active,
			'PWA_THEME_COLOUR'				=> $this->config['pwa_theme_colour'],
			'PWA_BACKGROUND_COLOUR'			=> $this->config['pwa_background_colour'],
			'PWA_ICON_512'					=> $this->config['pwa_icon_512'] ? generate_board_url() . '/images/pwa_icons/' . $this->config['pwa_icon_512'] : '',
			'PWA_MODE_COOKIE_NAME'			=> $this->config['cookie_name'] . '_pwa_mode',
			'U_PWA_MANIFEST'				=> $manifest_url,
			'U_PWA_SW'						=> $sw_url,
		]);
	}
}
