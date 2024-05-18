<?php

namespace App\Modules\Navbar;

class DefaultNavItem implements NavItemInterface {
    private bool $isActive = false;

    public function __construct(protected string $title, protected string $icon, protected string $routeName,
        protected array $routeParams = []) {
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getIcon(): string {
        return $this->icon;
    }

    public function getRouteName(): string {
        return $this->routeName;
    }

    public function getRouteParams(): array {
        return $this->routeParams;
    }

    public function setActive(): static {
        $this->isActive = true;

        return $this;
    }

    public function isActive(): bool {
        return $this->isActive;
    }

}