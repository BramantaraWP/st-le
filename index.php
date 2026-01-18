<?php
/*
===============================================
🧠 NEURAL NETWORK AI - WASMER COMPATIBLE
NO SELECT, NO SQL-LIKE OPERATIONS
===============================================
*/

// CLEAN OUTPUT BUFFER
if (ob_get_level()) ob_clean();

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

// ================= CONFIGURATION =================
define('BOT_TOKEN', '8337490666:AAHhTs1w57Ynqs70GP3579IHqo491LHaCl8');
define('CHAT_ID', '-1003557840518');
define('VOCAB_SIZE', 500);  // Reduced for Wasmer
define('HIDDEN_LAYERS', 1); // Reduced for Wasmer
define('LEARNING_RATE', 0.05);

// ================= NEURAL NETWORK =================
class NeuralAI {
    private $vocabulary = [];
    private $synapses = [];
    private $patterns = [];
    private $word_frequency = [];
    private $learning_rate = LEARNING_RATE;
    
    public function __construct() {
        $this->loadNeuralData();
    }
    
    // ================= TEXT PROCESSING =================
    private function tokenize($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Remove stop words
        $stop_words = ['yang', 'dengan', 'untuk', 'dari', 'pada', 'dan', 'atau', 'tapi', 'adalah', 'itu'];
        $words = array_diff($words, $stop_words);
        
        // Update frequency
        foreach ($words as $word) {
            if (strlen($word) > 1) {
                $this->word_frequency[$word] = ($this->word_frequency[$word] ?? 0) + 1;
            }
        }
        
        return array_values($words);
    }
    
    private function getWordIndex($word) {
        $word = strtolower($word);
        if (strlen($word) < 2) return -1;
        
        $index = array_search($word, $this->vocabulary);
        
        if ($index === false) {
            if (count($this->vocabulary) < VOCAB_SIZE) {
                $this->vocabulary[] = $word;
                return count($this->vocabulary) - 1;
            }
            return -1;
        }
        
        return $index;
    }
    
    private function textToVector($text) {
        $words = $this->tokenize($text);
        $vector = array_fill(0, VOCAB_SIZE, 0);
        
        foreach ($words as $word) {
            $index = $this->getWordIndex($word);
            if ($index >= 0 && $index < VOCAB_SIZE) {
                $vector[$index] = 1;
            }
        }
        
        return $vector;
    }
    
    // ================= NEURAL OPERATIONS =================
    private function neuralProcess($input_vector) {
        // Simple neural activation (no matrix multiplication to avoid SELECT-like ops)
        $output = array_fill(0, VOCAB_SIZE, 0);
        
        foreach ($input_vector as $i => $val) {
            if ($val > 0) {
                // Activate connected words
                if (isset($this->synapses[$i])) {
                    foreach ($this->synapses[$i] as $j => $strength) {
                        $output[$j] += $strength;
                    }
                }
            }
        }
        
        // Normalize
        $max = max($output);
        if ($max > 0) {
            foreach ($output as &$val) {
                $val = $val / $max;
            }
        }
        
        return $output;
    }
    
    private function generateResponse($output_vector) {
        // Get top activated words
        arsort($output_vector);
        $top_words = [];
        
        foreach ($output_vector as $index => $activation) {
            if ($activation > 0.3 && isset($this->vocabulary[$index])) {
                $top_words[] = $this->vocabulary[$index];
                if (count($top_words) >= 5) break;
            }
        }
        
        if (empty($top_words)) {
            return $this->getDefaultResponse();
        }
        
        return $this->makeSentence($top_words);
    }
    
