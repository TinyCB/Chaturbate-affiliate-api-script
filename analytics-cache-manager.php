<?php
/**
 * Analytics Cache Manager
 * 
 * Manages model analytics data in separate cache files to avoid corruption
 * and performance issues with large JSON files.
 */

class AnalyticsCacheManager {
    private $base_dir;
    private $cache_dir;
    private $analytics_dir;
    
    public function __construct() {
        $this->base_dir = dirname(__FILE__);
        $this->cache_dir = $this->base_dir . '/cache/';
        $this->analytics_dir = $this->cache_dir . 'analytics/';
        
        // Create analytics directory if it doesn't exist
        if (!is_dir($this->analytics_dir)) {
            mkdir($this->analytics_dir, 0755, true);
        }
    }
    
    /**
     * Get analytics data for a specific model
     */
    public function getModelAnalytics($username) {
        $username = strtolower($username);
        $file_path = $this->analytics_dir . $username . '.json';
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file_path), true);
        if ($data === null) {
            error_log("AnalyticsCacheManager: Could not parse analytics for $username: " . json_last_error_msg());
            return null;
        }
        
        return $data;
    }
    
    /**
     * Save analytics data for a specific model
     */
    public function saveModelAnalytics($username, $analytics_data) {
        $username = strtolower($username);
        $file_path = $this->analytics_dir . $username . '.json';
        
        $json = json_encode($analytics_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $result = file_put_contents($file_path, $json, LOCK_EX);
        
        if ($result === false) {
            error_log("AnalyticsCacheManager: Failed to save analytics for $username");
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if analytics data exists for a model
     */
    public function hasModelAnalytics($username) {
        $username = strtolower($username);
        $file_path = $this->analytics_dir . $username . '.json';
        return file_exists($file_path);
    }
    
    /**
     * Get list of all models with analytics data
     */
    public function getAllModelsWithAnalytics() {
        $models = [];
        $files = glob($this->analytics_dir . '*.json');
        
        foreach ($files as $file) {
            $username = basename($file, '.json');
            $models[] = $username;
        }
        
        return $models;
    }
    
    /**
     * Delete analytics data for a model (cleanup)
     */
    public function deleteModelAnalytics($username) {
        $username = strtolower($username);
        $file_path = $this->analytics_dir . $username . '.json';
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }
    
    /**
     * Get analytics directory size and file count for monitoring
     */
    public function getCacheStats() {
        $files = glob($this->analytics_dir . '*.json');
        $total_size = 0;
        $file_count = count($files);
        
        foreach ($files as $file) {
            $total_size += filesize($file);
        }
        
        return [
            'file_count' => $file_count,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2),
            'avg_file_size' => $file_count > 0 ? round($total_size / $file_count) : 0
        ];
    }
    
    /**
     * Cleanup old analytics files (models not seen for X days)
     */
    public function cleanupOldAnalytics($days_threshold = 180) {
        $files = glob($this->analytics_dir . '*.json');
        $cutoff_time = time() - ($days_threshold * 24 * 3600);
        $deleted_count = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if ($data && isset($data['last_updated'])) {
                if ($data['last_updated'] < $cutoff_time) {
                    if (unlink($file)) {
                        $deleted_count++;
                    }
                }
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Migrate analytics data from model_profiles.json to individual cache files
     */
    public function migrateFromModelProfiles() {
        $profile_file = $this->cache_dir . 'model_profiles.json';
        
        if (!file_exists($profile_file)) {
            return ['status' => 'error', 'message' => 'model_profiles.json not found'];
        }
        
        $profiles = json_decode(file_get_contents($profile_file), true);
        if ($profiles === null) {
            return ['status' => 'error', 'message' => 'Could not parse model_profiles.json'];
        }
        
        $migrated_count = 0;
        $error_count = 0;
        
        foreach ($profiles as $username => $profile) {
            // Handle both associative and numeric array formats
            if (is_numeric($username) && isset($profile['username'])) {
                $username = $profile['username'];
            }
            
            if (isset($profile['analytics'])) {
                $analytics_data = $profile['analytics'];
                $analytics_data['username'] = $username;
                $analytics_data['migrated_at'] = time();
                
                if ($this->saveModelAnalytics($username, $analytics_data)) {
                    $migrated_count++;
                } else {
                    $error_count++;
                }
            }
        }
        
        return [
            'status' => 'success',
            'migrated_count' => $migrated_count,
            'error_count' => $error_count,
            'total_profiles' => count($profiles)
        ];
    }
}