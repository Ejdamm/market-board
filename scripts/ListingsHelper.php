<?php
if (isset($argc) && isset($argv[1]) && is_numeric($argv[1])) {
    $days = intval($argv[1]);
    $date = date('Y-m-d', strtotime("-$days days", strtotime("now")));
} else {
    exit("Usage: php `scripts/ListingsHelper.php DAYS`. All listings older than the number of days ago will be removed\n");
}

use MarketBoard\Listings;

require __DIR__ . '/../vendor/autoload.php';

$conf = include __DIR__ . '/../config/config.php';

$dbConf = $conf['settings']['db'];
$pdo = new PDO(
    $dbConf['adapter'] . ':host=' . $dbConf['host'] . ';dbname=' . $dbConf['name'],
    $dbConf['user'],
    $dbConf['pass']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$listings = new Listings($pdo);
echo "Number of removed listings: " . $listings->removeListingsOlderThan($date) . "\n";
