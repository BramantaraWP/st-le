<?php
/*
================================================
🤖 WASMER AI ENGINE v1.0
Storage: Telegram Chat ID -1003557840518
Deploy: Wasmer Only
================================================
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// ================= CONFIGURATION =================
define('BOT_TOKEN', '8337490666:AAHhTs1w57Ynqs70GP3579IHqo491LHaCl8');
define('CHAT_ID', '-1003557840518'); // CHAT ID BARU
define('MEMORY_FILE', 'ai_memory.json');

// ================= SIMPLE AI ENGINE =================
class WasmerAI {
    private $memory = [];
    private $learning_rate = 0.1;
    
    public function __construct() {
        $this->loadMemory();
        $this->log("🤖 AI initialized with " . count($this->memory) . " memories");
    }
    
    // ================= MEMORY MANAGEMENT =================
    private function loadMemory() {
        // Load from Telegram first
        $this->loadFromTelegram();
        
        // If empty, try local file
        if (empty($this->memory) && file_exists(MEMORY_FILE)) {
            $data = json_decode(file_get_contents(MEMORY_FILE), true);
            if ($data && is_array($data)) {
                $this->memory = $data;
                $this->log("📂 Loaded from local cache");
            }
        }
    }
    
    private function loadFromTelegram() {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatHistory";
        $params = [
            'chat_id' => CHAT_ID,
            'limit' => 1000
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url . '?' . http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['result'])) {
                foreach ($data['result'] as $message) {
                    if (isset($message['text'])) {
                        $this->parseMessage($message['text']);
                    }
                }
                $this->log("✅ Loaded " . count($this->memory) . " memories from Telegram");
            }
        }
    }
    
    private function parseMessage($text) {
        // Format: PATTERN|RESPONSE|STRENGTH|TIMESTAMP
        if (strpos($text, '|') !== false) {
            $parts = explode('|', $text);
            if (count($parts) >= 4) {
                $this->memory[] = [
                    'id' => uniqid(),
                    'pattern' => $parts[0],
                    'response' => $parts[1],
                    'strength' => (float)$parts[2],
                    'timestamp' => (int)$parts[3],
                    'type' => 'memory'
                ];
            }
        }
        // Format JSON untuk complex data
        elseif (strpos($text, 'JSON:') === 0) {
            $json = substr($text, 5);
            $data = json_decode($json, true);
            if ($data && is_array($data)) {
                if (isset($data['type']) && $data['type'] === 'memory_array') {
                    foreach ($data['memories'] as $memory) {
                        $this->memory[] = $memory;
                    }
                }
            }
        }
    }
    
    private function saveToTelegram($data) {
        if (is_array($data)) {
            // Jika banyak data, simpan sebagai JSON
            if (count($data) > 1) {
                $json_data = [
                    'type' => 'memory_array',
                    'memories' => $data,
                    'saved_at' => time()
                ];
                $text = "JSON:" . json_encode($json_data, JSON_UNESCAPED_UNICODE);
            } else {
                // Single memory format
                $memory = $data[0];
                $text = implode('|', [
                    $memory['pattern'],
                    $memory['response'],
                    $memory['strength'],
                    time()
                ]);
            }
        } else {
            $text = $data;
        }
        
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $payload = [
            'chat_id' => CHAT_ID,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }
    
    // ================= AI THINKING =================
    public function think($input) {
        $input = trim($input);
        
        if (empty($input)) {
            return "Kasih pertanyaan dong bro!";
        }
        
        $this->log("🧠 Processing: " . substr($input, 0, 50) . "...");
        
        // 1. Exact match
        foreach ($this->memory as $mem) {
            if (strtolower($mem['pattern']) === strtolower($input)) {
                $this->strengthenMemory($mem['id']);
                return $mem['response'];
            }
        }
        
        // 2. Similarity match
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($this->memory as $mem) {
            $score = $this->calculateSimilarity($input, $mem['pattern']);
            $adjustedScore = $score * (1 + ($mem['strength'] * 0.1));
            
            if ($adjustedScore > $bestScore && $adjustedScore > 0.6) {
                $bestScore = $adjustedScore;
                $bestMatch = $mem;
            }
        }
        
        if ($bestMatch) {
            $this->strengthenMemory($bestMatch['id']);
            $this->log("✅ Found match (Score: " . round($bestScore*100) . "%)");
            return $bestMatch['response'];
        }
        
        // 3. Create new memory
        $this->log("🆕 Creating new memory...");
        return $this->createMemory($input);
    }
    
    private function calculateSimilarity($text1, $text2) {
        $words1 = array_unique(preg_split('/\s+/', strtolower($text1)));
        $words2 = array_unique(preg_split('/\s+/', strtolower($text2)));
        
        if (empty($words1) || empty($words2)) return 0;
        
        $common = count(array_intersect($words1, $words2));
        $total = count(array_unique(array_merge($words1, $words2)));
        
        return $common / $total;
    }
    
    private function createMemory($pattern) {
        $response = $this->generateResponse($pattern);
        
        $newMemory = [
            'id' => uniqid(),
            'pattern' => $pattern,
            'response' => $response,
            'strength' => 1.0,
            'timestamp' => time(),
            'type' => 'memory'
        ];
        
        $this->memory[] = $newMemory;
        $this->saveToTelegram([$newMemory]);
        $this->saveLocal();
        
        return $response;
    }
    
    private function generateResponse($pattern) {
        $pattern_lower = strtolower($pattern);
        
        // Detect intent
        if (preg_match('/php|kode|program|coding|function|class|method/', $pattern_lower)) {
            $responses = [
                "Wah pertanyaan coding! Mau bikin apa nih?",
                "Koding ya? Pake bahasa apa?",
                "Nih contoh sederhana:\n```php\necho 'Hello AI!';\n```",
                "Share detailnya dong, biar gw bisa bantu lebih spesifik!"
            ];
        }
        elseif (preg_match('/halo|hai|hi|hey|bro|woi|p|hallo/', $pattern_lower)) {
            $responses = [
                "Yo bro! Ada yang bisa dibantu?",
                "Hai! Gw AI di Wasmer, siap belajar bareng!",
                "Woi! Gimana kabarnya hari ini?",
                "Halo! Ready untuk training atau chat!"
            ];
        }
        elseif (preg_match('/\?/', $pattern)) {
            $responses = [
                "Pertanyaan menarik! Gw masih berkembang nih.",
                "Wah, gw belum tau jawaban pastinya. Lu tau ga?",
                "Bisa kasih tau jawaban yang bener? Nanti gw inget!",
                "Gw cari dulu ya, atau lu ajarin gw?"
            ];
        }
        elseif (preg_match('/train|ajar|belajar|learning/', $pattern_lower)) {
            $responses = [
                "Mau ajarin gw? Kasih contoh dong!",
                "Training mode activated! Kasih pattern dan response-nya!",
                "Gw siap belajar. Format: pattern|response",
                "Ajarin gw biar makin pinter!"
            ];
        }
        else {
            $responses = [
                "Menarik! Bisa kasih konteks lebih?",
                "Wah ini baru. Gw simpan dulu ya...",
                "Oke, gw catet. Ada tambahan info?",
                "Belum pernah denger ini. Kasih contoh respon yang tepat dong!"
            ];
        }
        
        return $responses[array_rand($responses)];
    }
    
    private function strengthenMemory($id) {
        foreach ($this->memory as &$mem) {
            if ($mem['id'] === $id) {
                $mem['strength'] += $this->learning_rate;
                $mem['timestamp'] = time();
                $this->log("💪 Memory strengthened: " . substr($mem['pattern'], 0, 30) . "...");
                break;
            }
        }
        $this->saveLocal();
    }
    
    // ================= TRAINING =================
    public function train($pattern, $response) {
        $pattern = trim($pattern);
        $response = trim($response);
        
        if (empty($pattern) || empty($response)) {
            return "Pattern dan response harus diisi!";
        }
        
        $this->log("🎓 Training: " . substr($pattern, 0, 50) . "...");
        
        // Check if exists
        foreach ($this->memory as &$mem) {
            if ($this->calculateSimilarity($pattern, $mem['pattern']) > 0.8) {
                $mem['response'] = $response;
                $mem['strength'] += 2.0; // Boost for manual training
                $mem['timestamp'] = time();
                
                $this->saveToTelegram([$mem]);
                $this->saveLocal();
                
                return "✅ Updated existing memory!";
            }
        }
        
        // Create new
        $newMemory = [
            'id' => uniqid(),
            'pattern' => $pattern,
            'response' => $response,
            'strength' => 3.0, // Higher for manual training
            'timestamp' => time(),
            'type' => 'memory'
        ];
        
        $this->memory[] = $newMemory;
        $this->saveToTelegram([$newMemory]);
        $this->saveLocal();
        
        return "✅ Created new memory with boosted strength!";
    }
    
    public function batchTrain($data) {
        $results = [];
        foreach ($data as $item) {
            if (isset($item['pattern']) && isset($item['response'])) {
                $results[] = $this->train($item['pattern'], $item['response']);
            }
        }
        
        // Save all memories to Telegram as JSON
        if (!empty($this->memory)) {
            $this->saveToTelegram($this->memory);
        }
        
        return [
            'total' => count($results),
            'results' => $results
        ];
    }
    
    // ================= UTILITIES =================
    public function getStats() {
        $strengths = array_column($this->memory, 'strength');
        $timestamps = array_column($this->memory, 'timestamp');
        
        return [
            'total_memories' => count($this->memory),
            'average_strength' => count($strengths) > 0 ? round(array_sum($strengths) / count($strengths), 2) : 0,
            'strongest_memory' => count($strengths) > 0 ? max($strengths) : 0,
            'oldest_memory' => count($timestamps) > 0 ? date('Y-m-d H:i:s', min($timestamps)) : 'None',
            'newest_memory' => count($timestamps) > 0 ? date('Y-m-d H:i:s', max($timestamps)) : 'None',
            'memory_size' => strlen(json_encode($this->memory)) . ' bytes',
            'learning_rate' => $this->learning_rate,
            'server_time' => date('Y-m-d H:i:s'),
            'wasmer_deployed' => true
        ];
    }
    
    public function search($keyword) {
        $results = [];
        $keyword_lower = strtolower($keyword);
        
        foreach ($this->memory as $mem) {
            if (stripos($mem['pattern'], $keyword_lower) !== false || 
                stripos($mem['response'], $keyword_lower) !== false) {
                $results[] = [
                    'pattern' => $mem['pattern'],
                    'response' => $mem['response'],
                    'strength' => $mem['strength'],
                    'age' => $this->formatAge($mem['timestamp'])
                ];
            }
        }
        
        return $results;
    }
    
    private function formatAge($timestamp) {
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
        
        return date('Y-m-d', $timestamp);
    }
    
    public function export() {
        return [
            'memories' => $this->memory,
            'exported_at' => time(),
            'total' => count($this->memory),
            'version' => 'wasmer_ai_v1.0'
        ];
    }
    
    public function cleanup($threshold = 0.5) {
        $before = count($this->memory);
        $this->memory = array_filter($this->memory, function($mem) use ($threshold) {
            return $mem['strength'] > $threshold;
        });
        $after = count($this->memory);
        
        $this->saveLocal();
        $this->saveToTelegram($this->memory);
        
        return "🧹 Cleaned up! Removed " . ($before - $after) . " weak memories. Now have $after memories.";
    }
    
    // ================= LOCAL STORAGE =================
    private function saveLocal() {
        file_put_contents(MEMORY_FILE, json_encode($this->memory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    private function log($message) {
        // Simple logging
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        }
        // For HTTP, we can store in session or ignore
    }
}

// ================= API HANDLER =================
function handleRequest() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Also check POST data
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }
    
    // Default action
    $action = $input['action'] ?? ($_GET['action'] ?? 'think');
    
    $ai = new WasmerAI();
    $response = [];
    
    try {
        switch ($action) {
            case 'think':
                $message = $input['message'] ?? ($_GET['message'] ?? '');
                $response = ['response' => $ai->think($message)];
                break;
                
            case 'train':
                $pattern = $input['pattern'] ?? '';
                $response_text = $input['response'] ?? '';
                $response = ['result' => $ai->train($pattern, $response_text)];
                break;
                
            case 'batch_train':
                $data = $input['data'] ?? [];
                $response = $ai->batchTrain($data);
                break;
                
            case 'stats':
                $response = $ai->getStats();
                break;
                
            case 'search':
                $keyword = $input['keyword'] ?? ($_GET['keyword'] ?? '');
                $response = ['results' => $ai->search($keyword)];
                break;
                
            case 'export':
                $response = $ai->export();
                break;
                
            case 'cleanup':
                $threshold = $input['threshold'] ?? 0.5;
                $response = ['result' => $ai->cleanup($threshold)];
                break;
                
            case 'health':
                $response = [
                    'status' => 'healthy',
                    'timestamp' => time(),
                    'endpoint' => 'https://generatelanguage.ct.ws/wasmer_ai_engine.php',
                    'storage' => 'Telegram Chat ID: ' . CHAT_ID,
                    'memory_count' => count((new ReflectionProperty('WasmerAI', 'memory'))->getValue($ai))
                ];
                break;
                
            default:
                $response = [
                    'error' => 'Unknown action',
                    'available_actions' => [
                        'think', 'train', 'batch_train', 'stats', 
                        'search', 'export', 'cleanup', 'health'
                    ]
                ];
        }
    } catch (Exception $e) {
        $response = ['error' => $e->getMessage()];
    }
    
    return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ================= MAIN EXECUTION =================
if (php_sapi_name() === 'cli') {
    // CLI Mode
    echo "🤖 WASMER AI ENGINE - CLI Mode\n";
    echo "===============================\n\n";
    
    $ai = new WasmerAI();
    
    if ($argc > 1) {
        switch ($argv[1]) {
            case 'think':
                if ($argc > 2) {
                    echo "AI: " . $ai->think($argv[2]) . "\n";
                }
                break;
            case 'train':
                if ($argc > 4) {
                    echo $ai->train($argv[2], $argv[3]) . "\n";
                }
                break;
            case 'stats':
                print_r($ai->getStats());
                break;
            case 'interactive':
                while (true) {
                    echo "\nYou: ";
                    $input = trim(fgets(STDIN));
                    if (strtolower($input) === 'exit') break;
                    echo "AI: " . $ai->think($input) . "\n";
                }
                break;
        }
    } else {
        echo "Usage:\n";
        echo "  php wasmer_ai_engine.php think \"message\"\n";
        echo "  php wasmer_ai_engine.php train \"pattern\" \"response\"\n";
        echo "  php wasmer_ai_engine.php stats\n";
        echo "  php wasmer_ai_engine.php interactive\n";
    }
} else {
    // HTTP Mode
    echo handleRequest();
}
