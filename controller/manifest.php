<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class manifest
{
    /** @var \phpbb\config\config */
    protected $config;

    /**
     * Constructor
     *
     * @param \phpbb\config\config $config
     */
    public function __construct(\phpbb\config\config $config)
    {
        $this->config = $config;
    }

    /**
     * Return JSON Manifest
     *
     * @return JsonResponse
     */
    public function display()
    {
        global $phpEx;
        
        $board_url = generate_board_url();
        $start_url = $board_url . '/index.' . $phpEx . '?pwa_mode=1';

        $manifest = [
            'name'             => $this->config['sitename'],
            'short_name'       => $this->config['sitename'],
            'description'      => $this->config['site_desc'],
            'start_url'        => $start_url,
            'display'          => 'standalone',
            'theme_color'      => $this->config['pwa_theme_colour'],
            'background_color' => $this->config['pwa_background_colour'],
            'icons'            => []
        ];

        // Add icons if defined in config
        if (!empty($this->config['pwa_icon_192'])) {
            $manifest['icons'][] = [
                'src'     => $board_url . '/images/pwa_icons/' . $this->config['pwa_icon_192'],
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any'
            ];
        }

        if (!empty($this->config['pwa_icon_512'])) {
            $manifest['icons'][] = [
                'src'     => $board_url . '/images/pwa_icons/' . $this->config['pwa_icon_512'],
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any'
            ];
        }

        return new JsonResponse($manifest, 200, [
            'Content-Type' => 'application/manifest+json'
        ]);
    }
}
