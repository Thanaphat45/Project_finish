<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Database-portable SQL fragments (MySQL locally, PostgreSQL on Supabase).
 */
class Sql
{
    /** Age in whole years from the elderly "Birthday" column. */
    public static function age(): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? 'EXTRACT(YEAR FROM AGE(CURRENT_DATE, "Birthday"))'
            : 'TIMESTAMPDIFF(YEAR, Birthday, CURDATE())';
    }
}
