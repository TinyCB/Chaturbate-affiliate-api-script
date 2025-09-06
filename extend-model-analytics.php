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
                        'low_viewers_ever' => PHP_INT_MAX,
                        'total_snapshots' => 0,
                        'avg_viewers_30d' => 0,
                        'consistency_score' => 0,
                        'goals' => [
                            'daily_viewer_goal' => 100,
                            'monthly_goal_progress' => 0,
                            'goal_achievements' => [],
                            'streak_days' => 0,
                            'best_streak' => 0
                        ],
                        'performance_milestones' => [
                            'first_100_viewers' => null,
                            'first_500_viewers' => null,
                            'first_1000_viewers' => null,
                            'most_productive_day' => null
                        ],
                        'last_updated' => $now
                    ];
                }
                
                $analytics = &$profile['analytics'];
                
                // Parse goal information from room subject
                $goal_info = $this->parseGoalFromSubject($current['room_subject'] ?? '');
                
                
                // Add current snapshot to history
                $snapshot = [
                    'timestamp' => $now,
                    'viewers' => $current['num_users'],
                    'online_time' => $current['seconds_online'],
                    'show_type' => $current['current_show'],
                    'day_of_week' => date('w', $now), // 0=Sunday, 6=Saturday
                    'hour_of_day' => date('G', $now),  // 0-23
                    'goal_info' => $goal_info
                ];
                $analytics['viewer_history'][] = $snapshot;
                
                // Keep only last 30 days of history (720 entries at 30min intervals)
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
                
                // Calculate consistency using session-based approach
                $days_ago_30 = $now - (30 * 24 * 3600);
                $recent_snapshots = array_filter($analytics['viewer_history'], function($h) use ($days_ago_30) {
                    return $h['timestamp'] > $days_ago_30;
                });
                
                if (count($recent_snapshots) > 0) {
                    // Calculate total online time in last 30 days
                    $sessions = $this->calculateSessions($recent_snapshots);
                    $total_online_time = 0;
                    foreach ($sessions as $session) {
                        $total_online_time += ($session['end_time'] - $session['start_time']);
                    }
                    
                    // Calculate consistency as percentage of time online (reasonable expectation: 4-8 hours/day)
                    $total_possible_time = 30 * 6 * 3600; // 6 hours per day for 30 days (reasonable streaming schedule)
                    $analytics['consistency_score'] = min(100, ($total_online_time / $total_possible_time) * 100);
                } else {
                    $analytics['consistency_score'] = 0;
                }
                
                // Calculate weekly activity pattern (last 7 days)
                $analytics['weekly_pattern'] = $this->calculateWeeklyPattern($analytics['viewer_history'], $now);
                
                // Update actual goal tracking (from room subjects)
                $this->updateActualGoalTracking($analytics, $goal_info, $now);
                
                // Update goal tracking
                $this->updateGoalTracking($analytics, $current['num_users'], $now);
                
                // Update performance milestones
                $this->updatePerformanceMilestones($analytics, $current['num_users'], $now);
                
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
                    'current_show' => $model['current_show'] ?? 'public',
                    'room_subject' => $model['room_subject'] ?? ''
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
            'low_viewers_ever' => $analytics['low_viewers_ever'] ?? 0,
            'avg_viewers_30d' => $analytics['avg_viewers_30d'],
            'consistency_score' => round($analytics['consistency_score']),
            'total_snapshots' => $analytics['total_snapshots'],
            'trend' => $trend,
            'chart_data' => $chart_data,
            'weekly_pattern' => $analytics['weekly_pattern'] ?? null,
            'viewer_history' => $analytics['viewer_history'] ?? [],
            'goals' => $analytics['goals'] ?? null,
            'performance_milestones' => $analytics['performance_milestones'] ?? null,
            'actual_goals' => $analytics['actual_goals'] ?? null,
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
     * Calculate sessions from snapshots
     */
    private function calculateSessions($snapshots) {
        if (empty($snapshots)) return [];
        
        // Sort by timestamp
        usort($snapshots, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        
        $sessions = [];
        $current_session = null;
        $session_gap_threshold = 3600; // 1 hour gap = new session
        
        foreach ($snapshots as $snapshot) {
            if (!$current_session || ($snapshot['timestamp'] - $current_session['end_time']) > $session_gap_threshold) {
                // Start new session
                if ($current_session) {
                    $sessions[] = $current_session;
                }
                $current_session = [
                    'start_time' => $snapshot['timestamp'],
                    'end_time' => $snapshot['timestamp'],
                    'snapshots' => [$snapshot]
                ];
            } else {
                // Continue current session
                $current_session['end_time'] = $snapshot['timestamp'];
                $current_session['snapshots'][] = $snapshot;
            }
        }
        
        // Don't forget the last session
        if ($current_session) {
            $sessions[] = $current_session;
        }
        
        return $sessions;
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
                'activity_by_hour' => array_fill(0, 24, 0),
                'total_sessions' => 0,
                'avg_session_length' => 0
            ];
        }
        
        // Sort by timestamp
        usort($recent_week, function($a, $b) {
            return $a['timestamp'] <=> $b['timestamp'];
        });
        
        // Group consecutive snapshots into sessions (gaps > 60 minutes = new session)
        $sessions = [];
        $current_session = null;
        $session_gap_threshold = 3600; // 1 hour gap = new session
        
        foreach ($recent_week as $snapshot) {
            if (!$current_session || ($snapshot['timestamp'] - $current_session['end_time']) > $session_gap_threshold) {
                // Start new session
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
                // Continue current session
                $current_session['end_time'] = $snapshot['timestamp'];
                $current_session['snapshots'][] = $snapshot;
            }
        }
        
        // Don't forget the last session
        if ($current_session) {
            $sessions[] = $current_session;
        }
        
        // Count sessions by day and hour
        $day_counts = array_fill(0, 7, 0);
        $hour_counts = array_fill(0, 24, 0);
        $total_session_time = 0;
        
        foreach ($sessions as $session) {
            $day_counts[$session['day_of_week']]++;
            $hour_counts[$session['start_hour']]++;
            $total_session_time += ($session['end_time'] - $session['start_time']);
        }
        
        // Find best day and hour
        $best_day = array_search(max($day_counts), $day_counts);
        $best_hour = array_search(max($hour_counts), $hour_counts);
        
        // Calculate activity score (total time online / total possible time in 7 days)
        $total_possible_time = 7 * 24 * 3600; // 7 days in seconds
        $activity_score = min(100, ($total_session_time / $total_possible_time) * 100);
        
        // Average session length
        $avg_session_length = count($sessions) > 0 ? ($total_session_time / count($sessions)) : 0;
        
        return [
            'activity_score' => round($activity_score, 1),
            'best_day' => $best_day,
            'best_hour' => $best_hour,
            'activity_by_day' => $day_counts,
            'activity_by_hour' => $hour_counts,
            'total_sessions' => count($sessions),
            'avg_session_length' => round($avg_session_length / 3600, 1), // Convert to hours
            'total_online_hours' => round($total_session_time / 3600, 1)
        ];
    }
    
    /**
     * Update goal tracking and streaks
     */
    private function updateGoalTracking(&$analytics, $current_viewers, $now) {
        // Initialize goals if not exists
        if (!isset($analytics['goals'])) {
            $analytics['goals'] = [
                'daily_viewer_goal' => 100,
                'monthly_goal_progress' => 0,
                'goal_achievements' => [],
                'streak_days' => 0,
                'best_streak' => 0
            ];
        }
        
        $goals = &$analytics['goals'];
        $today = date('Y-m-d', $now);
        
        // Check if daily goal was met
        if ($current_viewers >= $goals['daily_viewer_goal']) {
            $last_achievement = end($goals['goal_achievements']);
            
            // Only record once per day
            if (!$last_achievement || $last_achievement['date'] !== $today) {
                $goals['goal_achievements'][] = [
                    'date' => $today,
                    'viewers' => $current_viewers,
                    'goal' => $goals['daily_viewer_goal'],
                    'timestamp' => $now
                ];
                
                // Update streak
                if ($last_achievement && $last_achievement['date'] === date('Y-m-d', $now - 86400)) {
                    $goals['streak_days']++;
                } else {
                    $goals['streak_days'] = 1;
                }
                
                $goals['best_streak'] = max($goals['best_streak'], $goals['streak_days']);
            }
        }
        
        // Calculate monthly progress
        $this_month = date('Y-m', $now);
        $monthly_achievements = array_filter($goals['goal_achievements'], function($achievement) use ($this_month) {
            return strpos($achievement['date'], $this_month) === 0;
        });
        $goals['monthly_goal_progress'] = count($monthly_achievements);
        
        // Keep only last 60 days of achievements
        $cutoff_date = date('Y-m-d', $now - (60 * 86400));
        $goals['goal_achievements'] = array_filter($goals['goal_achievements'], function($achievement) use ($cutoff_date) {
            return $achievement['date'] >= $cutoff_date;
        });
    }
    
    /**
     * Update performance milestones
     */
    private function updatePerformanceMilestones(&$analytics, $current_viewers, $now) {
        if (!isset($analytics['performance_milestones'])) {
            $analytics['performance_milestones'] = [
                'first_100_viewers' => null,
                'first_500_viewers' => null,
                'first_1000_viewers' => null,
                'most_productive_day' => null
            ];
        }
        
        $milestones = &$analytics['performance_milestones'];
        
        // Check milestones
        if ($current_viewers >= 100 && !$milestones['first_100_viewers']) {
            $milestones['first_100_viewers'] = date('Y-m-d H:i:s', $now);
        }
        
        if ($current_viewers >= 500 && !$milestones['first_500_viewers']) {
            $milestones['first_500_viewers'] = date('Y-m-d H:i:s', $now);
        }
        
        if ($current_viewers >= 1000 && !$milestones['first_1000_viewers']) {
            $milestones['first_1000_viewers'] = date('Y-m-d H:i:s', $now);
        }
        
        // Update most productive day
        if (!$milestones['most_productive_day'] || 
            $current_viewers > $milestones['most_productive_day']['viewers']) {
            $milestones['most_productive_day'] = [
                'date' => date('Y-m-d', $now),
                'viewers' => $current_viewers,
                'timestamp' => $now
            ];
        }
    }
    
    /**
     * Parse goal information from room subject
     */
    private function parseGoalFromSubject($subject) {
        if (empty($subject)) {
            return null;
        }
        
        $goal_info = [
            'raw_subject' => $subject,
            'has_goal' => false,
            'goal_text' => null,
            'tokens_remaining' => null,
            'goal_type' => null
        ];
        
        // Common goal patterns
        $patterns = [
            // "Goal: something [123 tokens left]"
            '/Goal:\s*([^\[\n]+)\s*\[(\d+)\s*tokens?\s*(left|remaining)/i' => ['text' => 1, 'tokens' => 2],
            // "Goal: something [123 tokens remaining]"
            '/Goal:\s*([^\[\n]+)\s*\[(\d+)\s*tokens?\s*remaining/i' => ['text' => 1, 'tokens' => 2],
            // "something [123 tokens remaining]"
            '/([^\[\n]+)\s*\[(\d+)\s*tokens?\s*(left|remaining)/i' => ['text' => 1, 'tokens' => 2],
            // "Current Goal: something at 999 tokens"
            '/Current Goal:\s*([^\n]+)\s*at\s*(\d+)\s*tokens/i' => ['text' => 1, 'tokens' => 2],
            // "something @ 999 tokens" or "something @999 tokens"
            '/([^@\n]+)\s*@\s*(\d+)\s*tokens/i' => ['text' => 1, 'tokens' => 2],
            // "Multi Goal: something [123 tokens left]"
            '/Multi Goal:\s*([^\[\n]+)\s*\[(\d+)\s*tokens?\s*(left|remaining)/i' => ['text' => 1, 'tokens' => 2]
        ];
        
        foreach ($patterns as $pattern => $groups) {
            if (preg_match($pattern, $subject, $matches)) {
                $goal_info['has_goal'] = true;
                $goal_info['goal_text'] = trim($matches[$groups['text']]);
                $goal_info['tokens_remaining'] = intval($matches[$groups['tokens']]);
                
                // Determine goal type based on keywords
                $goal_text_lower = strtolower($goal_info['goal_text']);
                if (strpos($goal_text_lower, 'cum') !== false) {
                    $goal_info['goal_type'] = 'cumshow';
                } elseif (strpos($goal_text_lower, 'naked') !== false || strpos($goal_text_lower, 'nude') !== false) {
                    $goal_info['goal_type'] = 'naked';
                } elseif (strpos($goal_text_lower, 'squirt') !== false) {
                    $goal_info['goal_type'] = 'squirt';
                } elseif (strpos($goal_text_lower, 'bra') !== false || strpos($goal_text_lower, 'tits') !== false) {
                    $goal_info['goal_type'] = 'topless';
                } else {
                    $goal_info['goal_type'] = 'other';
                }
                break;
            }
        }
        
        return $goal_info;
    }
    
    /**
     * Track actual goals from room subjects
     */
    private function updateActualGoalTracking(&$analytics, $goal_info, $now) {
        // Initialize goal tracking if not exists
        if (!isset($analytics['actual_goals'])) {
            $analytics['actual_goals'] = [
                'current_goal' => null,
                'goal_history' => [],
                'completed_goals' => [],
                'goal_stats' => [
                    'total_goals_completed' => 0,
                    'total_tokens_reached' => 0,
                    'avg_goal_completion_time' => 0,
                    'most_popular_goal_type' => null,
                    'completion_rate' => 0,
                    'avg_tokens_per_minute' => 0,
                    'peak_tipping_hour' => null,
                    'goal_difficulty_score' => 0,
                    'consistency_rating' => 'new'
                ]
            ];
        }
        
        $actual_goals = &$analytics['actual_goals'];
        $current_goal = $actual_goals['current_goal'];
        
        if ($goal_info && $goal_info['has_goal']) {
            // Check if this is a new goal or goal completion
            if ($current_goal) {
                // Same goal text but fewer tokens = progress
                if ($current_goal['goal_text'] === $goal_info['goal_text']) {
                    if ($goal_info['tokens_remaining'] < $current_goal['tokens_remaining']) {
                        // Goal progress - update current goal
                        $time_elapsed = $now - $current_goal['started_at'];
                        $tokens_collected = $current_goal['initial_tokens'] - $goal_info['tokens_remaining'];
                        $progress_percent = $tokens_collected / $current_goal['initial_tokens'] * 100;
                        
                        // Calculate token velocity (tokens per minute)
                        $velocity = $time_elapsed > 0 ? ($tokens_collected / ($time_elapsed / 60)) : 0;
                        
                        $actual_goals['current_goal'] = array_merge($goal_info, [
                            'started_at' => $current_goal['started_at'],
                            'last_seen_at' => $now,
                            'initial_tokens' => $current_goal['initial_tokens'] ?? $current_goal['tokens_remaining'],
                            'progress' => $progress_percent,
                            'tokens_collected' => $tokens_collected,
                            'time_elapsed' => $time_elapsed,
                            'token_velocity' => round($velocity, 2),
                            'estimated_completion' => $velocity > 0 ? $now + ($goal_info['tokens_remaining'] / $velocity * 60) : null
                        ]);
                    }
                    
                    // Goal completed (0 tokens or very close)
                    if ($goal_info['tokens_remaining'] <= 5 && $current_goal['tokens_remaining'] > 5) {
                        $completion_time = $now - $current_goal['started_at'];
                        $tokens_collected = $current_goal['initial_tokens'] - $goal_info['tokens_remaining'];
                        $final_velocity = $completion_time > 0 ? ($tokens_collected / ($completion_time / 60)) : 0;
                        
                        $completed_goal = array_merge($current_goal, [
                            'completed_at' => $now,
                            'completion_time_seconds' => $completion_time,
                            'tokens_collected' => $tokens_collected,
                            'final_velocity' => round($final_velocity, 2),
                            'completion_hour' => date('G', $now),
                            'completion_day' => date('w', $now),
                            'difficulty_score' => $this->calculateGoalDifficulty($current_goal, $completion_time),
                            'success_factors' => $this->analyzeSuccessFactors($current_goal, $completion_time, $final_velocity)
                        ]);
                        
                        $actual_goals['completed_goals'][] = $completed_goal;
                        $actual_goals['goal_stats']['total_goals_completed']++;
                        $actual_goals['goal_stats']['total_tokens_reached'] += $tokens_collected;
                        
                        // Clear current goal
                        $actual_goals['current_goal'] = null;
                    }
                } else {
                    // Different goal = new goal started
                    $actual_goals['current_goal'] = array_merge($goal_info, [
                        'started_at' => $now,
                        'last_seen_at' => $now,
                        'initial_tokens' => $goal_info['tokens_remaining']
                    ]);
                }
            } else {
                // No current goal, this is a new goal
                $actual_goals['current_goal'] = array_merge($goal_info, [
                    'started_at' => $now,
                    'last_seen_at' => $now,
                    'initial_tokens' => $goal_info['tokens_remaining']
                ]);
            }
        } else {
            // No goal in subject - goal might be completed or removed
            if ($current_goal) {
                // Mark as completed/abandoned after some time
                if ($now - $current_goal['last_seen_at'] > 1800) { // 30 minutes
                    $actual_goals['current_goal'] = null;
                }
            }
        }
        
        // Keep only last 50 completed goals
        if (count($actual_goals['completed_goals']) > 50) {
            $actual_goals['completed_goals'] = array_slice($actual_goals['completed_goals'], -50);
        }
        
        // Update comprehensive stats
        $this->updateComprehensiveGoalStats($actual_goals, $now);
    }
    
    /**
     * Calculate goal difficulty based on tokens and completion time
     */
    private function calculateGoalDifficulty($goal, $completion_time) {
        $tokens = $goal['initial_tokens'] ?? 0;
        $minutes = $completion_time / 60;
        
        // Base difficulty on token amount
        $token_difficulty = min(10, $tokens / 100); // 1000 tokens = difficulty 10
        
        // Adjust based on completion speed (faster = easier audience)
        $speed_factor = 1;
        if ($minutes > 0) {
            $tokens_per_minute = $tokens / $minutes;
            if ($tokens_per_minute > 50) $speed_factor = 0.7; // Easy crowd
            elseif ($tokens_per_minute < 10) $speed_factor = 1.3; // Tough crowd
        }
        
        return min(10, round($token_difficulty * $speed_factor, 1));
    }
    
    /**
     * Analyze what made a goal successful
     */
    private function analyzeSuccessFactors($goal, $completion_time, $velocity) {
        $factors = [];
        $minutes = $completion_time / 60;
        
        if ($minutes < 30) {
            $factors[] = 'quick_completion';
        } elseif ($minutes > 120) {
            $factors[] = 'marathon_goal';
        }
        
        if ($velocity > 30) {
            $factors[] = 'high_tip_rate';
        } elseif ($velocity < 5) {
            $factors[] = 'steady_support';
        }
        
        $goal_text_lower = strtolower($goal['goal_text']);
        if (strpos($goal_text_lower, 'naked') !== false || strpos($goal_text_lower, 'nude') !== false) {
            $factors[] = 'nudity_goal';
        }
        if (strpos($goal_text_lower, 'cum') !== false || strpos($goal_text_lower, 'orgasm') !== false) {
            $factors[] = 'climax_goal';
        }
        
        $hour = date('G', $goal['started_at']);
        if ($hour >= 20 || $hour <= 2) {
            $factors[] = 'prime_time';
        }
        
        return $factors;
    }
    
    /**
     * Update comprehensive goal statistics
     */
    private function updateComprehensiveGoalStats(&$actual_goals, $now) {
        $completed = $actual_goals['completed_goals'];
        $stats = &$actual_goals['goal_stats'];
        
        if (empty($completed)) return;
        
        // Basic stats
        $completion_times = array_column($completed, 'completion_time_seconds');
        $stats['avg_goal_completion_time'] = array_sum($completion_times) / count($completion_times);
        
        $goal_types = array_column($completed, 'goal_type');
        $type_counts = array_count_values($goal_types);
        $stats['most_popular_goal_type'] = array_search(max($type_counts), $type_counts);
        
        // Enhanced stats
        $velocities = array_column($completed, 'final_velocity');
        $stats['avg_tokens_per_minute'] = array_sum($velocities) / count($velocities);
        
        // Peak tipping hour analysis
        $hour_tokens = [];
        foreach ($completed as $goal) {
            $hour = $goal['completion_hour'];
            if (!isset($hour_tokens[$hour])) $hour_tokens[$hour] = 0;
            $hour_tokens[$hour] += $goal['tokens_collected'];
        }
        if (!empty($hour_tokens)) {
            $stats['peak_tipping_hour'] = array_search(max($hour_tokens), $hour_tokens);
        }
        
        // Goal difficulty analysis
        $difficulties = array_column($completed, 'difficulty_score');
        $stats['goal_difficulty_score'] = array_sum($difficulties) / count($difficulties);
        
        // Completion rate (completed vs started goals estimate)
        $total_started = count($completed) + ($actual_goals['current_goal'] ? 1 : 0);
        $stats['completion_rate'] = $total_started > 0 ? (count($completed) / $total_started) * 100 : 0;
        
        // Consistency rating
        if (count($completed) < 5) {
            $stats['consistency_rating'] = 'new';
        } elseif ($stats['completion_rate'] > 80 && $stats['avg_tokens_per_minute'] > 15) {
            $stats['consistency_rating'] = 'excellent';
        } elseif ($stats['completion_rate'] > 60 && $stats['avg_tokens_per_minute'] > 10) {
            $stats['consistency_rating'] = 'good';  
        } elseif ($stats['completion_rate'] > 40) {
            $stats['consistency_rating'] = 'average';
        } else {
            $stats['consistency_rating'] = 'inconsistent';
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $extender = new SimpleAnalyticsExtender();
    $extender->updateAnalytics();
}
?>