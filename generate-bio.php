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
    $gender = $model['gender'] ?? '';
    $country = $model['country'] ?? '';
    $location = $model['location'] ?? ($model['country'] ?? '');
    $languages = $model['spoken_languages'] ?? '';
    $room_subject = $model['room_subject'] ?? '';
    $tags_str = (isset($model['tags']) && is_array($model['tags'])) ? implode(', ', $model['tags']) : ($model['tags'] ?? '');
    $display_name = $model['display_name'] ?? ($model['username'] ?? 'Me');
    $model_url = 'https://' . rtrim($whitelabel_domain, '/') . '/' . $model['username'];

    // --- GENDER PROMPT ---
    $gender_prompt = '';
    if ($gender === 'c') {
        // Couple: "we", mix of M/F, duo energy, no solo-I
        $gender_prompt = "- This is a COUPLE profile (gender='c'). Write as a COUPLE: Always use \"we\", \"our\", \"us\", \"both\" or shared duo language. Mix masculine and feminine sexual references if possible, and reference both partners' pleasure if appropriate. Do NOT use solo language (no 'I'm a...'). This should NEVER sound like a solo person, and should not read as only a man or only a woman. Use phrases for double pleasure, shared fantasies, swapping, and duo action.";
    } elseif ($gender === 'm') {
        $gender_prompt = "- This is a SOLO MALE (gender='m'). Write in first person as a man, focusing on masculine sexual acts, cock, stamina, muscles, dominant or playful guy attitude. No references to having tits, pussy, or acting as a woman.";
    } elseif ($gender === 't') {
        $gender_prompt = "- This is a TRANS or SHEMALE profile (gender='t'). Write in first person as a trans woman or shemale—be explicit and unashamed, mention cock, t-girl experiences, and blend feminine/masculine shame/pride if it fits the persona.";
    } else {
        // Solo woman, classic
        $gender_prompt = "- This is a SOLO WOMAN (gender='f'). Write in first person as a woman, sexy and explicit, as you have already been doing.";
    }

    $cta_phrases = [
        "Want more? [See my full profile here!]($model_url)",
        "Curious for the rest? [Check out my profile!]($model_url)",
        "Don’t be shy—[click here for the full show!]($model_url)",
        "Unlock my full profile for more play! ($model_url)",
        "Join me for the real fun—[see everything here!]($model_url)",
        "Can’t get enough? [Come to my profile!]($model_url)",
        "Let’s get wilder—[see my full profile!]($model_url)",
        "See what you’ve been missing—[visit my profile!]($model_url)",
        "Tempted yet? [See more here!]($model_url)",
        "Feeling naughty? [Step into my private world!]($model_url)",
        "Come see what happens after hours—[here’s my profile!]($model_url)",
        "The best is yet to come… [visit my profile!]($model_url)",
        "Think you can handle more? [Join me now!]($model_url)",
        "Don’t wait—[click here to see all my sides!]($model_url)"
    ];
    shuffle($cta_phrases);
    $example_bios = [
        // Your existing example bios, unchanged ...
        "Naughty by nature, wild by choice. I’ll tease every inch of you until you can’t take it. 😈 {$cta_phrases[0]}",
        "My favorite way to get off? Showing it all off—on cam, just for you. If you’ve got a filthy imagination, I’m all ears. {$cta_phrases[1]}",
        "Moaning, riding, and dripping just waiting for you to ask for more. 💋 {$cta_phrases[2]}",
        "If you’re after sweet and innocent, keep looking. My shows are wild, wet, and totally uncensored. 🚨 {$cta_phrases[3]}",
        "Lingerie, roleplay, and toys—just the beginning. Cum share your fantasy or just watch me lose myself. {$cta_phrases[4]}",
        "Who says good girls can’t get messy? Let me prove what these hands and lips can do. {$cta_phrases[5]}",
        "You’ll laugh and squirm—I dare you to push me further. {$cta_phrases[6]}",
        "Rain on the window, vibrator buzzing, dirty jokes—join me and make it wetter. {$cta_phrases[7]}",
        "My room is a home for big tit lovers and filthy fun. {$cta_phrases[8]}",
        "Movie marathons get x-rated fast when I shed my pj’s. Want to see? {$cta_phrases[9]}",
        "Nothing staged—just real moans, real play, and a taste of trouble. {$cta_phrases[10]}",
        "If you bring corny jokes, I’ll bring my best moves (and maybe my favorite toy). 😂 {$cta_phrases[11]}",
        "Confession: I love to hear what you want me to say while I’m dripping for you. {$cta_phrases[12]}",
        "Coffee and a cock in my mouth? Best way to start the day. ☕️ {$cta_phrases[13]}",
        "Bring your wildest requests. I dare you to shock me. {$cta_phrases[0]}",
        "No filter, just a lot of twerking—and my tits out, always. Try to keep up. {$cta_phrases[1]}",
        "If you love dirty talk, big boobs, and fucking until dawn, you’ll love me. 😊 {$cta_phrases[2]}",
        "Gamer, pizza slut, and the girl who’ll ride your face if you ask sweetly. 🍕 {$cta_phrases[3]}",
        "Curvy, raunchy, and obsessed with porn gifs. Come share your naughtiest dream! {$cta_phrases[4]}",
        "California sun and making every day a little dirtier—that’s my vibe. {$cta_phrases[5]}",
        "Pineapple on pizza? Who cares—I just want something thick to ride. 🍍 {$cta_phrases[6]}",
        "Laugh first, strip later. That's my motto. {$cta_phrases[7]}"
    ];
    shuffle($example_bios);
    $used_examples = array_slice($example_bios, 0, 6);

    $prompt = <<<EOT
