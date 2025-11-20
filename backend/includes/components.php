<?php
/**
 * includes/components.php
 * Minimal, safe renderStatBox used across roles.
 */

function renderStatBox($count, $label, $link) {
    $safeCount = is_numeric($count) ? (int)$count : htmlspecialchars((string)$count, ENT_QUOTES, 'UTF-8');
    $labelEsc  = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $linkEsc   = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    echo '<div class="stat-box">';
    echo '  <h2 class="stat-count">' . $safeCount . '</h2>';
    echo '  <p class="stat-label">' . $labelEsc . '</p>';
    echo '  <a class="details-btn" href="' . $linkEsc . '">View Details</a>';
    echo '</div>';
}
