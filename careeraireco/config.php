<?php
// Database & Hybrid Storage Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'careerai_db');
define('DATA_DIR', __DIR__ . '/data');
define('DATA_FILE', DATA_DIR . '/db.json');

// Ensure data directory exists
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0777, true);
}

// Initialize file storage if not exists
function init_file_storage() {
    if (!file_exists(DATA_FILE)) {
        $default_data = [
            'users' => [
                'student@careerai.com' => [
                    'id' => 1,
                    'name' => 'Alex Rivera',
                    'email' => 'student@careerai.com',
                    'password' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'student',
                    'education' => 'Bachelor',
                    'specialization' => 'Computer Science',
                    'cgpa' => 88,
                    'skills_score' => 85,
                    'interest_profile' => 'Investigative & Enterprising',
                    'top_career' => 'AI Software Engineer',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'admin@careerai.com' => [
                    'id' => 2,
                    'name' => 'Dr. Sarah Jenkins',
                    'email' => 'admin@careerai.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'sam@careerai.com' => [
                    'id' => 3,
                    'name' => 'Sam Patel',
                    'email' => 'sam@careerai.com',
                    'password' => password_hash('demo123', PASSWORD_DEFAULT),
                    'role' => 'student',
                    'education' => 'Master',
                    'specialization' => 'Data Science',
                    'cgpa' => 92,
                    'skills_score' => 91,
                    'interest_profile' => 'Investigative & Realistic',
                    'top_career' => 'Lead Data Scientist',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ],
            'profiles' => [],
            'assessments' => [],
            'surveys' => [],
            'recommendations_log' => [
                [
                    'student_name' => 'Alex Rivera',
                    'email' => 'student@careerai.com',
                    'top_career' => 'AI Software Engineer',
                    'score' => 94,
                    'cgpa' => 88,
                    'date' => date('Y-m-d')
                ],
                [
                    'student_name' => 'Sam Patel',
                    'email' => 'sam@careerai.com',
                    'top_career' => 'Lead Data Scientist',
                    'score' => 91,
                    'cgpa' => 92,
                    'date' => date('Y-m-d', strtotime('-1 day'))
                ]
            ]
        ];
        @file_put_contents(DATA_FILE, json_encode($default_data, JSON_PRETTY_PRINT));
    }
}
init_file_storage();

$pdo = null;
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_TIMEOUT => 2,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch(Exception $e) {
    // Graceful fallback to local JSON file storage if MySQL is offline or unconfigured
    $pdo = null;
}

// Helper functions for reading/writing storage
function get_storage_data() {
    init_file_storage();
    $content = @file_get_contents(DATA_FILE);
    return $content ? json_decode($content, true) : ['users' => [], 'profiles' => [], 'assessments' => [], 'surveys' => [], 'recommendations_log' => []];
}

function save_storage_data($data) {
    @file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function get_user_by_email($email) {
    global $pdo;
    $email = strtolower(trim($email));
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(Exception $e) { /* fallback */ }
    }
    $data = get_storage_data();
    return $data['users'][$email] ?? null;
}

function save_user_account($user_data) {
    global $pdo;
    $email = strtolower(trim($user_data['email']));
    $data = get_storage_data();
    
    if (!isset($user_data['id'])) {
        $user_data['id'] = count($data['users']) + 10;
    }
    $data['users'][$email] = $user_data;
    save_storage_data($data);

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
            $stmt->execute([$user_data['name'], $email, $user_data['password'], $user_data['role']]);
        } catch(Exception $e) { /* ignore */ }
    }
    return $user_data;
}

function get_all_users_list() {
    $data = get_storage_data();
    return array_values($data['users']);
}

function log_recommendation($student_name, $email, $top_career, $score, $cgpa) {
    $data = get_storage_data();
    array_unshift($data['recommendations_log'], [
        'student_name' => $student_name,
        'email' => $email,
        'top_career' => $top_career,
        'score' => $score,
        'cgpa' => $cgpa,
        'date' => date('Y-m-d H:i')
    ]);
    if (count($data['recommendations_log']) > 50) {
        $data['recommendations_log'] = array_slice($data['recommendations_log'], 0, 50);
    }
    save_storage_data($data);
}

