<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared date filter for the staff result lists.
 *
 * The lists used to show only today's records when no dates were given, which
 * staff read as "the data is gone". Default is now everything; the page offers
 * quick ranges (today / 7 / 30 days) and custom dates.
 */
class DateRange
{
    public const QUICK = ['all' => 'ทั้งหมด', 'today' => 'วันนี้', '7' => '7 วันล่าสุด', '30' => '30 วันล่าสุด'];

    /** Applies the filter and returns the active range key: all|today|7|30|custom. */
    public static function apply(Builder $query, Request $request, string $column = 'created_at'): string
    {
        $from = $request->get('date_from');
        $to = $request->get('date_to');

        if ($from || $to) {
            if ($from) {
                $query->whereDate($column, '>=', $from);
            }
            if ($to) {
                $query->whereDate($column, '<=', $to);
            }
            return 'custom';
        }

        $range = (string) $request->get('range', 'all');
        if ($range === 'today') {
            $query->whereDate($column, now()->toDateString());
        } elseif ($range === '7' || $range === '30') {
            $query->whereDate($column, '>=', now()->subDays((int) $range - 1)->toDateString());
        } else {
            $range = 'all';
        }

        return $range;
    }
}
