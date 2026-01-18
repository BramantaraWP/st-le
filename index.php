<?php
/*
===============================================
🧠 PURE NEURAL NETWORK AI - FIXED VERSION
===============================================
*/

// START BUFFERING - Capture semua output
ob_start();

// Set headers pertama
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

// ================= CONFIGURATION =================
define('BOT_TOKEN', '8337490666:AAHhTs1w57Ynqs70GP3579IHqo491LHaCl8');
define('CHAT_ID', '-1003557840518');
define('VOCAB_SIZE', 1000);
define('HIDDEN_LAYERS', 2);
define('LEARNING_RATE', 0.1);

// ================= ERROR HANDLING =================
function jsonError($message) {
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

// ================= NEURAL NETWORK CORE =================
class NeuralAI {
    private $vocabulary = [];
    private $word_vectors = [];
    private $synapses = [];
    private $patterns = [];
    private $weights = [];
    private $learning_rate = LEARNING_RATE;
    
    public function __construct() {
        $this->loadNeuralData();
    }
    
    private function textToVector($text) {
        $words = $this->tokenize($text);
        $vector = array_fill(0, VOCAB_SIZE, 0);
        
        foreach ($words as $word) {
            $index = $this->getWordIndex($word);
            if ($index < VOCAB_SIZE && $index >= 0) {
                $vector[$index] = 1;
            }
        }
        
        return $vector;
    }
    
    private function forwardPropagation($input_vector) {
        $layer_output = $input_vector;
        
        for ($i = 0; $i < HIDDEN_LAYERS; $i++) {
            if (!isset($this->weights[$i])) {
                $this->initializeWeights($i, count($layer_output), 128);
            }
            
            $layer_output = $this->sigmoid(
                $this->matrixMultiply($layer_output, $this->weights[$i])
            );
        }
        
        return $layer_output;
    }
    
    private function vectorToText($output_vector) {
        if (!is_array($output_vector)) {
            return $this->getDefaultResponse();
        }
        
        arsort($output_vector);
        $top_indices = array_slice(array_keys($output_vector), 0, 10, true);
        
        $words = [];
        foreach ($top_indices as $index => $value) {
            if ($value > 0.3 && isset($this->vocabulary[$index])) {
                $words[] = $this->vocabulary[$index];
            }
        }
        
        if (empty($words)) {
            return $this->getDefaultResponse();
        }
        
        return $this->generateSentence($words);
    }
    
    private function generateSentence($words) {
        if (empty($words)) {
            return $this->getDefaultResponse();
        }
        
        $patterns = [
            "Wah tentang " . implode(", ", array_slice($words, 0, 3)) . " nih!",
            "Menarik! " . ucfirst($words[0]) . " itu berkaitan dengan " . implode(" dan ", array_slice($words, 1, 2)),
            ucfirst($words[0]) . " ya? Bisa dijelaskan lebih detail?",
            "Kalau " . $words[0] . ", biasanya terkait " . implode(", ", array_slice($words, 1, 2)),
            "Pertanyaan tentang " . implode(" dan ", array_slice($words, 0, 2)) . " ya?"
        ];
        
        $word_string = implode(" ", $words);
        
        if (preg_match('/php|javascript|python|java|kode|program/i', $word_string)) {
            $patterns[] = "Wah bahas programming! " . ucfirst($words[0]) . " itu penting dalam development.";
        }
        
        if (preg_match('/ai|neural|machine|learning|bot/i', $word_string)) {
            $patterns[] = "AI ya? " . ucfirst($words[0]) . " adalah konsep penting.";
        }
        
        return $patterns[array_rand($patterns)];
    }
    
    public function think($input) {
        $input = trim($input);
        
        if (empty($input)) {
            return "Kasih input dong bro!";
        }
        
        try {
            $input_vector = $this->textToVector($input);
            $neural_output = $this->forwardPropagation($input_vector);
            $response = $this->vectorToText($neural_output);
            $this->learnPattern($input, $neural_output);
            
            return $response;
        } catch (Exception $e) {
            return "Neural network error: " . $e->getMessage();
        }
    }
    
    private function learnPattern($input, $output_vector) {
        $words = $this->tokenize($input);
        
        $pattern_id = md5($input);
        $this->patterns[$pattern_id] = [
            'words' => $words,
            'vector' => $this->textToVector($input),
            'strength' => 1,
            'timestamp' => time()
        ];
        
        foreach ($words as $word) {
            $index = $this->getWordIndex($word);
            if ($index >= 0 && $index < VOCAB_SIZE) {
                for ($i = 0; $i < min(count($output_vector), 50); $i++) {
                    if ($output_vector[$i] > 0.3) {
                        $this->strengthenConnection($index, $i, $output_vector[$i]);
                    }
                }
            }
        }
        
        $this->saveNeuralData();
    }
    
    private function strengthenConnection($from, $to, $strength) {
        if (!isset($this->synapses[$from])) {
            $this->synapses[$from] = [];
        }
        
        if (!isset($this->synapses[$from][$to])) {
            $this->synapses[$from][$to] = 0;
        }
        
        $this->synapses[$from][$to] += $strength * $this->learning_rate;
    }
    
    private function getWordIndex($word) {
        $word = strtolower(trim($word));
        
        if (strlen($word) < 2) return -1;
        
        $index = array_search($word, $this->vocabulary);
        
        if ($index === false) {
            if (count($this->vocabulary) < VOCAB_SIZE) {
                $this->vocabulary[] = $word;
                return count($this->vocabulary) - 1;
            } else {
                return rand(0, VOCAB_SIZE - 1);
            }
        }
        
        return $index;
    }
    
    private function tokenize($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $stop_words = ['yang', 'dengan', 'untuk', 'dari', 'pada', 'dan', 'atau', 'tapi', 'adalah', 'itu', 'ini', 'saya', 'kamu', 'dia', 'kita', 'mereka', 'di', 'ke', 'dari'];
        $words = array_diff($words, $stop_words);
        
        return array_values($words);
    }
    
    private function initializeWeights($layer, $input_size, $output_size) {
        $weights = [];
        
        for ($i = 0; $i < $input_size; $i++) {
            for ($j = 0; $j < $output_size; $j++) {
                $weights[$i][$j] = (rand() / getrandmax()) * 0.1 - 0.05;
            }
        }
        
        $this->weights[$layer] = $weights;
    }
    
    private function matrixMultiply($vector, $matrix) {
        $result = array_fill(0, count($matrix[0]), 0);
        
        foreach ($vector as $i => $value) {
            if (isset($matrix[$i])) {
                foreach ($matrix[$i] as $j => $weight) {
                    $result[$j] += $value * $weight;
                }
            }
        }
        
        return $result;
    }
    
    private function sigmoid($x) {
        if (is_array($x)) {
            return array_map(function($val) {
                return 1 / (1 + exp(-$val));
            }, $x);
        }
        return 1 / (1 + exp(-$x));
    }
    
    private function getDefaultResponse() {
        $responses = [
            "Menarik! Bisa jelasin lebih detail?",
            "Wah ini baru. Ajarin dong tentang ini!",
            "Oke, gw simpan pola katanya.",
            "Belum pernah dengar kombinasi kata ini.",
            "Gw catet pola ini. Makin banyak data, makin pinter nih!"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    // ================= TRAINING =================
    
    public function train($pattern) {
        $words = $this->tokenize($pattern);
        
        if (count($words) < 1) {
            return "Pattern harus ada kata-katanya bro!";
        }
        
        $vector = $this->textToVector($pattern);
        $output = $this->forwardPropagation($vector);
        $this->learnPattern($pattern, $output);
        $this->updateWeights($vector, $output);
        
        return "✅ Neural network trained with pattern: " . implode(" ", array_slice($words, 0, 5)) . "...";
    }
    
    private function updateWeights($input_vector, $output_vector) {
        for ($layer = 0; $layer < HIDDEN_LAYERS; $layer++) {
            if (isset($this->weights[$layer])) {
                for ($i = 0; $i < count($this->weights[$layer]); $i++) {
                    for ($j = 0; $j < count($this->weights[$layer][$i]); $j++) {
                        $delta = $output_vector[$j] * $this->learning_rate;
                        $this->weights[$layer][$i][$j] += $delta;
                        $this->weights[$layer][$i][$j] = max(-1, min(1, $this->weights[$layer][$i][$j]));
                    }
                }
            }
        }
    }
    
    // ================= DATA MANAGEMENT =================
    
    private function saveNeuralData() {
        $data = [
            'vocabulary' => $this->vocabulary,
            'synapses' => $this->synapses,
            'patterns' => $this->patterns,
            'weights' => $this->weights,
            'saved_at' => time()
        ];
        
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Save locally
        file_put_contents('neural_data.json', $json_data);
        
        // Save to Telegram (async)
        $this->asyncSaveToTelegram($json_data);
    }
    
    private function asyncSaveToTelegram($data) {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $payload = [
            'chat_id' => CHAT_ID,
            'text' => "NEURAL_DATA:" . $data,
            'parse_mode' => 'HTML'
        ];
        
        // Async request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 1, // Very short timeout for async
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    private function loadNeuralData() {
        // Try local file
        if (file_exists('neural_data.json')) {
            $json = file_get_contents('neural_data.json');
            $data = json_decode($json, true);
            
            if ($data && is_array($data)) {
                $this->vocabulary = $data['vocabulary'] ?? [];
                $this->synapses = $data['synapses'] ?? [];
                $this->patterns = $data['patterns'] ?? [];
                $this->weights = $data['weights'] ?? [];
                return;
            }
        }
        
        // Try Telegram
        $this->loadFromTelegram();
    }
    
    private function loadFromTelegram() {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatHistory";
        $params = [
            'chat_id' => CHAT_ID,
            'limit' => 50
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
            if (isset($data['result']) && is_array($data['result'])) {
                foreach ($data['result'] as $message) {
                    if (isset($message['text']) && strpos($message['text'], 'NEURAL_DATA:') === 0) {
                        $neural_data = json_decode(substr($message['text'], 12), true);
                        if ($neural_data) {
                            $this->vocabulary = $neural_data['vocabulary'] ?? [];
                            $this->synapses = $neural_data['synapses'] ?? [];
                            $this->patterns = $neural_data['patterns'] ?? [];
                            $this->weights = $neural_data['weights'] ?? [];
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // ================= UTILITIES =================
    
    public function getStats() {
        $word_counts = array_count_values($this->vocabulary);
        arsort($word_counts);
        $top_words = array_slice($word_counts, 0, 10, true);
        
        return [
            'vocabulary_size' => count($this->vocabulary),
            'patterns_learned' => count($this->patterns),
            'synapse_count' => $this->countSynapses(),
            'top_words' => $top_words,
            'hidden_layers' => HIDDEN_LAYERS,
            'learning_rate' => $this->learning_rate,
            'neural_status' => 'active',
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ];
    }
    
    private function countSynapses() {
        $total = 0;
        foreach ($this->synapses as $connections) {
            $total += count($connections);
        }
        return $total;
    }
    
    public function searchPatterns($keyword) {
        $results = [];
        $keyword_lower = strtolower($keyword);
        
        foreach ($this->patterns as $pattern) {
            $words = implode(' ', $pattern['words']);
            if (stripos($words, $keyword_lower) !== false) {
                $results[] = [
                    'words' => $pattern['words'],
                    'strength' => $pattern['strength'],
                    'age' => $this->formatAge($pattern['timestamp'])
                ];
            }
        }
        
        return $results;
    }
    
    private function formatAge($timestamp) {
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        
        return floor($diff / 86400) . ' hari lalu';
    }
    
    public function exportData() {
        return [
            'vocabulary' => $this->vocabulary,
            'patterns' => $this->patterns,
            'synapses' => $this->synapses,
            'weights' => $this->weights,
            'exported_at' => time()
        ];
    }
}

// ================= API HANDLER =================
function handleAPI() {
    // Clear any previous output
    ob_clean();
    
    $ai = new NeuralAI();
    
    // Get input - handle both POST and GET
    $input = [];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST)) {
            $input = $_POST;
        } else {
            // Try to get raw POST data
            $raw_input = file_get_contents('php://input');
            if (!empty($raw_input)) {
                parse_str($raw_input, $input);
            }
        }
    } else {
        $input = $_GET;
    }
    
    $action = $input['action'] ?? 'think';
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
                $response = ['results' => $ai->searchPatterns($keyword)];
                break;
                
            case 'export':
                $response = $ai->exportData();
                break;
                
            case 'neural_status':
                $response = [
                    'status' => 'neural_network_active',
                    'vocabulary_size' => count($ai->vocabulary),
                    'timestamp' => time()
                ];
                break;
                
            case 'test':
                $response = [
                    'status' => 'ok',
                    'message' => 'Neural AI is working',
                    'time' => date('Y-m-d H:i:s')
                ];
                break;
                
            default:
                $response = ['error' => 'Unknown action', 'valid_actions' => ['think', 'train', 'stats', 'search', 'export', 'neural_status', 'test']];
        }
    } catch (Exception $e) {
        $response = ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()];
    }
    
    // Ensure JSON output
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// ================= EXECUTION =================
if (php_sapi_name() === 'cli') {
    // CLI Mode - allow output
    ob_end_clean();
    
    echo "🧠 NEURAL NETWORK AI - CLI Mode\n";
    echo "===============================\n\n";
    
    $ai = new NeuralAI();
    
    if ($argc > 1) {
        switch ($argv[1]) {
            case 'think':
                if ($argc > 2) {
                    $message = implode(' ', array_slice($argv, 2));
                    echo "AI: " . $ai->think($message) . "\n";
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
                echo "🧪 Testing neural network...\n";
                echo "Think 'hello': " . $ai->think('hello') . "\n";
                echo "Think 'php python': " . $ai->think('php python') . "\n";
                echo "Think 'ai neural': " . $ai->think('ai neural') . "\n";
                break;
                
            case 'interactive':
                while (true) {
                    echo "\nYou: ";
                    $input = trim(fgets(STDIN));
                    
                    if (strtolower($input) === 'exit') break;
                    if (strtolower($input) === 'stats') {
                        print_r($ai->getStats());
                        continue;
                    }
                    
                    echo "AI: " . $ai->think($input) . "\n";
                }
                break;
                
            default:
                echo "Usage:\n";
                echo "  php neural_ai_engine.php think \"message\"\n";
                echo "  php neural_ai_engine.php train \"pattern words\"\n";
                echo "  php neural_ai_engine.php stats\n";
                echo "  php neural_ai_engine.php test\n";
                echo "  php neural_ai_engine.php interactive\n";
        }
    } else {
        echo "Usage:\n";
        echo "  php neural_ai_engine.php think \"message\"\n";
        echo "  php neural_ai_engine.php train \"pattern words\"\n";
        echo "  php neural_ai_engine.php stats\n";
        echo "  php neural_ai_engine.php test\n";
        echo "  php neural_ai_engine.php interactive\n";
    }
} else {
    // HTTP Mode - no extra output
    handleAPI();
    ob_end_flush();
}
