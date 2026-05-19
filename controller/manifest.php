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
		$board_path = $this->config['force_server_vars'] ? $this->config['script_path'] : (parse_url($board_url, PHP_URL_PATH) ?: '');
		$scope = rtrim($board_path, '/\\') . '/';
		$start_url = $scope . 'index.' . $phpEx . '?pwa_mode=1';
		$sitename = html_entity_decode($this->config['sitename'], ENT_QUOTES, 'UTF-8');

		$manifest = [
			'id'               => $scope,
			'name'             => $sitename,
			'short_name'       => utf8_substr($sitename, 0, 12),
			'description'      => html_entity_decode($this->config['site_desc'], ENT_QUOTES, 'UTF-8'),
			'start_url'        => $start_url,
			'scope'            => $scope,
			'display'          => 'standalone',
			'orientation'      => 'portrait',
			'theme_color'      => $this->config['pwa_theme_colour'],
			'background_color' => $this->config['pwa_background_colour'],
			'icons'            => [],
		];

		if (!empty($this->config['pwa_icon_512']))
		{
			$manifest['icons'][] = [
				'src'     => $board_url . '/images/pwa_icons/' . $this->config['pwa_icon_512'],
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'any',
			];
		}

		$response = new JsonResponse($manifest, 200, [
			'Content-Type' => 'application/manifest+json',
		]);
		$response->setPublic();
		$response->setMaxAge(3600);
		$response->headers->addCacheControlDirective('must-revalidate', true);

		return $response;
	}
}
