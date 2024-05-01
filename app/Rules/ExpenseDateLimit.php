<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExpenseDateLimit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $weekAgoTs = now()->subWeek()->timestamp;

        $expenseDateTs = strtotime($value);

        if ($expenseDateTs < $weekAgoTs) {
            $fail('The date must not be greater than a week.');
        }
    }
}