$gender_prompt
Write exactly ONE cam model profile bio (not more!), as if a real person or couple is introducing themselves on an explicit adult cam site for 18+ audiences. If this is a couple, always write as "we"; if solo, use "I"; for trans or male, match the voice!
• Begin immediately with a bold, filthy, sexual, playful, or flirty line—DO NOT use any greeting or solo introduction ("Hi", "Hey", "Hello", "I'm ...", etc) unless it's in the gender instructions above.
• Vary your opener and structure; use unique hooks like confessions, fantasies, playful questions, shocking admissions, activity descriptions, or brazen, direct invitations. Mix raw, explicit, and uninhibited styles with playful, teasing, or naughty humor.
• This is for a real, x-rated cam site: **do NOT hold back. Use sexual, graphic, obscene, or explicit words as commonly found in porn and real cam model bios (e.g.: "pussy", "cock", "cum", "squirt", "fuck", "suck dick", "ass", "spit", "anal", "deepthroat", etc.), wherever they fit the persona.**
• Take direct inspiration from the model's subject and tags fields below, especially if they are dirty/sexual—use their wording and explicit themes as natural sentences and fantasies in the bio, NOT as a comma-separated list or hashtag dump.
• Do NOT just repeat or list tags/subjects; always blend their content into filthy self-descriptions, kinks, sexual talents, fantasies, turn-ons, or invitations, as if you were describing yourself candidly.
• Strictly avoid formulaic/overused openers and phrases like "Just a ...", "I'm just ...", "Ever tried ...", "Ever wondered ...", or "ready to..." / "ready for...".
• Write like a real, uninhibited, adult performer—never robotic, poetic, fantasy, or roleplay script.
• Mix explicit sexuality, dirty talk, uninhibited self-expression, and playfulness, but never be mean, cruel, or dehumanizing.
• 200–300 words. No hashtags, usernames, birthdays, numbers, or exact city; only general places, ideally U.S. regions or states ("from the US", "from California", "from Texas").
• Use 1–2 emojis if they fit; skip if not.
• End with a unique, lively, and explicit call to action (CTA) that invites the reader to your profile and **includes a Markdown link to "$model_url" (placement should vary, not always at the end!)**
• Never output any field label, list of tags/subject/keywords, or meta info! Only naturally inspired, human bio.
• You _may_ mention general location/region (e.g. "from California"), language(s), gender, and age group if natural—never labels or exact details/ages.
• OUTPUT ONLY ONE single-paragraph bio—no lists, notes, field labels, or variants.
**IMPORTANT:** If the subject or tags below include dirty/sexual words or themes (like “pussy”, “cock”, “cum”, “fuck”, “suck dick”, “squirt”, etc.), you are strongly encouraged to use those actual words naturally and graphically in the bio as if you are an uninhibited cam performer.
Sample CTAs for inspiration (DO NOT COPY; link placement should vary, not always at the end):
- [Join my wild side!]($model_url) If you dare.
- Tongues and hands—bring both. [See the full fun here!]($model_url)
- I have plenty more—take a peek at [my private world!]($model_url)
- Curious what you’ll find? [Explore now!]($model_url)
- Just a click away: [Unlock my secrets here!]($model_url)
Example bios for style only (do not copy or refer directly):
- {$used_examples[0]}
- {$used_examples[1]}
- {$used_examples[2]}
- {$used_examples[3]}
- {$used_examples[4]}
- {$used_examples[5]}
For your inspiration ONLY (NEVER output these verbatim, or as any kind of list or label):
Room subject: $room_subject
Tags: $tags_str
Display name: $display_name
Location: $location
Gender: $gender
Languages: $languages
OUTPUT ONLY the bio text as a single paragraph and nothing else.
EOT;

    $temperature = rand(11, 13) / 10;
    $top_p = rand(92, 100) / 100;
    if ($llm_provider === 'openai') {
        $api_url = $llm_api_url ?: 'https://api.openai.com/v1/chat/completions';
        $model_name = $llm_model ?: 'gpt-4o';
        $payload = [
            "model" => $model_name,
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 1000,
            "temperature" => $temperature,
            "top_p" => $top_p
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
            'stream' => false,
            'temperature' => $temperature,
            'top_p' => $top_p
        ];
        $headers = ['Content-Type: application/json'];
    }
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($result === false) {
        $err = curl_error($ch);
        $msg = "[cURL error] {$model['username']}: $err (HTTP status: $http_code)";
        echo "$msg\n";
        log_debug($msg);
        return null;
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
            $bio_body = trim($json['choices'][0]['message']['content']);
        } else {
            return null;
        }
    } else {
        if (isset($json['response'])) {
            $bio_body = trim($json['response']);
        } elseif (isset($json['message']['content'])) {
            $bio_body = trim($json['message']['content']);
        } else {
            return null;
        }
    }
    $bio_body = ltrim($bio_body, "\r\n\t .");
    return $bio_body;
}

