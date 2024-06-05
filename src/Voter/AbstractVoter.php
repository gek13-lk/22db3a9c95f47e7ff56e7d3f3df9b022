<?php

namespace App\Voter;

use App\Entity\User;
use ReflectionClass;
use ReflectionClassConstant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractVoter extends Voter implements PrivilegeGroupInterface {
    abstract protected function getTitle(string $code): string;

    public function getPrivileges(): array {
        $class = new ReflectionClass($this);
        foreach ($class->getConstants(ReflectionClassConstant::IS_PUBLIC) as $code) {
            $list[$code] = $this->getTitle($code);
        }

        return $list ?? [];
    }

    protected function supports(string $attribute, mixed $subject): bool {
        return \in_array($attribute, $this->getPrivileges());
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool {
        /** @var User|null $user */
        $user = $token->getUser();

        return \in_array($attribute, $user->getAllPrivileges());
    }
}