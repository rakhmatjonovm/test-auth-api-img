<?php

namespace App\Enums;

enum ImageStatus: int
{
    case Processing = 0;
    case Ready = 1;
    case Failed = 2;

    public function label(): string
    {
        return match ($this) {
            self::Processing => 'Обрабатывается',
            self::Ready => 'Готово',
            self::Failed => 'Ошибка обработки',
        };
    }
}