// --- Setup ---
require_once 'bio-cache-manager.php';

$cache_dir = __DIR__ . "/cache/";
$profile_file = $cache_dir . "model_profiles.json";
$config = include(__DIR__.'/config.php');
$llm_provider = $config['llm_provider'] ?? 'ollama';
$llm_api_url = $config['llm_api_url'] ?? 'http://127.0.0.1:11434/api/generate';
$llm_model = $config['llm_model'] ?? 'mistral:7b';
$llm_api_key = $config['llm_api_key'] ?? '';
$whitelabel_domain = $config['whitelabel_domain'] ?? 'my.tinycb.com';
$llm_rewrite_all_bios = !empty($config['llm_rewrite_all_bios']) && $config['llm_rewrite_all_bios'] == "1";
$REWRITE_MODE = $config['llm_rewrite_mode'] ?? 'missing';
$stale_days   = $config['llm_stale_days'] ?? 7;
$ids_to_rewrite = [];
if (!empty($config['llm_manual_ids'])) {
    $ids_to_rewrite = array_filter(array_map('trim', preg_split('/[\s,]+/', $config['llm_manual_ids'])));
}

// Initialize bio cache manager
$bio_cache = new BioCacheManager();

// Migrate existing bios if this is the first run
$bio_stats = $bio_cache->getCacheStats();
if ($bio_stats['file_count'] === 0 && file_exists($profile_file)) {
    echo "Migrating existing bio data to individual cache files...\n";
    $migration_result = $bio_cache->migrateFromModelProfiles();
    echo "Migration complete: {$migration_result['migrated_count']} bios migrated, {$migration_result['error_count']} errors\n";
    log_debug("Bio migration: {$migration_result['migrated_count']} migrated, {$migration_result['error_count']} errors");
}

// Determine which models need bio generation based on mode
if ($llm_rewrite_all_bios) {
    $models_to_process = $bio_cache->getModelsNeedingBios('all', $stale_days, $ids_to_rewrite);
} else {
    $models_to_process = $bio_cache->getModelsNeedingBios($REWRITE_MODE, $stale_days, $ids_to_rewrite);
}
ini_set('max_execution_time', 0);
set_time_limit(0);
$count = 0;
$batch_size = 5;
$now = time();

