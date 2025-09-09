<?php
/**
 * Extended Model Analytics System v2.0
 * 
 * Uses separate analytics cache files for better performance and data safety
 */

// CLI execution check
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    if (php_sapi_name() !== 'cli') {
        header('HTTP/1.1 403 Forbidden');
        echo "This script can only be executed from command line for security reasons.";
        exit;
    }
}

require_once 'analytics-cache-manager.php';

class SimpleAnalyticsExtender {
    private $regions;
    private $base_dir;
    private $analytics_cache;
    
    public function __construct() {
        $this->base_dir = $this->detectBaseDirectory();
        $this->regions = ['northamerica', 'europe_russia', 'southamerica', 'asia', 'other'];
        $this->analytics_cache = new AnalyticsCacheManager();
    }
    
    private function detectBaseDirectory() {
        $script_path = __FILE__;
        $script_dir = dirname($script_path);
        
        if (is_dir($script_dir . '/cache')) {
            return $script_dir;
        }
        
        return dirname(__FILE__);
    }
    
    public function updateAnalytics() {
        $startup_log = "Analytics update started at " . date('Y-m-d H:i:s') . " [PID: " . getmypid() . "]";
        error_log($startup_log);
        
        $log_file = $this->base_dir . '/logs/analytics_cron.log';
        if (is_dir(dirname($log_file))) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $startup_log . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        
        $current_models = $this->getCurrentModelsData();
        if (empty($current_models)) {
            error_log("No current models data found");
            return;
        }
        
        $updated_count = 0;
        $now = time();
        
        // Update each online model's analytics
        foreach ($current_models as $username => $current) {
            // Load existing analytics or create new
            $analytics = $this->analytics_cache->getModelAnalytics($username);
            
            if (!$analytics) {
                $analytics = [
                    'username' => $current['username'],
                    'viewer_history' => [],
                    'peak_viewers_ever' => 0,
                    'avg_viewers_30d' => 0,
                    'low_viewers_ever' => 999999,
                    'consistency_score' => 0,
                    'trend' => 'stable',
                    'total_snapshots' => 0,
                    'first_seen' => $now,
                    'last_updated' => 0,
                    'weekly_pattern' => null,
                    'goal_tracking' => [],
                    'actual_goals' => ['goals' => [], 'current_goal' => null, 'completed_goals' => []],
                    'performance_milestones' => [
                        'first_100_viewers' => null,
                        'first_500_viewers' => null,
                        'first_1000_viewers' => null,
                        'most_productive_day' => null
                    ]
                ];
            }
            
            // Parse goal information from room subject
            $goal_info = $this->parseGoalFromSubject($current['room_subject'] ?? '');
            
            // Add current snapshot to history
            $snapshot = [
                'timestamp' => $now,
                'viewers' => $current['num_users'],
                'online_time' => $current['seconds_online'],
                'show_type' => $current['current_show'],
                'day_of_week' => date('w', $now),
                'hour_of_day' => date('G', $now),
                'goal_info' => $goal_info
            ];
            $analytics['viewer_history'][] = $snapshot;
            
            // Keep only last 30 days of history
            $analytics['viewer_history'] = array_slice($analytics['viewer_history'], -720);
            
            // Update peak and low viewers
            $analytics['peak_viewers_ever'] = max($analytics['peak_viewers_ever'], $current['num_users']);
            if (!isset($analytics['low_viewers_ever'])) {
                $analytics['low_viewers_ever'] = $current['num_users'];
            } else {
                $analytics['low_viewers_ever'] = min($analytics['low_viewers_ever'], $current['num_users']);
            }
            
            // Update counters
            $analytics['total_snapshots']++;
            
            // Calculate 30-day average
            if (count($analytics['viewer_history']) > 0) {
                $recent_viewers = array_column($analytics['viewer_history'], 'viewers');
                $analytics['avg_viewers_30d'] = round(array_sum($recent_viewers) / count($recent_viewers), 1);
            }
            
            // Calculate consistency
            $days_ago_30 = $now - (30 * 24 * 3600);
            $recent_snapshots = array_filter($analytics['viewer_history'], function($h) use ($days_ago_30) {
                return $h['timestamp'] > $days_ago_30;
            });
            
            if (count($recent_snapshots) > 0) {
                $sessions = $this->calculateSessions($recent_snapshots);
                $total_online_time = 0;
                foreach ($sessions as $session) {
                    $total_online_time += ($session['end_time'] - $session['start_time']);
                }
                $total_possible_time = 30 * 6 * 3600;
                $analytics['consistency_score'] = min(100, ($total_online_time / $total_possible_time) * 100);
            } else {
                $analytics['consistency_score'] = 0;
            }
            
            // Calculate weekly activity pattern
            $analytics['weekly_pattern'] = $this->calculateWeeklyPattern($analytics['viewer_history'], $now);
            
            // Update goal tracking
            $this->updateActualGoalTracking($analytics, $goal_info, $now);
            $this->updateGoalTracking($analytics, $current['num_users'], $now);
            $this->updatePerformanceMilestones($analytics, $current['num_users'], $now);
            
            $analytics['last_updated'] = $now;
            
            // Save individual analytics file
            if ($this->analytics_cache->saveModelAnalytics($username, $analytics)) {
                $updated_count++;
            }
        }
        
        $log_message = "Updated analytics for {$updated_count} models at " . date('Y-m-d H:i:s') . " [PID: " . getmypid() . "]";
        error_log($log_message);
        
        if (is_dir(dirname($log_file))) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $log_message . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
    
    public function getModelAnalytics($username) {
        $analytics = $this->analytics_cache->getModelAnalytics($username);
        
        if (!$analytics) {
            return null;
        }
        
        $now = time();
        
        // Calculate trends from recent data
        $recent_7d = array_filter($analytics['viewer_history'], function($h) use ($now) {
            return $h['timestamp'] > ($now - 7 * 24 * 3600);
        });
        
        $prev_7d = array_filter($analytics['viewer_history'], function($h) use ($now) {
            return $h['timestamp'] > ($now - 14 * 24 * 3600) && $h['timestamp'] <= ($now - 7 * 24 * 3600);
        });
        
        // Calculate trend
        if (count($recent_7d) > 0 && count($prev_7d) > 0) {
            $recent_avg = array_sum(array_column($recent_7d, 'viewers')) / count($recent_7d);
            $prev_avg = array_sum(array_column($prev_7d, 'viewers')) / count($prev_7d);
            
            $change_pct = (($recent_avg - $prev_avg) / $prev_avg) * 100;
            
            if ($change_pct > 10) {
                $analytics['trend'] = 'rising';
            } elseif ($change_pct < -10) {
                $analytics['trend'] = 'falling';
            } else {
                $analytics['trend'] = 'stable';
            }
        }
        
        return $analytics;
    }
    
    // Copy all the supporting methods from the original file
    private function getCurrentModelsData() {
        $all_models = [];
        
        foreach ($this->regions as $region) {
            $file = $this->base_dir . "/cache/cams_{$region}.json";
            if (!file_exists($file)) continue;
            
            $json = json_decode(file_get_contents($file), true);
            if (!$json || !isset($json['results'])) continue;
            
            foreach ($json['results'] as $model) {
                $username = strtolower($model['username']);
                $all_models[$username] = $model;
            }
        }
        
        return $all_models;
    }
    
    private function calculateSessions($snapshots) {
        if (empty($snapshots)) return [];
        
        usort($snapshots, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        
        $sessions = [];
        $current_session = null;
        $gap_threshold = 3600; // 1 hour
        
        foreach ($snapshots as $snapshot) {
            if (!$current_session || ($snapshot['timestamp'] - $current_session['end_time']) > $gap_threshold) {
                if ($current_session) {
                    $sessions[] = $current_session;
                }
                $current_session = [
                    'start_time' => $snapshot['timestamp'],
                    'end_time' => $snapshot['timestamp'],
                    'snapshots' => [$snapshot]
                ];
            } else {
                $current_session['end_time'] = $snapshot['timestamp'];
                $current_session['snapshots'][] = $snapshot;
            }
        }
        
        if ($current_session) {
            $sessions[] = $current_session;
        }
        
        return $sessions;
    }
    
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
                'activity_by_hour' => array_fill(0, 24, 0),
                'total_sessions' => 0,
                'avg_session_length' => 0
            ];
        }
        
