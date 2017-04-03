<?php

/**
 * This file is part of the EasyAdminAccessBundle.
 *
 * (c) Mikkel Ricky <mikkel@mikkelricky.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EasyAdminAccessBundle\Twig;

use JavierEguiluz\Bundle\EasyAdminBundle\Twig\EasyAdminTwigExtension;
use JavierEguiluz\Bundle\EasyAdminBundle\Configuration\ConfigManager;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EasyAdminAccessTwigExtension extends EasyAdminTwigExtension
{
    public function __construct(ConfigManager $configManager, PropertyAccessor $propertyAccessor, ContainerInterface $container, $debug = false)
    {
        parent::__construct($configManager, $propertyAccessor, $debug);
        $this->container = $container;
    }

    public function getActionsForItem($view, $entityName)
    {
        $actions = parent::getActionsForItem($view, $entityName);
        $entity = $this->container->get('easyadmin.config.manager')->getEntityConfiguration($entityName);
        $accessChecker = $this->container->get('easy_admin_access_bundle.access_checker');

        return array_filter(
            $actions,
            function ($action) use ($entity, $accessChecker) {
                if (isset($entity[$action]['roles'])) {
                    $roles = $entity[$action]['roles'];
                    try {
                        $accessChecker->requireRoles($roles);
                        return true;
                    } catch (\Exception $e) {
                        // throw $e;
                    }
                    return false;
                }
                return true;
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
