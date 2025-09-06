<?php
/**
 * Simple Model Analytics Extension
 * 
 * Extends the existing model_profiles.json with historical performance data
 * Run this script periodically (every 30 minutes) to update analytics
 */

class SimpleAnalyticsExtender {
    private $profiles_file;
    private $regions;
    
    public function __construct() {
        $this->profiles_file = __DIR__ . '/cache/model_profiles.json';
        $this->regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
    }
    
    /**
     * Main function to update analytics data
     */
    public function updateAnalytics() {
        // Load current profiles
        $profiles = $this->loadProfiles();
        if (empty($profiles)) {
            error_log("No profiles found to update");
            return;
        }
        
        // Get current online models data
        $current_models = $this->getCurrentModelsData();
        if (empty($current_models)) {
            error_log("No current models data found");
            return;
        }
        
        $updated_count = 0;
        $now = time();
        
        // Update each profile with current data if model is online
        foreach ($profiles as &$profile) {
            $username = strtolower($profile['username']);
            
            if (isset($current_models[$username])) {
                $current = $current_models[$username];
                
                // Initialize analytics array if not exists
                if (!isset($profile['analytics'])) {
                    $profile['analytics'] = [
                        'viewer_history' => [],
                        'peak_viewers_ever' => 0,
                        'total_snapshots' => 0,
                        'avg_viewers_30d' => 0,
                        'consistency_score' => 0,
                        'last_updated' => $now
                    ];
                }
                
                $analytics = &$profile['analytics'];
                
                // Add current snapshot to history
                $snapshot = [
                    'timestamp' => $now,
                    'viewers' => $current['num_users'],
                    'online_time' => $current['seconds_online'],
                    'show_type' => $current['current_show'],
                    'day_of_week' => date('w', $now), // 0=Sunday, 6=Saturday
                    'hour_of_day' => date('G', $now)  // 0-23
                ];
                $analytics['viewer_history'][] = $snapshot;
                
                // Keep only last 30 days of history (720 entries at 30min intervals)
                $analytics['viewer_history'] = array_slice($analytics['viewer_history'], -720);
                
                // Update peak viewers
                $analytics['peak_viewers_ever'] = max($analytics['peak_viewers_ever'], $current['num_users']);
                
                // Update counters
                $analytics['total_snapshots']++;
                
                // Calculate 30-day average
                if (count($analytics['viewer_history']) > 0) {
                    $recent_viewers = array_column($analytics['viewer_history'], 'viewers');
                    $analytics['avg_viewers_30d'] = round(array_sum($recent_viewers) / count($recent_viewers), 1);
                }
                
                // Calculate consistency (active snapshots / possible snapshots in 30 days)
                $days_ago_30 = $now - (30 * 24 * 3600);
                $recent_snapshots = array_filter($analytics['viewer_history'], function($h) use ($days_ago_30) {
                    return $h['timestamp'] > $days_ago_30;
                });
                
                // Maximum possible snapshots in 30 days (48 per day at 30min intervals)
                $max_possible = 30 * 48;
                $analytics['consistency_score'] = min(100, (count($recent_snapshots) / $max_possible) * 100);
                
                // Calculate weekly activity pattern (last 7 days)
                $analytics['weekly_pattern'] = $this->calculateWeeklyPattern($analytics['viewer_history'], $now);
                
                $analytics['last_updated'] = $now;
                $updated_count++;
            }
        }
        
        // Save updated profiles
        $this->saveProfiles($profiles);
        error_log("Updated analytics for {$updated_count} models at " . date('Y-m-d H:i:s'));
    }
    
    /**
     * Load model profiles
     */
    private function loadProfiles() {
        if (!file_exists($this->profiles_file)) {
            return [];
        }
        
        $json = file_get_contents($this->profiles_file);
        return json_decode($json, true) ?: [];
    }
    
    /**
     * Save model profiles
     */
    private function saveProfiles($profiles) {
        $json = json_encode($profiles, JSON_PRETTY_PRINT);
        file_put_contents($this->profiles_file, $json);
    }
    
    /**
     * Get current online models from all regions
     */
    private function getCurrentModelsData() {
        $all_models = [];
        
        foreach ($this->regions as $region) {
            $file = __DIR__ . "/cache/cams_{$region}.json";
            if (!file_exists($file)) continue;
            
            $json = json_decode(file_get_contents($file), true);
            if (!$json || !isset($json['results'])) continue;
            
            foreach ($json['results'] as $model) {
                $username = strtolower($model['username']);
                $all_models[$username] = [
                    'username' => $model['username'],
                    'num_users' => intval($model['num_users'] ?? 0),
                    'seconds_online' => intval($model['seconds_online'] ?? 0),
                    'current_show' => $model['current_show'] ?? 'public'
                ];
            }
        }
        
        return $all_models;
    }
    
