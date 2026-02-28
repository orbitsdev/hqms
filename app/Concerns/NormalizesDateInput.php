<?php

namespace App\Concerns;

use Carbon\Carbon;

trait NormalizesDateInput
{
    /**
     * Normalize a date input value to Y-m-d format.
     *
     * HTML date inputs should always send Y-m-d, but browser/Livewire re-renders
     * can sometimes send locale-formatted strings or empty strings.
     */
    protected function normalizeDate(?string $value): ?string
    {
        if (! $value || trim($value) === '') {
            return null;
        }

        // Already in Y-m-d format — pass through
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // Try common locale formats with STRICT parsing (no overflow)
        foreach (['d/m/Y', 'm/d/Y', 'd-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat("!{$format}", $value);
                $errors = $date::getLastErrors();

                if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                    continue;
                }

                // Sanity check: year must be reasonable (1900-current year)
                if ($date->year < 1900 || $date->year > (int) date('Y')) {
                    continue;
                }

                return $date->format('Y-m-d');
            } catch (\Exception) {
                continue;
            }
        }

        // Could not parse reliably — return null rather than risk corruption
        return null;
    }
}
