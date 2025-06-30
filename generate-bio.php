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
    $country = isset($model['country']) ? $model['country'] : '';
    $location = isset($model['location']) ? $model['location'] : (isset($model['country']) ? $model['country'] : '');
    $languages = isset($model['spoken_languages']) ? $model['spoken_languages'] : '';
    $room_subject = isset($model['room_subject']) ? $model['room_subject'] : '';
    $tags_str = (isset($model['tags']) && is_array($model['tags'])) ? implode(', ', $model['tags']) : (isset($model['tags']) ? $model['tags'] : '');
    $display_name = isset($model['display_name']) ? $model['display_name'] : (isset($model['username']) ? $model['username'] : 'Me');
    $model_url = 'https://' . rtrim($whitelabel_domain, '/') . '/' . $model['username'];

    // Diverse CTA endings for profile links
    $cta_phrases = [
        "Want more? [See my full profile here!]($model_url)",
        "Curious for the rest? [Check out my profile!]($model_url)",
        "Don’t be shy—[click here for the full show!]($model_url)",
        "Ready to play? [Unlock my full profile!]($model_url)",
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

    // Sample bios (leave as is, mix as you like)
    $example_bios = [
        "Naughty by nature, wild by choice. Ready to tease you until you can't take it? Tip for naughtier fun. 😈 {$cta_phrases[0]}",
        "My favorite position? In front of the cam, totally bare and ready to play. Got a wild side? Prove it to me. {$cta_phrases[1]}",
        "Ready to strip, moan, and make you lose control—just ask nicely. 💋 {$cta_phrases[2]}",
        "If you want shy and sweet, try someone else. If you want wild, wet, and a little taboo, get in here now. 🚨 {$cta_phrases[3]}",
        "Lingerie, roleplay, toys—it's all on my menu. Tell me your fantasy or watch me lose myself live. {$cta_phrases[4]}",
        "Who says good girls don't get dirty? Let me prove how wrong you are tonight. {$cta_phrases[5]}",
        "Guaranteed at least one laugh—more if you dare me. {$cta_phrases[6]}",
        "Rainy nights, soft playlists, and silly jokes = my perfect stream. Want to join in? {$cta_phrases[7]}",
        "My room is an open invite for snack lovers and silly dance-offs. {$cta_phrases[8]}",
        "Movie marathons, spontaneous dance breaks (and pajamas) are always welcome. {$cta_phrases[9]}",
        "Nothing staged here—just silly moments, real smiles, and a splash of mischief. {$cta_phrases[10]}",
        "If you've got corny jokes, bring them—I score extra points for a bad pun! 😂 {$cta_phrases[11]}",
        "Confession: My favorite part of streaming is asking you what song I'm supposed to sing next. {$cta_phrases[12]}",
        "I believe coffee and laughter can fix almost anything. ☕️ {$cta_phrases[13]}",
        "Bring your best meme game. I'll show my favorite. {$cta_phrases[0]}",
        "No filter, just a lot of snack breaks and very questionable dance moves. {$cta_phrases[1]}",
        "Hi! I’m Lexi. 😊 If you love flirting, dad jokes, or 90s pop, let’s talk! {$cta_phrases[2]}",
        "Hey, I’m Zoe, a friendly nerd who loves video games, pizza 🍕, and slow mornings. Say hi if you’re shy—I don’t bite (unless you ask nicely)! {$cta_phrases[3]}",
        "Welcome! I’m Lila, your curvy Latina bookworm who loves coffee ☕️ and long conversations. Come hang out and tell me your favorite movie! {$cta_phrases[4]}",
        "Hey! It’s Brooke, live from California. Sun and good times are my thing. {$cta_phrases[5]}",
        "Do you think pineapple belongs on pizza? I’ll fight for it. 🍍 {$cta_phrases[6]}",
        "Laugh first, strip later. That's my motto. {$cta_phrases[7]}"
    ];
    shuffle($example_bios);
    $used_examples = array_slice($example_bios, 0, 6);

    // Prepare greeting (randomly from your list, with display name!)
    $greetings = [
        "What's up? I'm ",
        "Yo! ",
        "Hi, I'm ",
        "Hey! My name’s ",
        "Hola! I’m ",
        "Greetings, I’m ",
        "Hey, call me ",
        "Hello there, they call me ",
        "Hellooo, my name is ",
        "Hey folks, it’s ",
        "Hiya! I'm ",
        "Hi! People call me ",
        "Sup, I'm ",
        "Hey hey, I’m ",
        "Hi, it's ",
        "Hey, I'm ",
        "Hello! I'm ",
        "Hi everyone, I'm "
    ];
    shuffle($greetings);
    $chosen_greeting = $greetings[0] . $display_name . ".";

    // Now prompt WITHOUT greeting in the output!
    $prompt = <<<EOT
Write exactly ONE cam model profile bio (not more!) in first person, as if a real person is introducing herself on an adult cam site for 18+ audiences.
- Begin the bio **without** any greeting or introduction (no "Hi", "Hey", "Hello", "I'm [Name]", "Hey there, I'm [Name]", etc). Jump right into the fun, flirty, sexy, or playful sentence. (The greeting will be added automatically.)
- Vary the structure: Some bios should open with a fun fact, playful confession, an activity, a bold or naughty invitation, or a unique line. Some can be openly sexual or teasing, some just playful. Mix up your style every time.
- You are permitted and encouraged to be explicit or naughty if fitting: do NOT censor sexual language or context if it's true to her persona or cam shows. Sexy, erotic, teasing, NSFW, and kinky references are allowed. Mix playful, flirty, and explicit as the mood fits. Do not be crude/mean.
- Do NOT start the bio with "Just a", "I'm just", "Just someone", "I'm a...", “Ever tried...”, “Ever wondered...”, or any other overused/stock opener. Avoid formulas and repeating your structures.
- Mix up your language—use bold openers, confessions, direct invites, teasing, silly, and flirty styles. Never use the same format every time.
- Use 1–2 emoji if it feels natural; skip if it doesn't.
- Write in a natural, relaxed, human voice—avoid fantasy, poetry, or obvious roleplay.
- Maximum 3 sentences. No hashtags, numbers, usernames, birthdays, or exact cities—only general places (“from Texas”, “from Europe”).
- End the bio with a lively, fun, or bold call to action that invites the reader to your profile—do not use the same CTA each time! Use your own style and always include a Markdown link to "$model_url" in the CTA.
- Output only a single, complete bio—never variants, notes, or explanations.
Below are a few sample bios for inspiration ONLY (do not copy verbatim):
- {$used_examples[0]}
- {$used_examples[1]}
- {$used_examples[2]}
- {$used_examples[3]}
- {$used_examples[4]}
- {$used_examples[5]}
Model info for inspiration:
Gender: $gender
Country: $country
Location: $location
Languages: $languages
Room topic: $room_subject
Tags: $tags_str
Output only your single final bio, nothing else.
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
            "max_tokens" => 210,
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

    // Prepend greeting and ensure proper spacing and punctuation.
    $bio_body = ltrim($bio_body, "\r\n\t .");
    $final_bio = $chosen_greeting . " " . $bio_body;
    return $final_bio;
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