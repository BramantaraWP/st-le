<?php
/*
===============================================
🚀 WASMER PROXY FOR AI TRAINING
===============================================
Untuk bypass bandwidth limit dan IP blocking
===============================================
*/

class WasmerAITrainer {
    private string $ai_endpoint;
    private array $proxy_pool = [];
    private int $request_delay = 1;
    
    public function __construct(string $ai_endpoint) {
        $this->ai_endpoint = $ai_endpoint;
        $this->initProxyPool();
    }
    
    private function initProxyPool(): void {
        // Pool of User-Agents untuk rotate
        $this->proxy_pool = [
            'user_agents' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/537.36'
            ],
            'delay_variance' => [0.5, 1, 1.5, 2]
        ];
    }
    
    public function trainFromText(string $text, string $context = ''): array {
        // Extract Q&A pairs from text
        $qaPairs = $this->extractQAPairs($text);
        
        $results = [];
        foreach ($qaPairs as $qa) {
            $result = $this->sendTraining($qa['question'], $qa['answer']);
            $results[] = $result;
            
            // Random delay untuk avoid blocking
            usleep($this->request_delay * 1000000 * mt_rand(5, 15) / 10);
        }
        
        return $results;
    }
    
    private function extractQAPairs(string $text): array {
        $pairs = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        
        for ($i = 0; $i < count($sentences) - 1; $i++) {
            if (strlen($sentences[$i]) > 20 && strlen($sentences[$i + 1]) > 20) {
                $pairs[] = [
                    'question' => $this->createQuestion($sentences[$i]),
                    'answer' => $sentences[$i + 1]
                ];
            }
        }
        
        return $pairs;
    }
    
    private function createQuestion(string $sentence): string {
        // Convert statement to question
        if (preg_match('/(\w+) adalah (\w+)/', $sentence)) {
            return preg_replace('/(\w+) adalah (\w+)/', 'apa itu $1?', $sentence);
        }
        
        if (preg_match('/(\w+) dapat (\w+)/', $sentence)) {
            return preg_replace('/(\w+) dapat (\w+)/', 'bagaimana $1?', $sentence);
        }
        
        return "jelaskan tentang " . substr($sentence, 0, 50);
    }
    
    private function sendTraining(string $pattern, string $response): array {
        $payload = [
            'action' => 'train',
            'pattern' => $pattern,
            'response' => $response
        ];
        
        // Random User-Agent
        $userAgent = $this->proxy_pool['user_agents'][array_rand($this->proxy_pool['user_agents'])];
        
        $ch = curl_init($this->ai_endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-Forwarded-For: ' . $this->generateRandomIP()
            ]
        ]);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'pattern' => substr($pattern, 0, 50),
            'success' => $httpCode === 200,
            'code' => $httpCode
        ];
    }
    
    private function generateRandomIP(): string {
        return mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254) . '.' . mt_rand(1, 254);
    }
    
    public function batchTrainFromURLs(array $urls, int $maxPerSite = 10): array {
        $allResults = [];
        
        foreach ($urls as $url) {
            echo "🌐 Processing: $url\n";
            
            try {
                $content = $this->fetchURL($url);
                $results = $this->trainFromText($content);
                
                $allResults[$url] = [
                    'processed' => count($results),
                    'success' => count(array_filter($results, fn($r) => $r['success']))
                ];
                
                // Delay between sites
                sleep(mt_rand(2, 5));
                
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "\n";
            }
        }
        
        return $allResults;
    }
    
    private function fetchURL(string $url): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => $this->proxy_pool['user_agents'][array_rand($this->proxy_pool['user_agents'])],
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('CURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        return $content;
    }
}

// ================= USAGE =================
if (php_sapi_name() === 'cli') {
    $trainer = new WasmerAITrainer('https://https://generatelangguage.ct.ws/pure_ai_engine.php');
    
    // Training dari website
    $urls = [
        'https://id.wikipedia.org/wiki/Python',
        'https://id.wikipedia.org/wiki/JavaScript',
        'https://id.wikipedia.org/wiki/Pemrograman'
    ];
    
    $results = $trainer->batchTrainFromURLs($urls);
    print_r($results);
}
