<?php

declare(strict_types=1);

namespace App\Enum;

enum StudyType: string
{
    case CT = 'ct';
    case MRI = 'mri';
    case RG = 'rg';
    case FLG = 'flg';
    case MMG = 'mmg';
    case DENSITOMETER = 'densitometer';
    case CT_WITH_KU_1_ZONE = 'ct_with_ku_1_zone';
    case MRI_WITH_KU_1_ZONE = 'mri_with_ku_1_zone';
    case CT_WITH_KU_MORE_THAN_1_ZONE = 'ct_with_ku_more_than_1_zone';
    case MRI_WITH_KU_MORE_THAN_1_ZONE = 'mri_with_ku_more_than_1_zone';

    private static function labels(): array
    {
        return [
            self::CT->value => 'КТ',
            self::MRI->value => 'МРТ',
            self::RG->value => 'РГ',
            self::FLG->value => 'ФЛГ',
            self::MMG->value => 'ММГ',
            self::DENSITOMETER->value => 'Денситометр',
            self::CT_WITH_KU_1_ZONE->value => 'КТ с КУ 1 зона',
            self::MRI_WITH_KU_1_ZONE->value => 'МРТ с КУ 1 зона',
            self::CT_WITH_KU_MORE_THAN_1_ZONE->value => 'КТ с КУ 2 и более зон',
            self::MRI_WITH_KU_MORE_THAN_1_ZONE->value => 'МРТ с КУ 2 и более зон',
        ];
    }

    public function is(StudyType $type): bool
    {
        return $this === $type;
    }

    public function label(): ?string
    {
        $labels = self::labels();

        return $labels[$this->value] ?? null;
    }
}