        usort($recent_week, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        
        $sessions = [];
        $current_session = null;
        $session_gap_threshold = 3600;
        
        foreach ($recent_week as $snapshot) {
            if (!$current_session || ($snapshot['timestamp'] - $current_session['end_time']) > $session_gap_threshold) {
                if ($current_session) {
                    $sessions[] = $current_session;
                }
                $current_session = [
                    'start_time' => $snapshot['timestamp'],
                    'end_time' => $snapshot['timestamp'],
                    'snapshots' => [$snapshot],
                    'day_of_week' => date('w', $snapshot['timestamp']),
                    'start_hour' => date('G', $snapshot['timestamp'])
                ];
            } else {
                $current_session['end_time'] = $snapshot['timestamp'];
                $current_session['snapshots'][] = $snapshot;
            }
        }
        
        if ($current_session) {
            // For ongoing sessions, extend to current time
            $time_since_last = $now - $current_session['end_time'];
            if ($time_since_last < 7200) {
                $current_session['end_time'] = $now;
                $current_session['is_ongoing'] = true;
            }
            $sessions[] = $current_session;
        }
        
        // Count activity by day and hour
        $day_counts = array_fill(0, 7, 0);
        $hour_counts = array_fill(0, 24, 0);
        $total_session_time = 0;
        
        $activity_matrix = [];
        for ($d = 0; $d < 7; $d++) {
            $activity_matrix[$d] = array_fill(0, 24, 0);
        }
        
        foreach ($sessions as $session) {
            $day_counts[$session['day_of_week']]++;
            
            $session_duration = $this->calculateActualSessionDuration($session);
            $total_session_time += $session_duration;
            
            // Mark all hours during this session
            $start_time = $session['start_time'];
            $end_time = $session['end_time'];
            
            $start_hour = (int)date('G', $start_time);
            $end_hour = (int)date('G', $end_time);
            $start_day = (int)date('w', $start_time);
            $end_day = (int)date('w', $end_time);
            
            if ($start_day === $end_day) {
                // Same day session
                for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
                    $activity_matrix[$start_day][$hour]++;
                    $hour_counts[$hour]++;
                }
            } else {
                // Multi-day session
                $current_timestamp = $start_time;
                while ($current_timestamp <= $end_time) {
                    $hour = (int)date('G', $current_timestamp);
                    $day = (int)date('w', $current_timestamp);
                    
                    $activity_matrix[$day][$hour]++;
                    $hour_counts[$hour]++;
                    
                    $current_timestamp = strtotime('+1 hour', $current_timestamp);
                    
                    if (($current_timestamp - $start_time) > 86400) break;
                }
            }
        }
        
