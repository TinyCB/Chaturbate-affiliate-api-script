<?php
/**
 * Modern Model Page - Complete Redesign
 * Sophisticated layout with full viewport utilization and elegant statistics display
 */

// Include all necessary functions and analytics
require_once 'model.php'; // Get all existing functions
require_once 'model-analytics-enhanced.php';

// For this demo, let's create the structure for a modern design
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($model['username']) ?> - <?= ucfirst($model['gender'] === 'f' ? 'Female' : ($model['gender'] === 'm' ? 'Male' : ($model['gender'] === 't' ? 'Trans' : 'Couple'))) ?> Live Cam | TinyCB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Modern Model Page Styles */
        :root {
            --primary-color: #808085;
            --accent-color: #6366f1;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--bg-secondary);
            margin: 0;
            padding: 0;
        }

        /* Modern Grid Layout */
        .model-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            min-height: calc(100vh - 120px);
        }

        /* Main Content Area */
        .model-main {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* Model Header Card */
        .model-header-card {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 25px;
            align-items: center;
        }

        .model-avatar {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            object-fit: cover;
            box-shadow: var(--shadow-lg);
        }

        .model-info h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }

        .model-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--success-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Live Stream Section */
        .stream-section {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-md);
        }

        .stream-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stream-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .viewer-count {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-color);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Sidebar */
        .model-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Analytics Dashboard */
        .analytics-dashboard {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-md);
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--bg-tertiary);
        }

        .dashboard-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        /* Enhanced Activity Heatmap */
        .activity-section {
            background: var(--bg-primary);
            border-radius: 16px;
            padding: 25px;
            box-shadow: var(--shadow-md);
        }

        .heatmap-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .heatmap-header {
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .activity-summary {
            display: flex;
            gap: 20px;
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .activity-stat {
            text-align: center;
        }

        .activity-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-color);
        }

        .activity-stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* Modern Heatmap Grid */
        .heatmap-grid {
            display: grid;
            grid-template-columns: repeat(24, 1fr);
            gap: 3px;
            margin-bottom: 15px;
        }

        .heatmap-cell {
            aspect-ratio: 1;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
        }

        .heatmap-cell:hover {
            transform: scale(1.2);
            z-index: 10;
            box-shadow: var(--shadow-lg);
        }

        .heatmap-day-labels {
            display: grid;
            grid-template-rows: repeat(7, 1fr);
            gap: 3px;
            margin-right: 10px;
        }

        .day-label {
            display: flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        /* Insights Cards */
        .insights-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .insight-card {
            background: var(--bg-secondary);
            border-radius: 12px;
            padding: 15px;
            border-left: 4px solid var(--accent-color);
        }

        .insight-positive { border-left-color: var(--success-color); }
        .insight-warning { border-left-color: var(--warning-color); }
        .insight-danger { border-left-color: var(--danger-color); }

        .insight-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .insight-message {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .model-container {
                grid-template-columns: 1fr;
                gap: 20px;
                max-width: 900px;
            }

            .model-header-card {
                grid-template-columns: auto 1fr;
                text-align: left;
            }

            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 768px) {
            .model-container {
                padding: 15px;
            }

            .model-header-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .model-info h1 {
                font-size: 2rem;
            }
        }

        /* Performance Score Ring */
        .performance-ring {
            position: relative;
            width: 80px;
            height: 80px;
        }

        .ring-background {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: conic-gradient(var(--accent-color) 0deg, var(--bg-tertiary) 0deg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <?php include('templates/header.php'); ?>
    
    <div class="model-container">
        <!-- Main Content -->
        <div class="model-main">
            <!-- Model Header Card -->
            <div class="model-header-card">
                <img src="<?= htmlspecialchars($model['image_url'] ?? '/assets/offline.png') ?>" 
                     alt="<?= htmlspecialchars($model['username']) ?>" 
                     class="model-avatar"
                     onerror="this.src='/assets/offline.png'">
                
                <div class="model-info">
                    <h1><?= htmlspecialchars($model['username']) ?></h1>
                    <div class="model-meta">
                        <div class="meta-badge">
                            <div class="status-indicator"></div>
                            <?= $model_online ? 'Online' : 'Offline' ?>
                        </div>
                        <div class="meta-badge">
                            <i class="fas fa-user"></i>
                            <?= ucfirst($model['gender'] === 'f' ? 'Female' : ($model['gender'] === 'm' ? 'Male' : ($model['gender'] === 't' ? 'Trans' : 'Couple'))) ?>
                        </div>
                        <?php if (!empty($model['age'])): ?>
                        <div class="meta-badge">
                            <i class="fas fa-calendar"></i>
                            <?= intval($model['age']) ?> years
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($model['location'])): ?>
                        <div class="meta-badge">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($model['location']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($model['room_subject'])): ?>
                    <p class="room-subject"><?= htmlspecialchars($model['room_subject']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="performance-ring">
                    <div class="ring-background" style="background: conic-gradient(var(--accent-color) <?= $enhanced_analytics['performance_score']['score'] * 3.6 ?>deg, var(--bg-tertiary) 0deg);">
                        <?= $enhanced_analytics['performance_score']['score'] ?>
                    </div>
                </div>
            </div>

            <!-- Live Stream Section -->
            <?php if($model_online): ?>
            <div class="stream-section">
                <div class="stream-header">
                    <h2 class="stream-title">Live Stream</h2>
                    <div class="viewer-count">
                        <i class="fas fa-eye"></i>
                        <?= number_format($model['num_users'] ?? 0) ?> viewers
                    </div>
                </div>
                <?= ensure_iframe_fullscreen(chaturbate_whitelabel_replace($model['iframe_embed_revshare'], $config['whitelabel_domain']), $iframe_height) ?>
            </div>
            <?php endif; ?>

            <!-- Enhanced Activity Heatmap -->
            <div class="activity-section">
                <div class="dashboard-header">
                    <div class="dashboard-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="dashboard-title">Weekly Activity Pattern</h3>
                </div>

                <?php if ($enhanced_analytics['historical'] && isset($enhanced_analytics['historical']['weekly_pattern'])): 
                    $pattern = $enhanced_analytics['historical']['weekly_pattern'];
                    $day_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                ?>
                <div class="activity-summary">
                    <div class="activity-stat">
                        <div class="activity-stat-value"><?= $pattern['activity_score'] ?>%</div>
                        <div class="activity-stat-label">Active This Week</div>
                    </div>
                    <div class="activity-stat">
                        <div class="activity-stat-value"><?= $day_names[$pattern['best_day']] ?? 'N/A' ?></div>
                        <div class="activity-stat-label">Most Active Day</div>
                    </div>
                    <div class="activity-stat">
                        <div class="activity-stat-value"><?= sprintf('%02d:00', $pattern['best_hour']) ?></div>
                        <div class="activity-stat-label">Peak Hour</div>
                    </div>
                    <div class="activity-stat">
                        <div class="activity-stat-value"><?= $pattern['total_sessions'] ?></div>
                        <div class="activity-stat-label">Sessions</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Traditional heatmap would go here but simplified for now -->
                <div class="insights-grid">
                    <div class="insight-card insight-positive">
                        <div class="insight-title">Activity Insight</div>
                        <div class="insight-message">Most active during evening hours with consistent weekend presence</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="model-sidebar">
            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($model_insights['peak_viewers']) ?></div>
                    <div class="stat-label">Peak Viewers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($model_insights['avg_viewers']) ?></div>
                    <div class="stat-label">Avg Viewers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $model_insights['consistency_score'] ?>%</div>
                    <div class="stat-label">Consistency</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= number_format($model['num_followers'] ?? 0) ?></div>
                    <div class="stat-label">Followers</div>
                </div>
            </div>

            <!-- Analytics Dashboard -->
            <div class="analytics-dashboard">
                <div class="dashboard-header">
                    <div class="dashboard-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="dashboard-title">Performance Analytics</h3>
                </div>

                <div class="insights-grid">
                    <?php foreach ($performance_insights as $insight): ?>
                    <div class="insight-card insight-<?= $insight['type'] ?>">
                        <div class="insight-title"><?= htmlspecialchars($insight['title']) ?></div>
                        <div class="insight-message"><?= htmlspecialchars($insight['message']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Model Details -->
            <div class="analytics-dashboard">
                <div class="dashboard-header">
                    <div class="dashboard-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h3 class="dashboard-title">Model Details</h3>
                </div>

                <?php if (!empty($model['tags'])): ?>
                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 10px; color: var(--text-secondary); font-size: 0.875rem; font-weight: 600;">TAGS</h4>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach (array_slice($model['tags'], 0, 10) as $tag): ?>
                        <span style="background: var(--bg-secondary); color: var(--text-secondary); padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                            #<?= htmlspecialchars($tag) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($model['spoken_languages'])): ?>
                <div style="margin-bottom: 15px;">
                    <h4 style="margin-bottom: 8px; color: var(--text-secondary); font-size: 0.875rem; font-weight: 600;">LANGUAGES</h4>
                    <p style="margin: 0; color: var(--text-primary);"><?= htmlspecialchars($model['spoken_languages']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('templates/footer.php'); ?>
</body>
</html>