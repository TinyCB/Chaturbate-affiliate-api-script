<?php
/**
 * Enhanced Model Analytics with Historical Data Integration
 * 
 * This file contains functions to integrate historical analytics data
 * from the extended model_profiles.json into the model page.
 */

require_once 'extend-model-analytics.php';

/**
 * Get comprehensive model analytics including historical trends
 */
function getEnhancedModelAnalytics($username, $days = 30) {
    $extender = new SimpleAnalyticsExtender();
    $historical = $extender->getModelAnalytics($username);
    
    // Get current insights (from existing function)
    $cache_dir = __DIR__ . '/cache/';
    $current = getModelInsights($username, $cache_dir);
    
    // If no historical data, return current only
    if (!$historical) {
        return [
            'current' => $current,
            'historical' => null,
            'trends' => ['viewer_trend' => 'stable', 'trend_indicators' => []],
            'performance_score' => ['score' => $current['activity_score'], 'grade' => 'N/A']
        ];
    }
    
    // Merge current and historical data
    return [
        'current' => $current,
        'historical' => $historical,
        'trends' => calculateTrends($historical),
        'performance_score' => calculatePerformanceScore($current, $historical)
    ];
}

/**
 * Calculate trend indicators from historical data
 */
function calculateTrends($historical) {
    $trends = [
        'viewer_trend' => $historical['trend'] ?? 'stable',
        'trend_indicators' => []
    ];
    
    // Add trend indicator message
    if ($trends['viewer_trend'] === 'rising') {
        $trends['trend_indicators'][] = [
            'type' => 'positive',
            'message' => 'Viewers trending upward this week 📈'
        ];
    } elseif ($trends['viewer_trend'] === 'declining') {
        $trends['trend_indicators'][] = [
            'type' => 'negative',
            'message' => 'Viewers trending downward this week 📉'
        ];
    } else {
        $trends['trend_indicators'][] = [
            'type' => 'info',
            'message' => 'Viewer count stable ➡️'
        ];
    }
    
    // Add consistency indicator
    if ($historical['consistency_score'] > 70) {
        $trends['trend_indicators'][] = [
            'type' => 'positive',
            'message' => 'Very consistent streaming schedule ✅'
        ];
    }
    
    return $trends;
}

/**
 * Calculate overall performance score (0-100)
 */
function calculatePerformanceScore($current, $historical) {
    $score = 0;
    $factors = [];
    
    // Current performance factors (50% weight)
    $factors['current_viewers'] = min(25, ($current['avg_viewers'] / 50) * 25); // Up to 25 points
    $factors['current_activity'] = min(25, $current['activity_score'] * 0.25); // Up to 25 points
    
    // Historical factors (50% weight)
    if ($historical) {
        $factors['peak_performance'] = min(25, ($historical['peak_viewers_ever'] / 100) * 25); // Up to 25 points
        $factors['consistency'] = min(25, $historical['consistency_score'] * 0.25); // Up to 25 points
    } else {
        // No historical data available
        $factors['peak_performance'] = min(25, ($current['peak_viewers'] / 100) * 25);
        $factors['consistency'] = min(25, $current['consistency_score'] * 0.25);
    }
    
    $score = array_sum($factors);
    return [
        'score' => min(100, round($score)),
        'breakdown' => $factors,
        'grade' => getPerformanceGrade($score)
    ];
}

/**
 * Convert numeric score to letter grade
 */
function getPerformanceGrade($score) {
    if ($score >= 85) return 'A';
    if ($score >= 75) return 'B';
    if ($score >= 65) return 'C'; 
    if ($score >= 50) return 'D';
    return 'F';
}

/**
 * Generate chart data for viewer trends
 */
function generateViewerTrendChart($historical, $days = 7) {
    if (!$historical || !isset($historical['chart_data'])) {
        return null;
    }
    
    return $historical['chart_data'];
}

/**
 * Get performance insights and recommendations
 */
function getPerformanceInsights($analytics) {
    $insights = [];
    $current = $analytics['current'];
    $historical = $analytics['historical'];
    $trends = $analytics['trends'];
    
    // Viewer insights
    if (!empty($historical['viewer_trends'])) {
        $recent_avg = array_sum(array_column(array_slice($historical['viewer_trends'], -7), 'avg_viewers')) / 7;
        $peak_ever = max(array_column($historical['viewer_trends'], 'peak_viewers'));
        
        if ($peak_ever > $recent_avg * 3) {
            $insights[] = [
                'type' => 'opportunity',
                'title' => 'Peak Performance Potential',
                'message' => "You've reached {$peak_ever} viewers before - that's " . round($peak_ever/$recent_avg, 1) . "x your recent average!"
            ];
        }
    }
    
    // Activity insights
    if (isset($trends['activity_trend']) && $trends['activity_trend'] === 'very_active') {
        $insights[] = [
            'type' => 'positive',
            'title' => 'Consistent Performer',
            'message' => 'Great online presence with regular long streaming sessions'
        ];
    }
    
    // Tag insights
    if (!empty($historical['tag_evolution'])) {
        $top_tags = array_slice($historical['tag_evolution'], 0, 3, true);
        $tag_names = array_keys($top_tags);
        $insights[] = [
            'type' => 'info',
            'title' => 'Popular Tags',
            'message' => 'Most used tags: #' . implode(', #', $tag_names)
        ];
    }
    
    return $insights;
}

/**
 * Format historical data for JavaScript charts
 */
function formatChartDataForJS($chart_data) {
    if (!$chart_data) return 'null';
    
    return json_encode([
        'labels' => $chart_data['labels'],
        'datasets' => [
            [
                'label' => 'Average Viewers',
                'data' => $chart_data['avg_viewers'],
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'fill' => true
            ],
            [
                'label' => 'Peak Viewers',
                'data' => $chart_data['peak_viewers'],
                'borderColor' => '#ef4444',
                'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                'fill' => false
            ]
        ]
    ]);
}

/**
 * Get time-based performance patterns
 */
function getTimeBasedPatterns($analytics) {
    $patterns = [
        'best_days' => [],
        'best_hours' => [],
        'recommendations' => []
    ];
    
    if (empty($analytics['historical']['viewer_trends'])) {
        return $patterns;
    }
    
    // Analyze day-of-week patterns
    $day_performance = [];
    foreach ($analytics['historical']['viewer_trends'] as $day) {
        $day_of_week = date('l', strtotime($day['date']));
        if (!isset($day_performance[$day_of_week])) {
            $day_performance[$day_of_week] = [];
        }
        $day_performance[$day_of_week][] = $day['avg_viewers'];
    }
    
    foreach ($day_performance as $day => $viewers) {
        $avg = array_sum($viewers) / count($viewers);
        $patterns['best_days'][] = [
            'day' => $day,
            'avg_viewers' => round($avg, 1)
        ];
    }
    
    // Sort by performance
    usort($patterns['best_days'], function($a, $b) {
        return $b['avg_viewers'] <=> $a['avg_viewers'];
    });
    
    return $patterns;
}
?>