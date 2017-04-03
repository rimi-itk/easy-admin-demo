<?php

namespace EasyAdminAccessBundle\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AccessChecker
{
    private $tokenStorage;
    private $roleHierarchy;

    public function __construct(TokenStorageInterface $tokenStorage = null, RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param string[]|Role[] $roles
     */
    public function requireRoles(array $roles)
    {
        $userRoles = $this->getUserRoles();
        $roleNames = array_map(function (Role $role) {
            return $role->getRole();
        }, $userRoles);

        if (!array_intersect($roles, $roleNames)) {
            $roleList = '';
            foreach ($roles as $index => $role) {
                if ($roleList) {
                    if ($index == count($roles)-1) {
                        $roleList .= ' or ';
                    } else {
                        $roleList .= ', ';
                    }
                }
                $roleList .= $role;
            }
            throw new HttpException(403, sprintf('Access denied (role %s required).', $roleList));
        }
    }

    private function getUserRoles()
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $roles = $user->getRoles();
        if (count($roles) > 0 && is_string($roles[0])) {
            // Map role names to Roles.
            $roles = array_map(function ($name) {
                return new Role($name);
            }, $roles);
        }

        if ($this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoles($roles);
        }

        return $roles;
    }
}
