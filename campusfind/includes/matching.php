<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notifications.php';

function stringSimilarity($str1, $str2) {
    if (empty($str1) || empty($str2)) return 0;
    $str1 = strtolower(trim($str1));
    $str2 = strtolower(trim($str2));
    $common = ['the','a','an','and','or','but','for','nor','on','at','to','by','in','of','with'];
    $str1 = str_replace($common, '', $str1);
    $str2 = str_replace($common, '', $str2);
    $similar_text = 0;
    similar_text($str1, $str2, $similar_text);
    $levenshtein = levenshtein($str1, $str2);
    $max_len = max(strlen($str1), strlen($str2));
    $levenshtein_score = $max_len > 0 ? (1 - $levenshtein / $max_len) * 100 : 100;
    return ($similar_text * 0.6 + $levenshtein_score * 0.4);
}

function findMatchesForLost($lost_item_id, $threshold = 60) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE id = ?");
    $stmt->execute([$lost_item_id]);
    $lost = $stmt->fetch();
    if (!$lost) return [];
    
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE status IN ('open', 'matched') AND id NOT IN (SELECT found_item_id FROM match_log WHERE lost_item_id = ?)");
    $stmt->execute([$lost_item_id]);
    $found_items = $stmt->fetchAll();
    $matches = [];
    
    foreach ($found_items as $found) {
        $title_score = stringSimilarity($lost['title'], $found['title']);
        $category_score = ($lost['category'] === $found['category']) ? 100 : 50;
        $location_score = stringSimilarity($lost['location'], $found['location']);
        $description_score = stringSimilarity($lost['description'] ?? '', $found['description'] ?? '');
        $final_score = ($title_score * 0.4 + $category_score * 0.2 + $location_score * 0.25 + $description_score * 0.15);
        if ($lost['category'] === $found['category']) $final_score += 5;
        if (stringSimilarity($lost['location'], $found['location']) > 70) $final_score += 5;
        $final_score = min(100, $final_score);
        if ($final_score >= $threshold) {
            $matches[] = ['found_item' => $found, 'score' => round($final_score, 2)];
        }
    }
    usort($matches, function($a, $b) { return $b['score'] - $a['score']; });
    return $matches;
}

function findMatchesForFound($found_item_id, $threshold = 60) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
    $stmt->execute([$found_item_id]);
    $found = $stmt->fetch();
    if (!$found) return [];
    
    $stmt = $pdo->prepare("SELECT * FROM lost_items WHERE status IN ('open', 'matched') AND id NOT IN (SELECT lost_item_id FROM match_log WHERE found_item_id = ?)");
    $stmt->execute([$found_item_id]);
    $lost_items = $stmt->fetchAll();
    $matches = [];
    
    foreach ($lost_items as $lost) {
        $title_score = stringSimilarity($lost['title'], $found['title']);
        $category_score = ($lost['category'] === $found['category']) ? 100 : 50;
        $location_score = stringSimilarity($lost['location'], $found['location']);
        $description_score = stringSimilarity($lost['description'] ?? '', $found['description'] ?? '');
        $final_score = ($title_score * 0.4 + $category_score * 0.2 + $location_score * 0.25 + $description_score * 0.15);
        if ($lost['category'] === $found['category']) $final_score += 5;
        if (stringSimilarity($lost['location'], $found['location']) > 70) $final_score += 5;
        $final_score = min(100, $final_score);
        if ($final_score >= $threshold) {
            $matches[] = ['lost_item' => $lost, 'score' => round($final_score, 2)];
        }
    }
    usort($matches, function($a, $b) { return $b['score'] - $a['score']; });
    return $matches;
}