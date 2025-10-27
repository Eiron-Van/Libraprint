<?php
require_once __DIR__ . '/../../inc/auth_admin.php';
// ✅ Highlight search terms
function highlightTerms(string $text, string $search): string {
    if ($search === '' || $text === '') return htmlspecialchars($text);
    $words = preg_split('/\s+/', trim($search));
    $words = array_filter($words);
    array_unshift($words, $search);
    $words = array_unique($words);
    usort($words, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
    $escaped = array_map(fn($w) => preg_quote($w, '/'), $words);
    $pattern = '/(' . implode('|', $escaped) . ')/iu';
    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        if (preg_match($pattern, $part)) {
            $out .= '<mark class="search-highlight">' . htmlspecialchars($part) . '</mark>';
        } else {
            $out .= htmlspecialchars($part);
        }
    }
    return $out;
}

// ✅ Format MySQL datetime into readable date + time
function formatDateTime($datetime) {
    if (empty($datetime) || $datetime === "0000-00-00" || $datetime === "0000-00-00 00:00:00" || is_null($datetime)) {
        return '-';
    }

    $timestamp = strtotime($datetime);
    if ($timestamp === false) return '-';
    return date("F j, Y - g:i A", $timestamp);
}

// ✅ Compute readable duration from 2 datetime values
function computeDuration($borrowedDate, $returnedDate) {
    $borrowed = strtotime($borrowedDate);
    $returned = strtotime($returnedDate);

    if (!$borrowed || !$returned || $returned <= $borrowed) {
        return "-";
    }

    $seconds = $returned - $borrowed;
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($days > 0) {
        return $days . " day" . ($days > 1 ? "s" : "");
    } elseif ($hours > 0) {
        return $hours . " hour" . ($hours > 1 ? "s" : "");
    } elseif ($minutes > 0) {
        return $minutes . " minute" . ($minutes > 1 ? "s" : "");
    } else {
        return "Less than a minute";
    }
}