    /**
     * Get enhanced analytics for a specific model
     */
    public function getModelAnalytics($username) {
        $profiles = $this->loadProfiles();
        $username_lower = strtolower($username);
        
        // Find the model profile
        $profile = null;
        foreach ($profiles as $p) {
            if (strtolower($p['username']) === $username_lower) {
                $profile = $p;
                break;
            }
        }
        
        if (!$profile || !isset($profile['analytics'])) {
            return null;
        }
        
        $analytics = $profile['analytics'];
        $now = time();
        
        // Calculate trends from recent data
        $recent_7d = array_filter($analytics['viewer_history'], function($h) use ($now) {
            return $h['timestamp'] > ($now - 7 * 24 * 3600);
        });
        
        $previous_7d = array_filter($analytics['viewer_history'], function($h) use ($now) {
            return $h['timestamp'] > ($now - 14 * 24 * 3600) && $h['timestamp'] <= ($now - 7 * 24 * 3600);
        });
        
        // Calculate trend
        $trend = 'stable';
        if (count($recent_7d) >= 10 && count($previous_7d) >= 10) {
            $recent_avg = array_sum(array_column($recent_7d, 'viewers')) / count($recent_7d);
            $previous_avg = array_sum(array_column($previous_7d, 'viewers')) / count($previous_7d);
            
            if ($previous_avg > 0) {
                $change = (($recent_avg - $previous_avg) / $previous_avg) * 100;
                if ($change > 15) $trend = 'rising';
                elseif ($change < -15) $trend = 'declining';
            }
        }
        
        // Generate chart data for last 7 days
        $chart_data = $this->generateChartData($analytics['viewer_history'], 7);
        
        return [
            'peak_viewers_ever' => $analytics['peak_viewers_ever'],
            'avg_viewers_30d' => $analytics['avg_viewers_30d'],
            'consistency_score' => round($analytics['consistency_score']),
            'total_snapshots' => $analytics['total_snapshots'],
            'trend' => $trend,
            'chart_data' => $chart_data,
            'weekly_pattern' => $analytics['weekly_pattern'] ?? null,
            'viewer_history' => $analytics['viewer_history'] ?? [],
            'last_updated' => $analytics['last_updated']
        ];
    }
    
    /**
     * Generate chart data for visualization
     */
    private function generateChartData($history, $days = 7) {
        $now = time();
        $start_time = $now - ($days * 24 * 3600);
        
        // Filter to requested time period
        $period_data = array_filter($history, function($h) use ($start_time) {
            return $h['timestamp'] > $start_time;
        });
        
        if (empty($period_data)) return null;
        
        // Group by day and calculate averages
        $daily_data = [];
        foreach ($period_data as $point) {
            $day = date('Y-m-d', $point['timestamp']);
            if (!isset($daily_data[$day])) {
                $daily_data[$day] = [];
            }
            $daily_data[$day][] = $point['viewers'];
        }
        
        $chart = [
            'labels' => [],
            'avg_viewers' => [],
            'peak_viewers' => []
        ];
        
        foreach ($daily_data as $day => $viewers) {
            $chart['labels'][] = date('M j', strtotime($day));
            $chart['avg_viewers'][] = round(array_sum($viewers) / count($viewers));
            $chart['peak_viewers'][] = max($viewers);
        }
        
        return $chart;
    }
    
    /**
     * Calculate weekly activity pattern for the model
     */
    private function calculateWeeklyPattern($history, $now) {
        $days_ago_7 = $now - (7 * 24 * 3600);
        $recent_week = array_filter($history, function($h) use ($days_ago_7) {
            return $h['timestamp'] > $days_ago_7;
        });
        
        if (empty($recent_week)) {
            return [
                'activity_score' => 0,
                'best_day' => null,
                'best_hour' => null,
                'activity_by_day' => array_fill(0, 7, 0),
                'activity_by_hour' => array_fill(0, 24, 0)
            ];
        }
        
        $day_counts = array_fill(0, 7, 0);
        $hour_counts = array_fill(0, 24, 0);
        
        foreach ($recent_week as $snapshot) {
            $day = $snapshot['day_of_week'] ?? date('w', $snapshot['timestamp']);
            $hour = $snapshot['hour_of_day'] ?? date('G', $snapshot['timestamp']);
            
            $day_counts[$day]++;
            $hour_counts[$hour]++;
        }
        
        // Find best day and hour
        $best_day = array_search(max($day_counts), $day_counts);
        $best_hour = array_search(max($hour_counts), $hour_counts);
        
        // Calculate activity score (percentage of time online in last 7 days)
        $possible_snapshots = 7 * 48; // 48 snapshots per day for 7 days
        $activity_score = min(100, (count($recent_week) / $possible_snapshots) * 100);
        
        return [
            'activity_score' => round($activity_score, 1),
            'best_day' => $best_day,
            'best_hour' => $best_hour,
            'activity_by_day' => $day_counts,
            'activity_by_hour' => $hour_counts,
            'total_sessions' => count($recent_week)
        ];
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $extender = new SimpleAnalyticsExtender();
    $extender->updateAnalytics();
}
?>