<?php
/**
 * TWITCH CODE REWARD SYSTEM - Database Class
 * 
 * Supabase REST API Wrapper
 * Handles all database operations via Supabase REST API
 */

class Database {
    private $url;
    private $apiKey;
    private $serviceKey;
    
    public function __construct($useServiceKey = false) {
        $this->url = SUPABASE_URL;
        $this->apiKey = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
        
        if ($useServiceKey) {
            $this->apiKey = $this->serviceKey;
        }
    }
    
    /**
     * Generic request method
     */
    private function request($method, $endpoint, $data = null, $headers = []) {
        $url = $this->url . '/rest/v1/' . $endpoint;
        
        $defaultHeaders = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null && in_array($method, ['POST', 'PATCH', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            if (DEBUG_MODE) {
                error_log("CURL Error: $error");
            }
            return ['success' => false, 'error' => 'Database connection error'];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $result];
        } else {
            if (DEBUG_MODE) {
                error_log("Database Error (HTTP $httpCode): " . json_encode($result));
            }
            return ['success' => false, 'error' => $result['message'] ?? 'Database error', 'code' => $httpCode];
        }
    }
    
    /**
     * Direct query with custom endpoint
     * For complex queries with joins, filters, etc.
     * 
     * Example: $db->query("codes?select=*,users!codes_streamer_id_fkey(twitch_username)&is_active=eq.true")
     */
    public function query($endpoint) {
        return $this->request('GET', $endpoint);
    }
    
