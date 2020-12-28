<?php

declare(strict_types=1);

namespace MarcoSimbuerger\MonitoringSatelliteBundle\Controller;

use Contao\CoreBundle\Util\PackageUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MonitoringSatelliteController.
 *
 * @package MarcoSimbuerger\MonitoringSatelliteBundle\Controller
 */
class MonitoringSatelliteController {

    /**
     * Get the app data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *   The app data as JSON response.
     */
    public function get(): JsonResponse {
        return new JsonResponse([
            'app' => 'Contao',
            'versions' => [
                'app' => PackageUtil::getContaoVersion(),
                'php' => phpversion(),
            ],
        ]);
    }

}
