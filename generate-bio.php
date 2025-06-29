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
    $model_url = 'https://' . rtrim($whitelabel_domain, '/') . '/' . $model['username'];

    // --- Highly varied, anti-repetitive sample bios ---
    $example_bios = [
        // Action/fact/confession openers:
        "Guaranteed at least one laugh—more if you dare me. [To see more, visit my full profile here!]",
        "Rainy nights, soft playlists, and silly jokes = my perfect stream. Want to join in? [To see more, visit my full profile here!]",
        "Confession: My karaoke voice is best enjoyed after midnight. 🎤 [To see more, visit my full profile here!]",
        "My room is an open invite for snack lovers and silly dance-offs. [To see more, visit my full profile here!]",
        "Movie marathons, spontaneous dance breaks (and pajamas) are always welcome. [To see more, visit my full profile here!]",
        "Admit it: you wish you could pull off neon socks too. 😏🧦 [To see more, visit my full profile here!]",
        "Nothing staged here—just silly moments, real smiles, and a splash of mischief. [To see more, visit my full profile here!]",
        "If you've got corny jokes, bring them—I score extra points for a bad pun! 😂 [To see more, visit my full profile here!]",
        "Confession: My favorite part of streaming is asking you what song I'm supposed to sing next. [To see more, visit my full profile here!]",
        "Is there anything better than a cozy blanket and good company? [To see more, visit my full profile here!]",
        "Bet you can't guess my favorite midnight snack! [To see more, visit my full profile here!]",
        "I believe coffee and laughter can fix almost anything. ☕️ [To see more, visit my full profile here!]",
        "My weekends are for sketching, silly voices, and finding the next great playlist. [To see more, visit my full profile here!]",
        "Bring your best meme game. I'll show my favorite. [To see more, visit my full profile here!]",
        "No filter, just a lot of snack breaks and very questionable dance moves. [To see more, visit my full profile here!]",
        "If kindness was currency, my room would be a billionaire hangout. 🌸 [To see more, visit my full profile here!]",
        "Life’s too short for boring conversations or lukewarm coffee. [To see more, visit my full profile here!]",
        "Chocolate and karaoke are always a vibe in my room. [To see more, visit my full profile here!]",
        "Can I get a show of hands for night owls? 🦉 [To see more, visit my full profile here!]",
        "Extra points for bringing your pet to the camera. 🐾 [To see more, visit my full profile here!]",
        "My specialty: silly hats and good advice (results may vary). [To see more, visit my full profile here!]",
        "Guaranteed: at least one real laugh and a fun surprise. [To see more, visit my full profile here!]",
        "Sometimes I stream in PJs. Sometimes it’s full glam. Always good vibes. [To see more, visit my full profile here!]",
        "Dare you to stump me with the weirdest question you know! [To see more, visit my full profile here!]",
        "Ask me about my weirdest talent—bonus if you can top it. [To see more, visit my full profile here!]",
        "First album I ever owned was a total guilty pleasure (ask me!). [To see more, visit my full profile here!]",
        "What's your go-to comfort food? Mine changes weekly. [To see more, visit my full profile here!]",
        "Sparkly socks, dumb jokes, and plenty of late-night confessions. [To see more, visit my full profile here!]",
        "Bet you a smile you can't guess my favorite holiday snack. [To see more, visit my full profile here!]",
        "Cozy blankets, oversharing, and questionable singing welcome here. [To see more, visit my full profile here!]",
        // Greeting openers (minority, still zero "I'm just..." etc):
        "Hi! I’m Lexi. 😊 If you love flirting, dad jokes, or 90s pop, let’s talk! [See my full profile!]",
        "Hey, I’m Zoe, a friendly nerd who loves video games, pizza 🍕, and slow mornings. Say hi if you’re shy—I don’t bite (unless you ask nicely)! [Visit my page!]",
        "Welcome! I’m Lila, your curvy Latina bookworm who loves coffee ☕️ and long conversations. Come hang out and tell me your favorite movie! [See more about me!]",
        "Hey! It’s Brooke, live from California. Sun and good times are my thing. [See what I’m up to!]",
        // Occasional questions (rarer, not "Ever tried/ever wondered"):
        "Do you think pineapple belongs on pizza? I’ll fight for it. 🍍 [To see more, visit my full profile here!]",
        "What’s your karaoke go-to? (I’m not judging. Much.) [To see more, visit my full profile here!]",
        "Question: spontaneous dance battles or deep chats—what's your vibe? [To see more, visit my full profile here!]",
        // Playful invitations and bold lines
        "Laugh first, strip later. That's my motto. [To see more, visit my full profile here!]",
        "Themed nights, goofy games, and snack reviews are a thing in my room. [To see more, visit my full profile here!]",
        "Let's get cozy and talk about everything (or nothing). [To see more, visit my full profile here!]",
        "Bring your quirks—I'll show you mine. [To see more, visit my full profile here!]",
        // Fun, direct, and weirdly honest
        "My weakness: late-night cereal and spin-the-wheel games. [To see more, visit my full profile here!]",
        "I collect stickers, cheesy jokes, and fun people. [To see more, visit my full profile here!]",
        "If you catch me with a book and a giant mug, it's a perfect night. [To see more, visit my full profile here!]",
        "Never met a snack I didn’t love or a weird story I didn’t want to hear. [To see more, visit my full profile here!]"
    ];
    shuffle($example_bios);
    $used_examples = array_slice($example_bios, 0, 5);

    $prompt = <<<EOT
Write exactly ONE cam model profile bio (not more!) in first person, as if a real person is introducing herself casually on an adult cam site.

- Vary the structure: Some bios can start with 'Hi', 'Hey', or a greeting and name, but others should open with a fun fact, direct statement, activity, playful confession, a situation, bold invitation, silly challenge, or even a unique line. Rarely use questions as openers, and avoid repeating the same format from example to example.
- Do NOT start the bio with "Just a", "I'm just", "Just someone", "I'm a...", or any “Ever tried...”, “Ever wondered...”, "Ever..." question style. Avoid formulas, cliches, and any repetitive opening style. Show lots of personality and variety.
- If it feels natural, you can include 1 or 2 emoji in the bio to add personality or mood—don't force emoji into every bio, just use them when it fits.
- Avoid fantasy, poetry, or roleplay storytelling styles. Language should feel natural, human, easygoing, and fun—not scripted or safe.
- Maximum 3 sentences. No hashtags, numbers, usernames, birthdays, or exact cities—just general place if needed (like "from Texas", "from Europe"). Mention interests, activities, what she likes to do on cam, hobbies, quirks, or favorites as fits.
- End the bio with a new sentence as a call to action: "To see more, [visit my full profile here]($model_url)!" using Markdown link, upbeat and inviting.
- Do NOT repeat openings like 'Hey there!', 'Hi there!', or any one style for every bio. Mix it up every time.
- Output only a single, complete bio—do not include multiple bios, notes, variants, or explanations.

Below are a few sample bios for inspiration ONLY (write just one, and not as a question!):
- {$used_examples[0]}
- {$used_examples[1]}
- {$used_examples[2]}
- {$used_examples[3]}
- {$used_examples[4]}

Model info for inspiration:
Gender: $gender
Country: $country
Location: $location
Languages: $languages
Room topic: $room_subject
Tags: $tags_str

Output only your single final bio, nothing else.
EOT;

    $temperature = rand(11, 13) / 10; // 1.1–1.3
    $top_p = rand(92, 100) / 100;     // 0.92–1.0

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