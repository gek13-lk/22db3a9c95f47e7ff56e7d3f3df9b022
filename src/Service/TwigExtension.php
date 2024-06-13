<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(private Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getGlobalVariables', [$this, 'getGlobalVariables']),
        ];
    }

    public function getGlobalVariables(): array
    {
        $user = $this->security->getUser();

        return [
            'username' => $user?->getUsername(),
            'first_word' => $user ? strtoupper($user->getUsername()[0]) : null,
        ];
    }
}
