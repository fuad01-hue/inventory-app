<?php
// Helper untuk validasi input

function validate_int($value, $min = null, $max = null) {
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    if ($filtered === false) return false;
    if ($min !== null && $filtered < $min) return false;
    if ($max !== null && $filtered > $max) return false;
    return $filtered;
}

function validate_float($value, $min = null) {
    $filtered = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($filtered === false) return false;
    if ($min !== null && $filtered < $min) return false;
    return $filtered;
}