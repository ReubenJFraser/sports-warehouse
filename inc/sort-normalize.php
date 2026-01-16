<?php
// inc/sort-normalize.php
// Canonicalizes catalog sort values.
// Enforces Routing Invariant: sorting parameters must be explicit and canonical.

function sw_normalize_sort(?string $sort): string {
    static $allowed = [
        'relevance',
        'price_asc',
        'price_desc',
        'name_asc',
        'name_desc',
    ];

    return in_array($sort, $allowed, true)
        ? $sort
        : 'relevance';
}

