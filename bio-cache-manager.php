<?php
/**
 * Bio Cache Manager
 * 
 * Manages AI-generated bio data in separate cache files to improve
 * performance and avoid issues with large JSON files.
 */

class BioCacheManager {
    private $base_dir;
    private $cache_dir;
    private $bio_dir;
    
    public function __construct() {
        $this->base_dir = dirname(__FILE__);
        $this->cache_dir = $this->base_dir . '/cache/';
        $this->bio_dir = $this->cache_dir . 'bio/';
        
        // Create bio directory if it doesn't exist
        if (!is_dir($this->bio_dir)) {
            mkdir($this->bio_dir, 0755, true);
        }
    }
    
    /**
     * Get bio data for a specific model
     */
    public function getModelBio($username) {
        $username = strtolower($username);
        $file_path = $this->bio_dir . $username . '.json';
        
        if (!file_exists($file_path)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file_path), true);
        if ($data === null) {
            error_log("BioCacheManager: Could not parse bio for $username: " . json_last_error_msg());
            return null;
        }
        
        return $data;
    }
    
    /**
     * Save bio data for a specific model
     */
    public function saveModelBio($username, $bio_data) {
        $username = strtolower($username);
        $file_path = $this->bio_dir . $username . '.json';
        
        // Ensure required fields are set
        if (!isset($bio_data['username'])) {
            $bio_data['username'] = $username;
        }
        if (!isset($bio_data['created_at'])) {
            $bio_data['created_at'] = time();
        }
        $bio_data['updated_at'] = time();
        
        $json = json_encode($bio_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($file_path, $json, LOCK_EX);
        
        if ($result === false) {
            error_log("BioCacheManager: Failed to save bio for $username");
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if bio data exists for a model
     */
    public function hasModelBio($username) {
        $username = strtolower($username);
        $file_path = $this->bio_dir . $username . '.json';
        return file_exists($file_path);
    }
    
    /**
     * Get list of all models with bio data
     */
    public function getAllModelsWithBios() {
        $models = [];
        $files = glob($this->bio_dir . '*.json');
        
        foreach ($files as $file) {
            $username = basename($file, '.json');
            $models[] = $username;
        }
        
        return $models;
    }
    
    /**
     * Delete bio data for a model (cleanup)
     */
    public function deleteModelBio($username) {
        $username = strtolower($username);
        $file_path = $this->bio_dir . $username . '.json';
        
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        
        return true;
    }
    
    /**
     * Get bio directory size and file count for monitoring
     */
    public function getCacheStats() {
        $files = glob($this->bio_dir . '*.json');
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
     * Check if a bio is stale and needs regeneration
     */
    public function isBioStale($username, $stale_days = 7) {
        $bio_data = $this->getModelBio($username);
        
        if (!$bio_data) {
            return true; // No bio exists, so it's "stale"
        }
        
        $last_generated = $bio_data['ai_bio_last_generated'] ?? 0;
        $stale_threshold = time() - ($stale_days * 24 * 3600);
        
        return $last_generated < $stale_threshold;
    }
    
    /**
     * Get models that need bio updates based on various criteria
     */
    public function getModelsNeedingBios($mode = 'missing', $stale_days = 7, $manual_ids = []) {
        $models_needing_bios = [];
        
        // Get all models from model_profiles.json
        $profile_file = $this->cache_dir . 'model_profiles.json';
        if (!file_exists($profile_file)) {
            return $models_needing_bios;
        }
        
        $profiles = json_decode(file_get_contents($profile_file), true);
        if (!is_array($profiles)) {
            return $models_needing_bios;
        }
        
        foreach ($profiles as $profile) {
            $username = $profile['username'] ?? '';
            if (empty($username)) continue;
            
            $should_generate = false;
            
            switch ($mode) {
                case 'all':
                    $should_generate = true;
                    break;
                    
                case 'missing':
                    $should_generate = !$this->hasModelBio($username) || 
                                     empty($this->getModelBio($username)['ai_bio'] ?? '');
                    break;
                    
                case 'stale':
                    $should_generate = $this->isBioStale($username, $stale_days);
                    break;
                    
                case 'ids':
                    $should_generate = in_array($profile['id'] ?? $username, $manual_ids, true);
                    break;
            }
            
            if ($should_generate) {
                $models_needing_bios[] = $profile;
            }
        }
        
        return $models_needing_bios;
    }
    
    /**
     * Migrate bio data from model_profiles.json to individual cache files
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
        
        foreach ($profiles as $profile) {
            $username = $profile['username'] ?? '';
            if (empty($username)) continue;
            
            // Check if model has AI bio data to migrate
            if (!empty($profile['ai_bio'])) {
                $bio_data = [
                    'username' => $username,
                    'ai_bio' => $profile['ai_bio'],
                    'ai_bio_last_generated' => $profile['ai_bio_last_generated'] ?? time(),
                    'ai_bio_version' => $profile['ai_bio_version'] ?? 1,
                    'migrated_at' => time(),
                    'source_profile' => [
                        'gender' => $profile['gender'] ?? '',
                        'location' => $profile['location'] ?? '',
                        'country' => $profile['country'] ?? '',
                        'spoken_languages' => $profile['spoken_languages'] ?? '',
                        'tags' => $profile['tags'] ?? [],
                        'display_name' => $profile['display_name'] ?? $username
                    ]
                ];
                
                if ($this->saveModelBio($username, $bio_data)) {
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
    
    /**
     * Cleanup old bio files (models not seen for X days)
     */
    public function cleanupOldBios($days_threshold = 180) {
        $files = glob($this->bio_dir . '*.json');
        $cutoff_time = time() - ($days_threshold * 24 * 3600);
        $deleted_count = 0;
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            
            if ($data && isset($data['updated_at'])) {
                if ($data['updated_at'] < $cutoff_time) {
                    if (unlink($file)) {
                        $deleted_count++;
                    }
                }
            } else if (filemtime($file) < $cutoff_time) {
                // Fallback to file modification time if updated_at is not available
                if (unlink($file)) {
                    $deleted_count++;
                }
            }
        }
        
        return $deleted_count;
    }
}