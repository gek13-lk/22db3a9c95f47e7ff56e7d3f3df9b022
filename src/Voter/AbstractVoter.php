<?php

namespace App\Voter;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter implements PrivilegeGroupInterface {

    public function __construct(private RoleRepository $repository) {
    }

    abstract protected function getTitle(string $code): string;

    public function getPrivileges(): array {
        $class = new ReflectionClass($this);
        foreach ($class->getConstants(ReflectionClassConstant::IS_PUBLIC) as $code) {
            if (!is_string($code)) {
                continue;
            }

            $list[$code] = $this->getTitle($code);
        }

        return $list ?? [];
    }

    protected function supports(string $attribute, mixed $subject): bool {
        return \in_array($attribute, array_keys($this->getPrivileges()));
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
        /** @var User|null $user */
        $user = $token->getUser();

        if (\in_array($attribute, $user->getPrivileges()) || \in_array('ROLE_ADMIN',$user->getRoles())) {
            return true;
        }

        $roles = $this->repository->findBy(['code' => $user->getRoles()]);

        return \in_array($attribute, array_unique(array_merge(...array_map(fn (Role $role) => $role->getPrivileges(), $roles))));
    }
}