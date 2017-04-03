<?php

namespace EasyAdminAccessBundle\EventSubscriber;

use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use EasyAdminAccessBundle\Service\AccessChecker;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private $accessChecker;

    public function __construct(AccessChecker $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_NEW => ['checkPermission'],
            EasyAdminEvents::PRE_LIST => ['checkPermission'],
            EasyAdminEvents::PRE_EDIT => ['checkPermission'],
            EasyAdminEvents::PRE_SHOW => ['checkPermission'],
            EasyAdminEvents::PRE_REMOVE => ['checkPermission'],
        ];
    }

    public function checkPermission(GenericEvent $event)
    {
        $request = $event->getArgument('request');
        $action = $request->query->get('action');
        $entity = $event->getArgument('entity');

        if (isset($entity[$action]['roles'])) {
            $roles = $entity[$action]['roles'];
            $this->accessChecker->requireRoles($roles);
        }
    }
}