function get_system_stats() {
    $data = get_storage_data();
    $students = array_filter($data['users'], function($u) { return ($u['role'] ?? '') === 'student'; });
    return [
        'total_students' => count($students) + 1240, // Base stats + dynamic
        'total_recs' => count($data['recommendations_log'] ?? []) + 4580,
        'popular_career' => 'AI & Software Engineering'
    ];
}

function delete_user_account($email) {
    global $pdo;
    $email = strtolower(trim($email));
    $data = get_storage_data();
    if (isset($data['users'][$email])) {
        unset($data['users'][$email]);
        save_storage_data($data);
    }
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
            $stmt->execute([$email]);
        } catch(Exception $e) { /* ignore */ }
    }
    return true;
}

function get_all_careers() {
    $data = get_storage_data();
    if (empty($data['careers']) || !is_array($data['careers'])) {
        $default_careers = [
            'ai_eng' => [
                'id' => 'ai_eng',
                'title' => 'AI & Machine Learning Engineer',
                'category' => 'Artificial Intelligence',
                'description' => 'Designs, builds, and deploys scalable machine learning models, deep neural networks, and generative LLM pipelines.',
                'benchmarks' => 'Min Technical Score: 85%, CGPA: 85+, Key Skills: PyTorch, Python, Transformers, Vector DBs',
                'salary' => '$135,000 / yr',
                'growth' => '+34% YoY'
            ],
            'data_sci' => [
                'id' => 'data_sci',
                'title' => 'Data Scientist & Big Data Architect',
                'category' => 'Data Science & Analytics',
                'description' => 'Extracts actionable business insights from petabyte-scale datasets using predictive modeling and statistical inference.',
                'benchmarks' => 'Min Technical Score: 80%, CGPA: 82+, Key Skills: Python, SQL, Spark, Statistical Modeling',
                'salary' => '$125,000 / yr',
                'growth' => '+28% YoY'
            ],
            'cloud_arch' => [
                'id' => 'cloud_arch',
                'title' => 'Cloud & DevOps Solutions Architect',
                'category' => 'Cloud Computing & Infrastructure',
                'description' => 'Architects resilient, secure, and highly scalable multi-cloud infrastructure and automated CI/CD deployment pipelines.',
                'benchmarks' => 'Min Technical Score: 78%, CGPA: 80+, Key Skills: AWS/Azure, Kubernetes, Terraform, Docker',
                'salary' => '$130,000 / yr',
                'growth' => '+31% YoY'
            ],
            'cyber_sec' => [
                'id' => 'cyber_sec',
                'title' => 'Cybersecurity Threat Analyst & Engineer',
                'category' => 'Cybersecurity',
                'description' => 'Monitors, defends, and conducts penetration testing on enterprise network infrastructure and cloud native architectures.',
                'benchmarks' => 'Min Technical Score: 75%, CGPA: 78+, Key Skills: Ethical Hacking, SIEM, Cryptography, Zero Trust',
                'salary' => '$120,000 / yr',
                'growth' => '+29% YoY'
            ],
            'fintech_quant' => [
                'id' => 'fintech_quant',
                'title' => 'Quantitative FinTech Developer',
                'category' => 'FinTech & Blockchain',
                'description' => 'Develops high-frequency trading algorithms, smart contracts, and decentralized financial protocols.',
                'benchmarks' => 'Min Technical Score: 88%, CGPA: 88+, Key Skills: C++, Rust, Solidity, Quantitative Mathematics',
                'salary' => '$145,000 / yr',
                'growth' => '+25% YoY'
            ]
        ];
        $data['careers'] = $default_careers;
        save_storage_data($data);
    }
    return $data['careers'];
}

function save_career_role($career_data) {
    $data = get_storage_data();
    if (empty($data['careers']) || !is_array($data['careers'])) {
        get_all_careers();
        $data = get_storage_data();
    }
    $id = trim($career_data['id'] ?? '');
    if (empty($id)) {
        $id = 'career_' . uniqid();
        $career_data['id'] = $id;
    }
    $data['careers'][$id] = $career_data;
    save_storage_data($data);
    return $career_data;
}

function delete_career_role($career_id) {
    $data = get_storage_data();
    if (isset($data['careers'][$career_id])) {
        unset($data['careers'][$career_id]);
        save_storage_data($data);
    }
    return true;
}
?>