<?php
// Helper untuk output encoding (XSS Protection)
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}