    private function makeSentence($words) {
        $templates = [
            "Wah tentang " . implode(", ", array_slice($words, 0, 3)) . " ya?",
            ucfirst($words[0]) . " itu berkaitan dengan " . implode(" dan ", array_slice($words, 1, 2)),
            "Menarik kombinasi kata " . implode(", ", $words) . "!",
            "Pola " . implode("-", array_slice($words, 0, 2)) . " sering muncul nih.",
            "Kata kunci: " . implode(", ", $words) . ". Mau bahas yang mana?"
        ];
        
        // Check for programming terms
        $all_words = implode(" ", $words);
        if (preg_match('/php|javascript|python|java|kode|program/i', $all_words)) {
            $templates[] = "Wah bahas programming! " . ucfirst($words[0]) . " itu bahasa yang powerful.";
        }
        
        if (preg_match('/ai|neural|machine|learning|bot/i', $all_words)) {
            $templates[] = "AI ya? " . ucfirst($words[0]) . " bagian dari machine learning.";
        }
        
        return $templates[array_rand($templates)];
    }
    
    private function getDefaultResponse() {
        $responses = [
            "Menarik pola katanya! Bisa kasih konteks?",
            "Wah kombinasi baru nih. Gw catet dulu!",
            "Oke, neural network gw proses ini.",
            "Makasih inputnya! Gw belajar dari sini.",
            "Pola kata dicatat. Makin banyak data, makin pinter!"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    // ================= MAIN FUNCTIONS =================
    public function think($input) {
        $input = trim($input);
        
        if (empty($input)) {
            return "Kasih input dong bro!";
        }
        
        try {
            // Convert to vector
            $vector = $this->textToVector($input);
            
            // Neural process
            $output = $this->neuralProcess($vector);
            
            // Generate response
            $response = $this->generateResponse($output);
            
            // Learn from interaction
            $this->learn($input, $output);
            
            return $response;
            
        } catch (Exception $e) {
            return "AI error: " . $e->getMessage();
        }
    }
    
    private function learn($input, $output_vector) {
        $words = $this->tokenize($input);
        
        // Create connections between words
        for ($i = 0; $i < count($words); $i++) {
            for ($j = $i + 1; $j < count($words); $j++) {
                $word1 = $words[$i];
                $word2 = $words[$j];
                
                $idx1 = $this->getWordIndex($word1);
                $idx2 = $this->getWordIndex($word2);
                
                if ($idx1 >= 0 && $idx2 >= 0) {
                    // Strengthen connection
                    if (!isset($this->synapses[$idx1])) {
                        $this->synapses[$idx1] = [];
                    }
                    if (!isset($this->synapses[$idx1][$idx2])) {
                        $this->synapses[$idx1][$idx2] = 0;
                    }
                    $this->synapses[$idx1][$idx2] += $this->learning_rate;
                }
            }
        }
        
        // Save learning
        $this->saveData();
    }
    
    public function train($pattern) {
        $words = $this->tokenize($pattern);
        
        if (count($words) < 1) {
            return "Pattern butuh kata-kata bro!";
        }
        
        $vector = $this->textToVector($pattern);
        $output = $this->neuralProcess($vector);
        $this->learn($pattern, $output);
        
        return "✅ Neural trained: " . implode(" ", array_slice($words, 0, 3)) . "...";
    }
    
    // ================= DATA STORAGE =================
    private function saveData() {
        $data = [
            'vocabulary' => $this->vocabulary,
            'synapses' => $this->synapses,
            'word_frequency' => $this->word_frequency,
            'patterns' => $this->patterns,
            'timestamp' => time()
        ];
        
        // Save locally (file_put_contents is allowed)
        file_put_contents('neural_cache.dat', serialize($data));
        
        // Async save to Telegram using curl (not file_get_contents)
        $this->asyncSaveToTelegram($data);
    }
    
    private function asyncSaveToTelegram($data) {
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $message = "NEURAL:" . $json_data;
        
        // Use curl instead of file_get_contents
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'chat_id' => CHAT_ID,
                'text' => substr($message, 0, 4000) // Telegram limit
            ],
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    private function loadNeuralData() {
        // Try local cache first
        if (file_exists('neural_cache.dat')) {
            $data = unserialize(file_get_contents('neural_cache.dat'));
            if ($data) {
                $this->vocabulary = $data['vocabulary'] ?? [];
                $this->synapses = $data['synapses'] ?? [];
                $this->word_frequency = $data['word_frequency'] ?? [];
                $this->patterns = $data['patterns'] ?? [];
                return;
            }
        }
        
        // Initialize empty
        $this->vocabulary = [];
        $this->synapses = [];
        $this->word_frequency = [];
        $this->patterns = [];
    }
    
    // ================= UTILITIES =================
    public function getStats() {
        $top_words = $this->word_frequency;
        arsort($top_words);
        $top_words = array_slice($top_words, 0, 10, true);
        
        return [
            'vocabulary' => count($this->vocabulary),
            'synapses' => $this->countSynapses(),
            'top_words' => $top_words,
            'learning_rate' => $this->learning_rate,
            'status' => 'neural_active'
        ];
    }
    
    private function countSynapses() {
        $total = 0;
        foreach ($this->synapses as $connections) {
            $total += count($connections);
        }
        return $total;
    }
    
    public function search($keyword) {
        $results = [];
        $keyword_lower = strtolower($keyword);
        
        foreach ($this->vocabulary as $word) {
            if (stripos($word, $keyword_lower) !== false) {
                $results[] = [
                    'word' => $word,
                    'frequency' => $this->word_frequency[$word] ?? 0
                ];
            }
        }
        
        return array_slice($results, 0, 20);
    }
}

// ================= API HANDLER =================
function handleRequest() {
    // Clean any previous output
    if (ob_get_level()) ob_clean();
    
    // Get input safely
    $input = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST)) {
            $input = $_POST;
        } else {
            $raw = file_get_contents('php://input');
            if (!empty($raw)) {
                parse_str($raw, $input);
            }
        }
    } else {
        $input = $_GET;
    }
    
    // Default action
    $action = $input['action'] ?? 'think';
    
    // Initialize AI
    $ai = new NeuralAI();
    $response = [];
    
    try {
        switch ($action) {
            case 'think':
                $message = $input['message'] ?? '';
                $response = ['response' => $ai->think($message)];
                break;
                
            case 'train':
                $pattern = $input['pattern'] ?? '';
                $response = ['result' => $ai->train($pattern)];
                break;
                
            case 'stats':
                $response = $ai->getStats();
                break;
                
            case 'search':
                $keyword = $input['keyword'] ?? '';
                $response = ['results' => $ai->search($keyword)];
                break;
                
            case 'ping':
                $response = ['status' => 'alive', 'time' => time()];
                break;
                
            default:
                $response = ['error' => 'Unknown action', 'help' => 'Use: think, train, stats, search, ping'];
        }
    } catch (Exception $e) {
        $response = ['error' => $e->getMessage()];
    }
    
    // Ensure JSON output
    header('Content-Type: application/json');
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ================= EXECUTION =================
if (php_sapi_name() === 'cli') {
    // CLI Mode
    echo "🧠 NEURAL AI - CLI Mode\n";
    echo "=======================\n\n";
    
    $ai = new NeuralAI();
    
    if ($argc > 1) {
        switch ($argv[1]) {
            case 'think':
                if ($argc > 2) {
                    $msg = implode(' ', array_slice($argv, 2));
                    echo "AI: " . $ai->think($msg) . "\n";
                }
                break;
                
            case 'train':
                if ($argc > 2) {
                    $pattern = implode(' ', array_slice($argv, 2));
                    echo $ai->train($pattern) . "\n";
                }
                break;
                
            case 'stats':
                print_r($ai->getStats());
                break;
                
            case 'test':
                echo "Testing...\n";
                echo "1. " . $ai->think("hello") . "\n";
                echo "2. " . $ai->think("php javascript") . "\n";
                echo "3. " . $ai->think("neural ai") . "\n";
                break;
                
            default:
                echo "Commands:\n";
                echo "  think <message>\n";
                echo "  train <pattern>\n";
                echo "  stats\n";
                echo "  test\n";
        }
    } else {
        echo "Commands:\n";
        echo "  think <message>\n";
        echo "  train <pattern>\n";
        echo "  stats\n";
        echo "  test\n";
    }
} else {
    // HTTP Mode
    handleRequest();
}
