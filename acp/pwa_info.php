<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\acp;

class pwa_info
{
    public function module()
    {
        return [
            'filename' => '\vinny\pwa\acp\pwa_module',
            'title'    => 'ACP_PWA',
            'modes'    => [
                'settings' => [
                    'title' => 'ACP_PWA_SETTINGS',
                    'auth'  => 'ext_vinny/pwa && acl_a_board',
                    'cat'   => ['ACP_PWA']
                ],
            ],
        ];
    }
}
