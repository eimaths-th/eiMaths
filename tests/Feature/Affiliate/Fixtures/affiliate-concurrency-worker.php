<?php

$database = new PDO(
    sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        getenv('AFFILIATE_CONCURRENCY_DB_HOST'),
        getenv('AFFILIATE_CONCURRENCY_DB_PORT') ?: '3306',
        getenv('AFFILIATE_CONCURRENCY_DB_DATABASE'),
    ),
    getenv('AFFILIATE_CONCURRENCY_DB_USERNAME'),
    getenv('AFFILIATE_CONCURRENCY_DB_PASSWORD') ?: null,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC],
);

fwrite(STDOUT, "started\n");
fflush(STDOUT);

$database->beginTransaction();

$window_statement = $database->prepare(
    'SELECT id, unique_window_key FROM affiliate_visitor_windows WHERE affiliate_profile_id = :profile_id AND visitor_hash = :visitor_hash FOR UPDATE',
);
$window_statement->execute([
    'profile_id' => getenv('AFFILIATE_CONCURRENCY_PROFILE_ID'),
    'visitor_hash' => getenv('AFFILIATE_CONCURRENCY_VISITOR_HASH'),
]);
$window = $window_statement->fetch();
if (! is_array($window)) {
    throw new RuntimeException('The concurrency worker could not lock the visitor window.');
}

$now = gmdate('Y-m-d H:i:s');
$event_statement = $database->prepare(
    'INSERT INTO affiliate_click_events (affiliate_profile_id, affiliate_content_id, campaign_code, source_type, destination_target, visitor_hash, unique_window_key, is_unique, clicked_at, created_at) VALUES (:profile_id, NULL, NULL, :source_type, :destination_target, :visitor_hash, :window_key, 0, :clicked_at, :created_at)',
);
$event_statement->execute([
    'profile_id' => getenv('AFFILIATE_CONCURRENCY_PROFILE_ID'),
    'source_type' => 'direct',
    'destination_target' => '/',
    'visitor_hash' => getenv('AFFILIATE_CONCURRENCY_VISITOR_HASH'),
    'window_key' => $window['unique_window_key'],
    'clicked_at' => $now,
    'created_at' => $now,
]);

$metric_statement = $database->prepare(
    'UPDATE affiliate_click_daily_metrics SET total_clicks = total_clicks + 1, updated_at = :updated_at WHERE affiliate_profile_id = :profile_id AND metric_date = :metric_date AND dimension_key = :dimension_key',
);
$metric_statement->execute([
    'updated_at' => $now,
    'profile_id' => getenv('AFFILIATE_CONCURRENCY_PROFILE_ID'),
    'metric_date' => getenv('AFFILIATE_CONCURRENCY_METRIC_DATE'),
    'dimension_key' => 'direct',
]);

$window_update = $database->prepare('UPDATE affiliate_visitor_windows SET last_seen_at = :last_seen_at, updated_at = :updated_at WHERE id = :id');
$window_update->execute(['last_seen_at' => $now, 'updated_at' => $now, 'id' => $window['id']]);

$database->commit();
fwrite(STDOUT, "committed\n");
