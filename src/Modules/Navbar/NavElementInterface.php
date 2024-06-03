<?php

namespace App\Modules\Navbar;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.navigation.element')]
interface NavElementInterface {
    public function getNavItem(): NavItemInterface;
    public static function getPriority(): int;
}