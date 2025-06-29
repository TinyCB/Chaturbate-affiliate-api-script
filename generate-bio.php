<?php
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    echo "Forbidden.";
    exit;
}
function log_debug($msg) {
    $logfile = __DIR__ . '/ollama-bio-debug.log';
    file_put_contents($logfile, date("[Y-m-d H:i:s] ") . $msg . "\n", FILE_APPEND);
}

function generate_model_bio($model, $llm_provider, $llm_api_url, $llm_model, $llm_api_key, $whitelabel_domain) {
    $gender = isset($model['gender']) ? $model['gender'] : '';
    $age = isset($model['age']) ? $model['age'] : '';
    $country = isset($model['country']) ? $model['country'] : '';
    $location = isset($model['location']) ? $model['location'] : (isset($model['country']) ? $model['country'] : '');
    $languages = isset($model['spoken_languages']) ? $model['spoken_languages'] : '';
    $room_subject = isset($model['room_subject']) ? $model['room_subject'] : '';
    $tags_str = (isset($model['tags']) && is_array($model['tags'])) ? implode(', ', $model['tags']) : (isset($model['tags']) ? $model['tags'] : '');
    $model_url = 'https://' . rtrim($whitelabel_domain, '/') . '/' . $model['username'];

    $prompt = <<<PROMPT
Write a creative, flirty, playful, fun, or sexy cam model bio, in the first person as if the model is introducing herself.
- You have maximum freedom! Bio can be wild, poetic, saucy, cute, or mysterious—but must sound natural for a lively cam girl or cam guy profile (never as boring template).
- No more than 3 sentences.
- Greet the user in a unique way for each bio; do not use "Hey there" or "Let me tempt you" each time. Get creative: "Welcome to my jungle", "Craving a new adventure?", "Dare to join me tonight?", "Feeling playful?", "Let's go wild together" or invent new lines. Emojis are encouraged, use freely when the mood fits!
- Don't use actual username, age, or a city name. Country or region is fine. No hashtags or numbers.
- Always mention or allude to at least one hobby, room topic, or trait from the tags or subject.
- End with a different, seductive call-to-action (e.g., "Dive in!", "See the rest", "Come play tonight!", "I'm waiting for you", "Peek at my gallery", "Unlock my secrets..."), and include a Markdown link like [Join me]($model_url) or [Come visit my profile]($model_url) with varied link text per profile.
- ONLY output the bio—no reasonings, thoughts, extra explanations, or markdown fences.

Model details:
Gender: $gender
Age: $age
Country: $country
Location: $location
Languages: $languages
Room topic: $room_subject
Tags: $tags_str
Bio (max 3 sentences, then the link):
PROMPT;

    if ($llm_provider === 'openai') {
        $api_url = $llm_api_url ?: 'https://api.openai.com/v1/chat/completions';
        $model_name = $llm_model ?: 'gpt-4o';
        $payload = [
            "model" => $model_name,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 180,
            "temperature" => 1.2 // High diversity!
        ];
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $llm_api_key
        ];
    } else {
        $api_url = $llm_api_url ?: 'http://127.0.0.1:11434/api/generate';
        $model_name = $llm_model ?: 'mistral:7b';
        $payload = [
            'model' => $model_name,
            'prompt' => $prompt,
            'stream' => false
        ];
        $headers = ['Content-Type: application/json'];
    }
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($result === false) {
        $err = curl_error($ch);
        $msg = "[cURL error] {$model['username']}: $err (HTTP status: $http_code)";
        echo "$msg\n";
        log_debug($msg);
    } else {
        $msg = "[LLM HTTP $http_code] for {$model['username']}: $result";
        echo "$msg\n";
        log_debug($msg);
    }
    curl_close($ch);
    if (!$result) return null;
    $json = json_decode($result, true);
    if ($llm_provider === 'openai') {
        if (isset($json['choices'][0]['message']['content'])) {
            return trim($json['choices'][0]['message']['content']);
        }
    } else {
        if (isset($json['response']))  return trim($json['response']);
        if (isset($json['message']['content']))  return trim($json['message']['content']);
    }
    return null;
}
$config = include('config.php');
$cache_dir = __DIR__ . "/cache/";
$profile_file = $cache_dir . "model_profiles.json";
$llm_provider = !empty($config['llm_provider']) ? $config['llm_provider'] : 'ollama';
$llm_api_url = !empty($config['llm_api_url']) ? $config['llm_api_url'] : 'http://127.0.0.1:11434/api/generate';
$llm_model   = !empty($config['llm_model']) ? $config['llm_model'] : 'mistral:7b';
$llm_api_key = !empty($config['llm_api_key']) ? $config['llm_api_key'] : '';
$whitelabel_domain = isset($config['whitelabel_domain']) ? $config['whitelabel_domain'] : 'my.tinycb.com';
$llm_rewrite_all_bios = !empty($config['llm_rewrite_all_bios']) && $config['llm_rewrite_all_bios'] == "1";
$modelProfiles = [];
if (file_exists($profile_file)) {
    $json = @file_get_contents($profile_file);
    $modelProfiles = json_decode($json, true);
    if (!is_array($modelProfiles)) $modelProfiles = [];
}
ini_set('max_execution_time', 0);
set_time_limit(0);
$count = 0;
$batch_size = 5;
foreach ($modelProfiles as $idx => &$profile) {
    if ($llm_rewrite_all_bios || empty($profile['ai_bio'])) {
        echo "Generating bio for: {$profile['username']} ... ";
        log_debug("Generating bio for: {$profile['username']}");
        $bio = generate_model_bio(
            $profile,
            $llm_provider,
            $llm_api_url,
            $llm_model,
            $llm_api_key,
            $whitelabel_domain
        );
        if ($bio) {
            $profile['ai_bio'] = $bio;
            echo "DONE\n";
            log_debug("DONE for: {$profile['username']}");
        } else {
            echo "FAILED\n";
            log_debug("FAILED for: {$profile['username']}");
        }
        $count++;
        usleep(250000);
        if ($count % $batch_size === 0) {
            file_put_contents($profile_file, json_encode($modelProfiles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            echo "Batch of $batch_size written to archive...\n";
            log_debug("Batch of $batch_size bios written to model_profiles.json");
        }
    }
}
unset($profile);
// If rewrite-all was used, turn it off after run!
if ($llm_rewrite_all_bios) {
    $config['llm_rewrite_all_bios'] = "0";
    file_put_contents(__DIR__ . "/config.php", "<?php\nreturn " . var_export($config, true) . ";\n");
    echo "Rewrite-all flag auto-disabled in config for next run.\n";
}
if ($count > 0 && $count % $batch_size !== 0) {
    file_put_contents($profile_file, json_encode($modelProfiles, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    echo "Final batch written to archive.\n";
    log_debug("Final batch bios written to model_profiles.json");
}
if ($count > 0) {
    echo "$count AI bios generated and written to archive.\n";
    log_debug("$count bios written to model_profiles.json");
} else {
    echo "No model needed a new bio.\n";
    log_debug("No model needed a new bio.");
}