<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\controller;

class offline
{
    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\language\language */
    protected $language;

    /**
     * Constructor
     *
     * @param \phpbb\config\config      $config
     * @param \phpbb\controller\helper  $helper
     * @param \phpbb\language\language  $language
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\language\language $language)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->language = $language;
    }

    /**
     * Display offline page
     */
    public function display()
    {
        $this->language->add_lang('pwa', 'vinny/pwa');

        return $this->helper->render('@vinny_pwa/pwa_offline.html', $this->language->lang('PWA_OFFLINE_TITLE'));
    }
}
