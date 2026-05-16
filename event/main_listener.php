<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\request\request_interface */
    protected $request;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\extension\manager */
    protected $ext_manager;

    /** @var \Detection\MobileDetect */
    protected $mobile_detect;

    /**
     * Constructor
     *
     * @param \phpbb\config\config              $config
     * @param \phpbb\user                       $user
     * @param \phpbb\request\request_interface  $request
     * @param \phpbb\template\template          $template
     * @param \phpbb\controller\helper          $helper
     * @param \phpbb\extension\manager          $ext_manager
     */
    public function __construct(
        \phpbb\config\config $config,
        \phpbb\user $user,
        \phpbb\request\request_interface $request,
        \phpbb\template\template $template,
        \phpbb\controller\helper $helper,
        \phpbb\extension\manager $ext_manager
    ) {
        $this->config = $config;
        $this->user = $user;
        $this->request = $request;
        $this->template = $template;
        $this->helper = $helper;
        $this->ext_manager = $ext_manager;

        // Init MobileDetect blocking it from reading $_SERVER (which phpBB forbids)
        $this->mobile_detect = new \Detection\MobileDetect(null, ['autoInitOfHttpHeaders' => false]);
        
        $user_agent = (string) $this->request->server('HTTP_USER_AGENT', '');
        $this->mobile_detect->setHttpHeaders([
            'HTTP_USER_AGENT' => $user_agent,
            'HTTP_ACCEPT'     => (string) $this->request->server('HTTP_ACCEPT', ''),
        ]);
        $this->mobile_detect->setUserAgent($user_agent);
    }

    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup'    => 'user_setup',
            'core.page_header'   => 'page_header',
        ];
    }

    /**
     * Force mobile/PWA style and session settings
     *
     * @param \phpbb\event\data $event
     */
    public function user_setup($event)
    {
        if (!$this->config['pwa_enabled']) {
            return;
        }

        $style_id = (int) $this->config['pwa_mobile_style_id'];
        if ($style_id <= 0) {
            return;
        }

        // Check if the device is a mobile phone (excluding tablets)
        $is_mobile = $this->mobile_detect->isMobile() && !$this->mobile_detect->isTablet();

        // Detect PWA Mode
        $is_pwa = false;

        // Verify if the url has parameter pwa_mode=1
        if ($this->request->variable('pwa_mode', 0) === 1) {
            $is_pwa = true;
            
            // Set a cookie to persist the PWA state for 1 year
            $this->user->set_cookie('pwa_mode', '1', time() + 31536000); 
        } elseif ($this->request->variable($this->config['cookie_name'] . '_pwa_mode', '', true, \phpbb\request\request_interface::COOKIE) === '1') {
            $is_pwa = true;
        }

        $should_force_style = false;

        if ($is_mobile) {
            // Check if we force on PWA and we are in PWA
            if ($is_pwa && $this->config['pwa_force_on_pwa']) {
                $should_force_style = true;
            }

            // Check if we force on mobile generally
            if (!$should_force_style && $this->config['pwa_force_on_mobile']) {
                $should_force_style = true;
            }
        }

        if ($should_force_style) {
            $event['style_id'] = $style_id;
        }
    }

    /**
     * Inject Manifest and SW registration on page boundary
     *
     * @param \phpbb\event\data $event
     */
    public function page_header($event)
    {
        if (!$this->config['pwa_enabled']) {
            return;
        }

        $this->user->add_lang_ext('vinny/pwa', 'pwa');

        $manifest_url = $this->helper->route('vinny_pwa_manifest', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
        $sw_url = $this->helper->route('vinny_pwa_sw', [], true, false, UrlGeneratorInterface::ABSOLUTE_URL);
        
        $is_mobile = $this->mobile_detect->isMobile() && !$this->mobile_detect->isTablet();
        $is_webpush_active = $this->ext_manager->is_enabled('phpbb/webpushnotifications');

        $this->template->assign_vars([
            'S_PWA_ENABLED'                    => true,
            'S_PWA_IS_MOBILE'                  => $is_mobile,
            'S_PWA_SHOW_BANNER'                => (bool) $this->config['pwa_show_install_banner'],
            'S_PWA_WEBPUSH_INTEGRATION_ACTIVE' => $is_webpush_active,
            'PWA_THEME_COLOUR'                 => $this->config['pwa_theme_colour'],
            'PWA_BACKGROUND_COLOUR'            => $this->config['pwa_background_colour'],
            'PWA_ICON_192'                     => $this->config['pwa_icon_192'] ? generate_board_url() . '/images/pwa_icons/' . $this->config['pwa_icon_192'] : '',
            'PWA_ICON_512'                     => $this->config['pwa_icon_512'] ? generate_board_url() . '/images/pwa_icons/' . $this->config['pwa_icon_512'] : '',
            'U_PWA_MANIFEST'                   => $manifest_url,
            'U_PWA_SW'                         => $sw_url,
        ]);
    }
}
