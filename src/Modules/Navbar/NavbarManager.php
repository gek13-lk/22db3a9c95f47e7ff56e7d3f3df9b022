<?php

namespace App\Modules\Navbar;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class NavbarManager {
    private array $items = [];

    /**
     * @param NavElementInterface[] $elements
     */
    public function __construct(
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        #[TaggedIterator('app.navigation.element', defaultPriorityMethod: 'getPriority')]
        iterable $elements = []
    ) {
        foreach ($elements as $element) {
            $item = $element->getNavItem();
            if ($requestStack->getCurrentRequest()->getRequestUri() === $urlGenerator->generate($item->getRouteName(), $item->getRouteParams())) {
                $item->setActive();
            }

            $this->items[] = $item;
        }
    }

    /**
     * @return array<NavItemInterface>
     */
    public function getNavItems(): array
    {
        return $this->items;
    }
}