echo "Processing " . count($models_to_process) . " models for bio generation...\n";

foreach ($models_to_process as $profile) {
    $username = $profile['username'] ?? '';
    if (empty($username)) continue;
    
    echo "Generating bio for: $username ... ";
    log_debug("Generating bio for: $username");
    
    $bio = generate_model_bio(
        $profile,
        $llm_provider,
        $llm_api_url,
        $llm_model,
        $llm_api_key,
        $whitelabel_domain
    );
    
    if ($bio) {
        // Get existing bio data or create new
        $existing_bio_data = $bio_cache->getModelBio($username);
        
        $bio_data = [
            'username' => $username,
            'ai_bio' => $bio,
            'ai_bio_last_generated' => time(),
            'ai_bio_version' => ($existing_bio_data['ai_bio_version'] ?? 0) + 1,
            'generation_config' => [
                'llm_provider' => $llm_provider,
                'llm_model' => $llm_model,
                'whitelabel_domain' => $whitelabel_domain,
                'generated_at' => time()
            ],
            'source_profile' => [
                'gender' => $profile['gender'] ?? '',
                'location' => $profile['location'] ?? '',
                'country' => $profile['country'] ?? '',
                'spoken_languages' => $profile['spoken_languages'] ?? '',
                'room_subject' => $profile['room_subject'] ?? '',
                'tags' => $profile['tags'] ?? [],
                'display_name' => $profile['display_name'] ?? $username
            ]
        ];
        
        // Preserve creation timestamp if it exists
        if (isset($existing_bio_data['created_at'])) {
            $bio_data['created_at'] = $existing_bio_data['created_at'];
        }
        if (isset($existing_bio_data['migrated_at'])) {
            $bio_data['migrated_at'] = $existing_bio_data['migrated_at'];
        }
        
        if ($bio_cache->saveModelBio($username, $bio_data)) {
            echo "DONE\n";
            log_debug("DONE for: $username - saved to bio cache");
        } else {
            echo "SAVE FAILED\n";
            log_debug("Bio generation succeeded but save failed for: $username");
        }
    } else {
        echo "FAILED\n";
        log_debug("FAILED for: $username");
    }
    
    $count++;
    usleep(250000);
    
    if ($count % $batch_size === 0) {
        echo "Batch of $batch_size bios processed...\n";
        log_debug("Batch of $batch_size bios processed");
    }
}
// --- Self-resetting logic for safe admin ops ---
if ($llm_rewrite_all_bios) {
    $config['llm_rewrite_all_bios'] = "0";
    file_put_contents(__DIR__ . "/config.php", "<?php\nreturn " . var_export($config, true) . ";\n");
    echo "Rewrite-all flag auto-disabled in config for next run.\n";
}
if (in_array($REWRITE_MODE, ['all', 'stale', 'ids'])) {
    $config['llm_rewrite_mode'] = 'missing';
    if ($REWRITE_MODE == 'ids') $config['llm_manual_ids'] = '';
    file_put_contents(__DIR__ . "/config.php", "<?php\nreturn " . var_export($config, true) . ";\n");
    echo "LLM rewrite mode auto-reset to 'missing' after batch run.\n";
}

// Final summary
if ($count > 0) {
    echo "$count AI bios generated and saved to individual cache files.\n";
    log_debug("$count bios generated and saved to bio cache");
    
    // Show cache statistics
    $final_stats = $bio_cache->getCacheStats();
    echo "Bio cache stats: {$final_stats['file_count']} files, {$final_stats['total_size_mb']} MB total\n";
} else {
    echo "No model needed a new bio.\n";
    log_debug("No model needed a new bio.");
}

// Cleanup old bio files if requested
if (!empty($config['bio_cleanup_days']) && $config['bio_cleanup_days'] > 0) {
    $cleanup_days = (int)$config['bio_cleanup_days'];
    $deleted_count = $bio_cache->cleanupOldBios($cleanup_days);
    if ($deleted_count > 0) {
        echo "Cleaned up $deleted_count old bio files (older than $cleanup_days days).\n";
        log_debug("Cleaned up $deleted_count old bio files");
    }
}
?>