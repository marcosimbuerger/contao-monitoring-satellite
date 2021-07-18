<?php

namespace MarcoSimbuerger\MonitoringSatelliteBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use MarcoSimbuerger\MonitoringSatelliteBundle\MarcoSimbuergerMonitoringSatelliteBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\User\User;

/**
 * Class Plugin.
 *
 * @package MarcoSimbuerger\MonitoringSatelliteBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface, ExtensionPluginInterface, RoutingPluginInterface {

    /**
     * {@inheritdoc}.
     */
    public function getBundles(ParserInterface $parser) {
        return [
            BundleConfig::create(MarcoSimbuergerMonitoringSatelliteBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                ]),
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container) {
        if ($extensionName !== 'security') {
            return $extensionConfigs;
        }

        // Get the parameter values from app/config/parameters.yml.
        if (!$container->hasParameter('monitoring_satellite')) {
            return $extensionConfigs;
        }

        $monitoringSatelliteConfig = $container->getParameter('monitoring_satellite');

        if (!isset($monitoringSatelliteConfig['basic_auth']['username']) || empty($monitoringSatelliteConfig['basic_auth']['username']) ||
            !isset($monitoringSatelliteConfig['basic_auth']['password']) || empty($monitoringSatelliteConfig['basic_auth']['password'])
        ) {
            return $extensionConfigs;
        }

        foreach ($extensionConfigs as &$extensionConfig) {
            if (isset($extensionConfig['firewalls'])) {

                // This Symfony internal User class is used by Symfony to represent in-memory users.
                $extensionConfig['encoders'] = array_merge($extensionConfig['encoders'], [
                    User::class => [
                        'algorithm' => 'auto'
                    ],
                ]);

                // Add the Monitoring Satellite's security authentication provider.
                $extensionConfig['providers'] = array_merge($extensionConfig['providers'], [
                    'monitoring_satellite_auth_provider' => [
                        'memory' => [
                            'users' => [
                                $monitoringSatelliteConfig['basic_auth']['username'] => [
                                    'password' => $monitoringSatelliteConfig['basic_auth']['password'],
                                ],
                            ],
                        ],
                    ],
                ]);

                // Add the Monitoring Satellite firewall before the "frontend" firewall of Contao.
                // Int-Cast position so "false" (not found) results in position 0.
                $offset = (int) array_search('frontend', array_keys($extensionConfig['firewalls']));
                $extensionConfig['firewalls'] = array_merge(
                    array_slice($extensionConfig['firewalls'], 0, $offset, TRUE),
                    [
                        'monitoring_satellite_controller' => [
                            'pattern' => '^/monitoring-satellite/v1/get',
                            'http_basic' => [
                                'provider' => 'monitoring_satellite_auth_provider',
                            ],
                        ],
                    ],
                    array_slice($extensionConfig['firewalls'], $offset, NULL, FALSE)
                );

                break;
            }
        }

        return $extensionConfigs;
    }

    /**
     * {@inheritdoc}.
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel) {
        $file = '@MarcoSimbuergerMonitoringSatelliteBundle/Resources/config/routes.yml';
        return $resolver->resolve($file)->load($file);
    }

}