    /**
     * SELECT query
     */
    public function select($table, $columns = '*', $conditions = [], $orderBy = null, $limit = null) {
        $endpoint = $table . '?select=' . $columns;
        
        // Add conditions
        if (is_string($conditions) && !empty($conditions)) {
            // If conditions is a string, append directly
            $endpoint .= '&' . $conditions;
        } elseif (is_array($conditions)) {
            // If conditions is an array, process it
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    // Handle operators like ['gte', 100]
                    $operator = $value[0];
                    $val = $value[1];
                    $endpoint .= "&$key=$operator.$val";
                } else {
                    $endpoint .= "&$key=eq.$value";
                }
            }
        }
        
        // Add order by
        if ($orderBy) {
            $endpoint .= "&order=$orderBy";
        }
        
        // Add limit
        if ($limit) {
            $endpoint .= "&limit=$limit";
        }
        
        return $this->request('GET', $endpoint);
    }
    
    /**
     * INSERT query
     */
    public function insert($table, $data) {
        return $this->request('POST', $table, $data);
    }
    
    /**
     * UPDATE query
     */
    public function update($table, $data, $conditions = []) {
        $endpoint = $table;
        
        // Add conditions
        if (is_string($conditions) && !empty($conditions)) {
            // If conditions is a string, append directly
            $endpoint .= '?' . $conditions;
        } elseif (is_array($conditions)) {
            // If conditions is an array, process it
            $first = true;
            foreach ($conditions as $key => $value) {
                $prefix = $first ? '?' : '&';
                $endpoint .= "$prefix$key=eq.$value";
                $first = false;
            }
        }
        
        return $this->request('PATCH', $endpoint, $data);
    }
    
    /**
     * DELETE query
     */
    public function delete($table, $conditions = []) {
        $endpoint = $table;
        
        // Add conditions
        if (is_string($conditions) && !empty($conditions)) {
            // If conditions is a string, append directly
            $endpoint .= '?' . $conditions;
        } elseif (is_array($conditions)) {
            // If conditions is an array, process it
            $first = true;
            foreach ($conditions as $key => $value) {
                $prefix = $first ? '?' : '&';
                $endpoint .= "$prefix$key=eq.$value";
                $first = false;
            }
        }
        
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Count rows
     */
    public function count($table, $conditions = []) {
        $endpoint = $table . '?select=*';
        
        // Add conditions
        if (is_string($conditions) && !empty($conditions)) {
            // If conditions is a string, append directly
            $endpoint .= '&' . $conditions;
        } elseif (is_array($conditions)) {
            // If conditions is an array, process it
            foreach ($conditions as $key => $value) {
                $endpoint .= "&$key=eq.$value";
            }
        }
        
        $headers = ['Prefer: count=exact'];
        $result = $this->request('GET', $endpoint, null, $headers);
        
        // Supabase returns count in Content-Range header, but we can also count the array
        if ($result['success']) {
            return ['success' => true, 'count' => count($result['data'])];
        }
        return $result;
    }
    
    /**
     * Execute RPC (stored procedure)
     */
    public function rpc($functionName, $params = []) {
        $endpoint = 'rpc/' . $functionName;
        return $this->request('POST', $endpoint, $params);
    }
    
    /**
     * Get single row
     */
    public function selectOne($table, $columns = '*', $conditions = []) {
        $result = $this->select($table, $columns, $conditions, null, 1);
        
        if ($result['success'] && !empty($result['data'])) {
            return ['success' => true, 'data' => $result['data'][0]];
        } elseif ($result['success'] && empty($result['data'])) {
            return ['success' => false, 'error' => 'No data found'];
        }
        
        return $result;
    }
    
    /**
     * Check if record exists
     */
    public function exists($table, $conditions = []) {
        $result = $this->count($table, $conditions);
        return $result['success'] && $result['count'] > 0;
    }
    
    /**
     * Get user by Twitch ID
     */
    public function getUserByTwitchId($twitchId) {
        return $this->selectOne('users', '*', ['twitch_user_id' => $twitchId]);
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        return $this->selectOne('users', '*', ['id' => $userId]);
    }
    
    /**
     * Get user by overlay token
     */
    public function getUserByToken($token) {
        return $this->selectOne('users', '*', ['overlay_token' => $token]);
    }
    
    /**
     * Get active code for streamer
     */
    public function getActiveCode($streamerId) {
        // Get active codes for this streamer, ordered by creation time
        $result = $this->select(
            'codes', 
            '*', 
            "streamer_id=eq.$streamerId&is_active=eq.true&order=created_at.desc&limit=1"
        );
        
        if (!$result['success'] || empty($result['data'])) {
            return ['success' => false, 'message' => 'No active code'];
        }
        
        $code = $result['data'][0];
        
        // Check if code is still within countdown + duration window
        $createdAt = strtotime($code['created_at']);
        $now = time();
        $elapsed = $now - $createdAt;
        $totalDuration = intval($code['countdown_duration']) + intval($code['duration']);
        
        if ($elapsed < $totalDuration) {
            return ['success' => true, 'data' => $code];
        }
        
        return ['success' => false, 'message' => 'Code expired'];
    }
    
    /**
     * Get user balance
     */
    public function getUserBalance($userId) {
        // Sum of rewards from submissions
        $submissions = $this->select('submissions', 'reward_amount', ['user_id' => $userId]);
        $totalEarned = 0;
        if ($submissions['success']) {
            foreach ($submissions['data'] as $sub) {
                $totalEarned += floatval($sub['reward_amount']);
            }
        }
        
        // Sum of completed payouts
        $payouts = $this->select('payout_requests', 'amount', [
            'user_id' => $userId,
            'status' => 'completed'
        ]);
        $totalPaid = 0;
        if ($payouts['success']) {
            foreach ($payouts['data'] as $payout) {
                $totalPaid += floatval($payout['amount']);
            }
        }
        
        return $totalEarned - $totalPaid;
    }
    
    /**
     * Get setting value
     */
    public function getSetting($key, $default = null) {
        $result = $this->selectOne('settings', 'value', ['key' => $key]);
        
        if ($result['success']) {
            return $result['data']['value'];
        }
        
        return $default;
    }
    
    /**
     * Update setting
     */
    public function updateSetting($key, $value) {
        $exists = $this->exists('settings', ['key' => $key]);
        
        if ($exists) {
            return $this->update('settings', ['value' => $value], ['key' => $key]);
        } else {
            return $this->insert('settings', ['key' => $key, 'value' => $value]);
        }
    }
}

