<?php

namespace App\Modules\Navbar;

interface NavItemInterface {
    public function getTitle(): string;

    public function getIcon(): string;

    public function getRouteName(): string;

    public function getRouteParams(): array;

    public function setActive(): static;

    public function isActive(): bool;
}