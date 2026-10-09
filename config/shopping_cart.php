<?php

declare(strict_types=1);

return [
    'format_numbers' => env('SHOPPING_FORMAT_VALUES', default: false),

    'decimals' => env('SHOPPING_DECIMALS', 0),

    'dec_point' => env('SHOPPING_DEC_POINT', '.'),

    'thousands_sep' => env('SHOPPING_THOUSANDS_SEP', ','),
];
