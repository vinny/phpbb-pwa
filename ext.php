<?php
/**
 *
 * @package vinny/pwa
 * @copyright (c) 2026 Vinny
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace vinny\pwa;

/**
 * Extension class for custom activate/deactivate actions
 */
class ext extends \phpbb\extension\base
{
    /**
     * Create pwa_icons dir when extension is enabled
     */
    public function enable_step($old_state)
    {
        if ($old_state !== false) {
            return parent::enable_step($old_state);
        }

        /** @var \phpbb\filesystem\filesystem_interface $filesystem */
        $filesystem = $this->container->get('filesystem');
        $my_dir_path = $this->container->getParameter('core.root_path') . 'images/pwa_icons';

        try {
            $filesystem->mkdir($my_dir_path, 0755);
            $filesystem->touch($my_dir_path . '/index.htm');
        } catch (\phpbb\filesystem\exception\filesystem_exception $e) {
            // Log or handle any errors here if needed
        }

        return 'added_pwa_icons_dir';
    }

    /**
     * Delete pwa_icons dir when deleting extension data
     */
    public function purge_step($old_state)
    {
        if ($old_state !== false) {
            return parent::purge_step($old_state);
        }

        /** @var \phpbb\filesystem\filesystem_interface $filesystem */
        $filesystem = $this->container->get('filesystem');
        $my_dir_path = $this->container->getParameter('core.root_path') . 'images/pwa_icons';

        try {
            $filesystem->remove($my_dir_path);
        } catch (\phpbb\filesystem\exception\filesystem_exception $e) {
            // Log or handle any errors here if needed
        }

        return 'removed_pwa_icons_dir';
    }
}
