<?php

namespace App\Twig\Components;

use App\Modules\Navbar\NavbarManager;
use App\Modules\Navbar\NavItemInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final readonly class Sidebar {

    public function __construct(private NavbarManager $manager) {
    }

    /**
     * @return array<NavItemInterface>
     */
    public function getNavItems(): array
    {
        return $this->manager->getNavItems();
    }
}