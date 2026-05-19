<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa\migrations;

class v100_initial extends \phpbb\db\migration\migration
{
    /**
     * Effectively installed
     *
     * @return bool True if this migration is installed
     */
    public function effectively_installed()
    {
        return isset($this->config['pwa_enabled']);
    }

    /**
     * Defines dependencies for this migration
     *
     * @return array Array of migration classes this depends on
     */
    public static function depends_on()
    {
        return ['\phpbb\db\migration\data\v330\v330'];
    }

    /**
     * Update data
     *
     * @return array Array of data update instructions
     */
    public function update_data()
    {
        return [
            // Add configuration settings
            ['config.add', ['pwa_enabled', '1']],
            ['config.add', ['pwa_mobile_style_id', '0']],
            ['config.add', ['pwa_force_on_mobile', '0']],
            ['config.add', ['pwa_force_on_pwa', '1']],
            ['config.add', ['pwa_theme_colour', '#000000']],
            ['config.add', ['pwa_background_colour', '#ffffff']],
            ['config.add', ['pwa_show_install_banner', '1']],
            ['config.add', ['pwa_cache_version', '1']],
            
            // Allow storing the app icon path in the future, initialize empty
            ['config.add', ['pwa_icon_512', '']],

            // Add the ACP Module Class
            ['module.add', [
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_PWA'
            ]],
            
            // Add the ACP Module itself
            ['module.add', [
                'acp',
                'ACP_PWA',
                [
                    'module_basename' => '\vinny\pwa\acp\pwa_module',
                    'modes'           => ['settings'],
                ]
            ]],
        ];
    }
    
    /**
     * Revert data
     *
     * @return array Array of data revert instructions
     */
    public function revert_data()
    {
        return [
            // Remove configuration settings
            ['config.remove', ['pwa_enabled']],
            ['config.remove', ['pwa_mobile_style_id']],
            ['config.remove', ['pwa_force_on_mobile']],
            ['config.remove', ['pwa_force_on_pwa']],
            ['config.remove', ['pwa_theme_colour']],
            ['config.remove', ['pwa_background_colour']],
            ['config.remove', ['pwa_show_install_banner']],
            ['config.remove', ['pwa_cache_version']],
            ['config.remove', ['pwa_icon_512']],
        ];
    }
}