        $best_day = array_search(max($day_counts), $day_counts);
        $best_hour = array_search(max($hour_counts), $hour_counts);
        
        $total_possible_time = 7 * 24 * 3600;
        $activity_score = min(100, ($total_session_time / $total_possible_time) * 100);
        
        $avg_session_length = count($sessions) > 0 ? ($total_session_time / count($sessions)) : 0;
        
        return [
            'activity_score' => round($activity_score, 1),
            'best_day' => $best_day,
            'best_hour' => $best_hour,
            'activity_by_day' => $day_counts,
            'activity_by_hour' => $hour_counts,
            'activity_matrix' => $activity_matrix,
            'total_sessions' => count($sessions),
            'avg_session_length' => round($avg_session_length / 3600, 1),
            'total_online_hours' => round($total_session_time / 3600, 1)
        ];
    }
    
    private function calculateActualSessionDuration($session) {
        if (empty($session['snapshots'])) {
            return $session['end_time'] - $session['start_time'];
        }
        
        $snapshots = $session['snapshots'];
        $max_online_time = 0;
        
        foreach ($snapshots as $snapshot) {
            if (isset($snapshot['online_time'])) {
                $max_online_time = max($max_online_time, $snapshot['online_time']);
            }
        }
        
        return $max_online_time > 0 ? $max_online_time : ($session['end_time'] - $session['start_time']);
    }
    
    // Parse goal information from room subject
    private function parseGoalFromSubject($subject) {
        if (empty($subject)) {
            return null;
        }
        
        $goal_text = '';
        $tokens_remaining = 0;
        $matched = false;
        
        // Pattern 1: GOAL: text [tokens remaining]
        if (preg_match('/GOAL:\s*([^[]+?)\s*\[(\d+)\s*tokens?\s+(remaining|left)\]/i', $subject, $matches)) {
            $goal_text = trim($matches[1]);
            $tokens_remaining = (int)$matches[2];
            $matched = true;
        }
        // Pattern 2: text [tokens remaining] (without GOAL: prefix)
        elseif (preg_match('/^([^[]+?)\s*\[(\d+)\s*tokens?\s+(remaining|left)\]/i', $subject, $matches)) {
            $goal_text = trim($matches[1]);
            $tokens_remaining = (int)$matches[2];
            $matched = true;
        }
        // Pattern 3: Current Goal: text [tokens remaining]
        elseif (preg_match('/Current Goal:\s*([^[]+?)\s*\[(\d+)\s*tokens?\s+(remaining|left)\]/i', $subject, $matches)) {
            $goal_text = trim($matches[1]);
            $tokens_remaining = (int)$matches[2];
            $matched = true;
        }
        // Pattern 4: text -- tokens remaining
        elseif (preg_match('/^([^-]+?)\s*--\s*(\d+)\s*tokens?\s+(remaining|left)/i', $subject, $matches)) {
            $goal_text = trim($matches[1]);
            $tokens_remaining = (int)$matches[2];
            $matched = true;
        }
        // Pattern 5: Goal reached! or similar completion messages
        elseif (preg_match('/(goal\s*(reached|completed|done)|all\s*goals?\s*(completed|done|reached))/i', $subject)) {
            // Extract potential goal text before the completion message
            if (preg_match('/^([^-\[!]+?)\s*(?:-|!|$)/i', $subject, $matches)) {
                $goal_text = trim($matches[1]);
            } else {
                $goal_text = 'Goal completed';
            }
            $tokens_remaining = 0;
            $matched = true;
        }
        
        if (!$matched) {
            return null;
        }
        
        // Clean up goal text - remove hashtags and extra formatting
        $goal_text = preg_replace('/\s*#\w+.*$/', '', $goal_text);
        $goal_text = trim($goal_text);
        
        if (empty($goal_text)) {
            return null;
        }
        
        // Extract additional goal info
        $goal_type = 'other';
        
        // Classify goal type based on keywords
        $goal_lower = strtolower($goal_text);
        if (preg_match('/\b(naked?|nude|strip|undress|clothes?\s+off)\b/', $goal_lower)) {
            $goal_type = 'nudity';
        } elseif (preg_match('/\b(cum|orgasm|climax|finish)\b/', $goal_lower)) {
            $goal_type = 'climax';
        } elseif (preg_match('/\b(dance|twerk|wiggle|move)\b/', $goal_lower)) {
            $goal_type = 'dance';
        } elseif (preg_match('/\b(show|flash|reveal)\b/', $goal_lower)) {
            $goal_type = 'show';
        } elseif (preg_match('/\b(dildo|toy|vibe|vibrator|fuck|ride)\b/', $goal_lower)) {
            $goal_type = 'toy';
        } elseif (preg_match('/\b(finger|touch|rub|play)\b/', $goal_lower)) {
            $goal_type = 'play';
        } elseif (preg_match('/\b(squirt|wet|drip)\b/', $goal_lower)) {
            $goal_type = 'squirt';
        }
        
        return [
            'raw_subject' => $subject,
            'has_goal' => true,
            'goal_text' => $goal_text,
            'tokens_remaining' => $tokens_remaining,
            'goal_type' => $goal_type
        ];
    }
    
    private function updateActualGoalTracking(&$analytics, $goal_info, $now) {
        if (!isset($analytics['actual_goals'])) {
            $analytics['actual_goals'] = [
                'current_goal' => null,
                'goal_history' => [],
                'completed_goals' => [],
                'goal_stats' => [
                    'total_goals_completed' => 0,
                    'total_tokens_reached' => 0,
                    'avg_goal_completion_time' => 0,
                    'most_popular_goal_type' => 'other',
                    'completion_rate' => 0,
                    'avg_tokens_per_minute' => 0,
                    'peak_tipping_hour' => null,
                    'goal_difficulty_score' => 0,
                    'consistency_rating' => 'unknown',
                    'performance_trend' => 'insufficient_data'
                ]
            ];
        }
        
        $current_goal = &$analytics['actual_goals']['current_goal'];
        
        if ($goal_info) {
            if (!$current_goal || $current_goal['goal_text'] !== $goal_info['goal_text']) {
                // Check if we completed the previous goal
                if ($current_goal && $current_goal['tokens_remaining'] > 0 && $goal_info['tokens_remaining'] === 0) {
                    $this->completeGoal($analytics, $current_goal, $now);
                } elseif ($current_goal && $current_goal['goal_text'] !== $goal_info['goal_text']) {
                    // Check if goal was effectively completed (95%+ done) before marking as incomplete
                    $progress = 0;
                    if ($current_goal['initial_tokens'] > 0) {
                        $progress = (($current_goal['initial_tokens'] - $current_goal['tokens_remaining']) / $current_goal['initial_tokens']) * 100;
                    }
                    
                    if ($progress >= 80) {
                        // Goal was effectively completed - treat as success
                        $this->completeGoal($analytics, $current_goal, $now);
                    } else {
                        // Goal changed without significant completion - mark as incomplete
                        $current_goal['status'] = 'incomplete';
                        $analytics['actual_goals']['goal_history'][] = $current_goal;
                    }
                }
                
                // Start new goal or handle already completed goal
                if ($goal_info['tokens_remaining'] > 0) {
                    // New goal with tokens remaining
                    $current_goal = array_merge($goal_info, [
                        'started_at' => $now,
                        'last_seen_at' => $now,
                        'initial_tokens' => $goal_info['tokens_remaining'],
                        'progress' => 0,
                        'tokens_collected' => 0,
                        'time_elapsed' => 0,
                        'token_velocity' => 0,
                        'historical_velocity' => $this->calculateHistoricalVelocity($analytics),
                        'predicted_velocity' => 0,
                        'estimated_completion' => null,
                        'goal_classification' => $this->classifyGoal($goal_info),
                        'engagement_level' => 'unknown',
                        'completion_confidence' => 50
                    ]);
                } else {
                    // Goal is already completed (0 tokens remaining)
                    $current_goal = null;
                }
            } else {
                // Update existing goal
                if ($current_goal) {
                    $previous_tokens = $current_goal['tokens_remaining'];
                    $current_goal['tokens_remaining'] = $goal_info['tokens_remaining'];
                    $current_goal['last_seen_at'] = $now;
                    $current_goal['time_elapsed'] = $now - $current_goal['started_at'];
                    
                    // Calculate progress and velocity
                    $current_goal['tokens_collected'] = $current_goal['initial_tokens'] - $goal_info['tokens_remaining'];
                    $current_goal['progress'] = $current_goal['initial_tokens'] > 0 ? 
                        ($current_goal['tokens_collected'] / $current_goal['initial_tokens']) * 100 : 0;
                    
                    if ($current_goal['time_elapsed'] > 0) {
                        $current_goal['token_velocity'] = ($current_goal['tokens_collected'] / $current_goal['time_elapsed']) * 60; // per minute
                    }
                    
                    // Update prediction and confidence
                    $historical_velocity = $current_goal['historical_velocity'] ?? 0;
                    $current_goal['predicted_velocity'] = max($current_goal['token_velocity'] ?? 0, $historical_velocity * 0.1);
                    
                    if ($current_goal['predicted_velocity'] > 0) {
                        $remaining_time = $goal_info['tokens_remaining'] / ($current_goal['predicted_velocity'] / 60);
                        $current_goal['estimated_completion'] = $now + $remaining_time;
                        
                        // Calculate completion confidence
                        if (($current_goal['token_velocity'] ?? 0) > $historical_velocity * 0.5) {
                            $current_goal['completion_confidence'] = min(95, 70 + ($current_goal['progress'] * 0.25));
                        } else {
                            $current_goal['completion_confidence'] = max(20, 50 - (($now - $current_goal['started_at']) / 3600) * 10);
                        }
                        
                        // Update engagement level
                        if (($current_goal['token_velocity'] ?? 0) > $historical_velocity) {
                            $current_goal['engagement_level'] = 'high';
                        } elseif (($current_goal['token_velocity'] ?? 0) > $historical_velocity * 0.5) {
                            $current_goal['engagement_level'] = 'moderate';
                        } else {
                            $current_goal['engagement_level'] = 'low';
                        }
                    }
                    
                    // Check for completion
                    if ($goal_info['tokens_remaining'] === 0) {
                        $this->completeGoal($analytics, $current_goal, $now);
                        $current_goal = null;
                    }
                }
            }
        } else {
            // No current goal - mark previous as completed or incomplete
            if ($current_goal && $current_goal['tokens_remaining'] > 0) {
                // Check if goal was effectively completed (95%+ done) before marking as incomplete
                $progress = 0;
                if ($current_goal['initial_tokens'] > 0) {
                    $progress = (($current_goal['initial_tokens'] - $current_goal['tokens_remaining']) / $current_goal['initial_tokens']) * 100;
                }
                
                if ($progress >= 95) {
                    // Goal was effectively completed - treat as success
                    $this->completeGoal($analytics, $current_goal, $now);
                } else {
                    // Goal disappeared without significant completion - mark as incomplete
                    $current_goal['status'] = 'incomplete';
                    $current_goal['ended_at'] = $now;
                    $analytics['actual_goals']['goal_history'][] = $current_goal;
                }
                $current_goal = null;
            }
        }
        
        // Update overall goal statistics
        $this->updateGoalStats($analytics);
    }
    
    private function updateGoalTracking(&$analytics, $viewers, $now) {
        // Keep this for backward compatibility - can be expanded if needed
        if (!isset($analytics['goal_tracking'])) {
            $analytics['goal_tracking'] = [];
        }
        
        // Store basic viewer tracking
        $analytics['goal_tracking'][] = [
            'timestamp' => $now,
            'viewers' => $viewers,
            'hour' => date('G', $now)
        ];
        
        // Keep only last 100 tracking points
        $analytics['goal_tracking'] = array_slice($analytics['goal_tracking'], -100);
    }
    
    private function calculateHistoricalVelocity($analytics) {
        if (!isset($analytics['actual_goals']['completed_goals']) || empty($analytics['actual_goals']['completed_goals'])) {
            return 0;
        }
        
        $velocities = [];
        foreach ($analytics['actual_goals']['completed_goals'] as $goal) {
            if (isset($goal['final_velocity'])) {
                $velocities[] = $goal['final_velocity'];
            }
        }
        
        if (empty($velocities)) {
            return 0;
        }
        
        return array_sum($velocities) / count($velocities);
    }
    
    private function classifyGoal($goal_info) {
        $goal_text_lower = strtolower($goal_info['goal_text']);
        $tokens = $goal_info['tokens_remaining'];
        
        // Classify by token amount
        if ($tokens <= 50) {
            return 'quick_tip';
        } elseif ($tokens <= 200) {
            return 'tip_goal';
        } elseif ($tokens <= 500) {
            return 'show_goal';
        } else {
            return 'major_goal';
        }
    }
    
    private function completeGoal(&$analytics, &$goal, $now) {
        $completion_time = $now - $goal['started_at'];
        $final_velocity = 0;
        
        if ($completion_time > 0) {
            $final_velocity = ($goal['initial_tokens'] / $completion_time) * 60; // tokens per minute
        }
        
        $completed_goal = array_merge($goal, [
            'completed_at' => $now,
            'completion_time_seconds' => $completion_time,
            'tokens_collected' => $goal['initial_tokens'],
            'final_velocity' => $final_velocity,
            'completion_hour' => date('G', $now),
            'completion_day' => date('w', $now),
            'difficulty_score' => $this->calculateGoalDifficulty($goal),
            'success_factors' => $this->identifySuccessFactors($goal, $completion_time, $final_velocity)
        ]);
        
        $analytics['actual_goals']['completed_goals'][] = $completed_goal;
        
        // Keep only last 20 completed goals
        $analytics['actual_goals']['completed_goals'] = array_slice(
            $analytics['actual_goals']['completed_goals'], 
            -20
        );
    }
    
    private function calculateGoalDifficulty($goal) {
        $base_difficulty = 1.0;
        
        // Factor in token amount
        $tokens = $goal['initial_tokens'];
        if ($tokens > 500) $base_difficulty += 2.0;
        elseif ($tokens > 200) $base_difficulty += 1.0;
        elseif ($tokens > 100) $base_difficulty += 0.5;
        
        // Factor in goal type
        switch ($goal['goal_type']) {
            case 'climax':
                $base_difficulty += 1.5;
                break;
            case 'nudity':
                $base_difficulty += 1.0;
                break;
            case 'toy':
                $base_difficulty += 1.2;
                break;
            case 'show':
                $base_difficulty += 0.8;
                break;
        }
        
        return min(10.0, $base_difficulty);
    }
    
    private function identifySuccessFactors($goal, $completion_time, $final_velocity) {
        $factors = [];
        
        if ($completion_time < 600) { // Less than 10 minutes
            $factors[] = 'quick_completion';
        } elseif ($completion_time > 3600) { // More than 1 hour
            $factors[] = 'marathon_goal';
        }
        
        if ($final_velocity > 50) { // High tip rate
            $factors[] = 'high_tip_rate';
        } elseif ($final_velocity > 20) {
            $factors[] = 'steady_support';
        }
        
        // Goal type factors
        if ($goal['goal_type'] === 'nudity') {
            $factors[] = 'nudity_goal';
        } elseif ($goal['goal_type'] === 'climax') {
            $factors[] = 'climax_goal';
        }
        
        // Time-based factors
        if (isset($goal['completed_at'])) {
            $hour = (int)date('G', $goal['completed_at']);
            if ($hour >= 19 && $hour <= 23) { // Prime time
                $factors[] = 'prime_time';
            }
        }
        
        return $factors;
    }
    
    private function updateGoalStats(&$analytics) {
        $completed = $analytics['actual_goals']['completed_goals'];
        $stats = &$analytics['actual_goals']['goal_stats'];
        
        if (empty($completed)) {
            return;
        }
        
        $stats['total_goals_completed'] = count($completed);
        $stats['total_tokens_reached'] = array_sum(array_column($completed, 'tokens_collected'));
        
        $completion_times = array_column($completed, 'completion_time_seconds');
        $stats['avg_goal_completion_time'] = array_sum($completion_times) / count($completion_times);
        
        // Most popular goal type
        $types = array_column($completed, 'goal_type');
        $type_counts = array_count_values($types);
        arsort($type_counts);
        $stats['most_popular_goal_type'] = key($type_counts);
        
        // Completion rate (if we track incomplete goals too)
        $total_goals = count($completed) + count($analytics['actual_goals']['goal_history'] ?? []);
        $stats['completion_rate'] = $total_goals > 0 ? (count($completed) / $total_goals) * 100 : 100;
        
        // Average velocity
        $velocities = array_column($completed, 'final_velocity');
        $stats['avg_tokens_per_minute'] = array_sum($velocities) / count($velocities);
        
        // Peak tipping hour
        $hours = array_column($completed, 'completion_hour');
        $hour_counts = array_count_values($hours);
        arsort($hour_counts);
        $stats['peak_tipping_hour'] = count($hour_counts) > 0 ? (int)key($hour_counts) : null;
        
        // Goal difficulty score
        $difficulties = array_column($completed, 'difficulty_score');
        $stats['goal_difficulty_score'] = array_sum($difficulties) / count($difficulties);
        
        // Consistency rating
        if ($stats['completion_rate'] >= 80) {
            $stats['consistency_rating'] = 'excellent';
        } elseif ($stats['completion_rate'] >= 60) {
            $stats['consistency_rating'] = 'good';
        } elseif ($stats['completion_rate'] >= 40) {
            $stats['consistency_rating'] = 'fair';
        } else {
            $stats['consistency_rating'] = 'poor';
        }
    }
    
    private function updatePerformanceMilestones(&$analytics, $viewers, $now) {
        if ($viewers >= 100 && !$analytics['performance_milestones']['first_100_viewers']) {
            $analytics['performance_milestones']['first_100_viewers'] = date('Y-m-d H:i:s', $now);
        }
        
        if ($viewers >= 500 && !$analytics['performance_milestones']['first_500_viewers']) {
            $analytics['performance_milestones']['first_500_viewers'] = date('Y-m-d H:i:s', $now);
        }
        
        if ($viewers >= 1000 && !$analytics['performance_milestones']['first_1000_viewers']) {
            $analytics['performance_milestones']['first_1000_viewers'] = date('Y-m-d H:i:s', $now);
        }
    }
}

// CLI execution
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    $extender = new SimpleAnalyticsExtender();
    $extender->updateAnalytics();
}
?>