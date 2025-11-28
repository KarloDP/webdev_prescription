<?php
require_once "../includes/db_connect.php";
require_once "../includes/dashboard_functions.php";
require_once "../includes/dashboard_cards.php";

$totals = getDashboardTotals($conn);

include "dashboard.html";
