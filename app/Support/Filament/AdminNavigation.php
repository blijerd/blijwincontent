<?php

namespace App\Support\Filament;

final class AdminNavigation
{
    public const GROUP_CONTENT = 'Content';

    public const GROUP_STRUCTURE = 'Structuur';

    public const GROUP_MEDIA = 'Media';

    public const GROUP_DOWNLOADS = 'Downloads';

    public const GROUP_SEO_MANAGEMENT = 'SEO & beheer';

    public const GROUP_TRACKING = 'Tracking';

    /**
     * @return array<int, string>
     */
    public static function orderedGroups(): array
    {
        return [
            self::GROUP_CONTENT,
            self::GROUP_STRUCTURE,
            self::GROUP_MEDIA,
            self::GROUP_DOWNLOADS,
            self::GROUP_SEO_MANAGEMENT,
            self::GROUP_TRACKING,
        ];
    }
}
