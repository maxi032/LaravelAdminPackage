<?php

namespace Maxi032\LaravelAdminPackage\Enums;
enum PostStatusEnum: int
{
    case PENDING = 2;
    case ACTIVE = 1;
    case INACTIVE = 0;
    case DRAFT = 3;
    case ARCHIVED = -1;

    public static function badgeClass(int $status): string
    {
        $status = PostStatusEnum::tryFrom($status);
        return match($status)
        {
            self::DRAFT => 'light',
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::ARCHIVED => 'warning',
            self::PENDING => 'secondary',
            default => throw new \Exception('Unexpected match value in enum'),
        };
    }
}