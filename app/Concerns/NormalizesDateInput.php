<?php

namespace App\Concerns;

use Carbon\Carbon;

trait NormalizesDateInput
{
    /**
     * Normalize a date input value to Y-m-d format.
     *
     * Handles empty strings (from HTML date inputs), locale-formatted dates
     * (e.g. DD/MM/YYYY from browser locale), and standard Y-m-d format.
     */
    protected function normalizeDate(?string $value): ?string
    {
        if (! $value || trim($value) === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception) {
            // Fallback: try generic parse
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return $value;
        }
    }
}
