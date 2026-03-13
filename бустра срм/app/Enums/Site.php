<?php

declare(strict_types=1);

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * @method static Site BOOSTRA()
 * @method static Site SOYAPLACE()
 * @method static Site RUBL()
 * @method static Site NEOMANI()
 */
/**
 * Перечисление, представляющее доступные сайты (лендинги) в системе.
 * Используется для идентификации сайтов по их идентификаторам (site_id).
 */
final class Site extends Enum
{
    private const BOOSTRA = 'boostra';
    private const SOYAPLACE = 'soyaplace';
    private const RUBL = 'rubl';
    private const NEOMANI = 'neomani';
}
