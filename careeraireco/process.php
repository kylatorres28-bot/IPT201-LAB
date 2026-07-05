<?php
require_once 'config.php';

function callMLAPI($data) {
    // 1. Try HTTP API (Flask)
    $url = 'http://localhost:5000/predict';
    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 2
        ]
    ];
    $context = @stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== FALSE) {
        $response = json_decode($result, true);
        if (!empty($response['recommendations'])) {
            return $response['recommendations'];
        }
    }
    
    // 2. Try CLI execution of Python script
    $py_script = __DIR__ . '/ml_api/app.py';
    $cmd = 'python ' . escapeshellarg($py_script) . ' --cli ' . escapeshellarg(json_encode($data)) . ' 2>&1';
    $cli_out = @shell_exec($cmd);
    if ($cli_out) {
        $res = json_decode(trim($cli_out), true);
        if (!empty($res['recommendations']) && is_array($res['recommendations'])) {
            return $res['recommendations'];
        }
    }

    // 3. Dynamic PHP Classification Engine matching against dynamic career database exactly
    return getDynamicAIRecommendations($data);
}

function getDynamicAIRecommendations($data) {
    $careers = get_all_careers();
    $cgpa = floatval($data['cgpa'] ?? 82);
    $skills_score = floatval($data['skills_score'] ?? 80);
    $spec = strtolower(trim($data['specialization'] ?? ''));
    $skills = strtolower(trim($data['skills'] ?? ''));
    $interests = strtolower(trim($data['interests'] ?? ''));
    $edu = strtolower(trim($data['education_level'] ?? ''));

    $scored_careers = [];

    foreach ($careers as $id => $career) {
        $title = strtolower(trim($career['title'] ?? ''));
        $category = strtolower(trim($career['category'] ?? ''));
        $desc = strtolower(trim($career['description'] ?? ''));
        $benchmarks = $career['benchmarks'] ?? '';

        $base_score = 62.0;

        // Academic degree boost
        if ($edu === 'master' || $edu === 'phd') {
            $base_score += 6.0;
        } elseif ($edu === 'bachelor') {
            $base_score += 4.0;
        }

        // Academic CGPA & Technical Assessment score contributions
        $base_score += min(14.0, ($cgpa - 65) * 0.35);
        $base_score += min(12.0, ($skills_score - 60) * 0.25);

        // Keyword matching against Student Interest Survey Vector
        $interest_tokens = preg_split('/[\s&]+/', $interests, -1, PREG_SPLIT_NO_EMPTY);
        $interest_match_count = 0;
        foreach ($interest_tokens as $token) {
            if (strlen($token) < 3) continue;
            if (str_contains($title, $token) || str_contains($category, $token) || str_contains($desc, $token)) {
                $interest_match_count += 1;
            }
            // Domain mapping heuristics from RIASEC survey
            if (in_array($token, ['investigative', 'analytical']) && (str_contains($category, 'ai') || str_contains($title, 'data') || str_contains($title, 'machine') || str_contains($category, 'science'))) {
                $interest_match_count += 2;
            } elseif (in_array($token, ['realistic', 'systems']) && (str_contains($category, 'cloud') || str_contains($title, 'devops') || str_contains($category, 'cyber') || str_contains($title, 'architect'))) {
                $interest_match_count += 2;
            } elseif (in_array($token, ['artistic', 'creative']) && (str_contains($category, 'design') || str_contains($title, 'ux') || str_contains($title, 'ui') || str_contains($desc, 'interface'))) {
                $interest_match_count += 2;
            } elseif (in_array($token, ['enterprising', 'leadership']) && (str_contains($category, 'fintech') || str_contains($title, 'product') || str_contains($title, 'lead') || str_contains($desc, 'strategic'))) {
                $interest_match_count += 2;
            } elseif (in_array($token, ['conventional', 'structured']) && (str_contains($category, 'fintech') || str_contains($title, 'quant') || str_contains($title, 'analyst') || str_contains($category, 'finance'))) {
                $interest_match_count += 2;
            }
        }
        $base_score += min(24.0, $interest_match_count * 6.5);

        // Keyword matching against Specialization & Skills
        if (!empty($spec) && (str_contains($title, $spec) || str_contains($category, $spec))) {
            $base_score += 8.0;
        }

        $final_score = min(98, max(68, intval($base_score)));

        // Parse required skills from benchmark text
        $req_skills = ['System Architecture', 'Problem Solving', 'Domain Mastery'];
        if (str_contains($benchmarks, 'Key Skills:')) {
            $parts = explode('Key Skills:', $benchmarks);
            if (isset($parts[1])) {
                $sk_list = explode(',', $parts[1]);
                $req_skills = array_map('trim', array_filter($sk_list));
            }
        }

        $scored_careers[] = [
            'id' => $id,
            'title' => $career['title'] ?? 'Technology Specialist',
            'score' => $final_score,
            'description' => $career['description'] ?? '',
            'salary_range' => $career['salary'] ?? '$110,000 - $160,000 / yr',
            'growth_rate' => $career['growth'] ?? '+25% YoY',
            'industries' => [$career['category'] ?? 'Technology Domain'],
            'required_skills' => array_slice($req_skills, 0, 4),
            'missing_skills' => $final_score >= 90 ? [] : ['Advanced Certification in ' . ($req_skills[0] ?? 'Domain Mastery')]
        ];
    }

    usort($scored_careers, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $scored_careers;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    if (isset($_SESSION['user']) && empty($_POST)) {
        $u = $_SESSION['user'];
        $data = [
            'education_level' => $u['education'] ?? 'Bachelor',
            'specialization' => $u['specialization'] ?? 'Computer Science',
            'skills' => ($u['specialization'] ?? '') . ', Programming, Data Analysis',
            'cgpa' => $u['cgpa'] ?? 85,
            'skills_score' => $u['skills_score'] ?? 80,
            'interests' => $u['interest_profile'] ?? 'Investigative'
        ];
    } else {
        $data = [
            'education_level' => $_POST['education_level'] ?? 'Bachelor',
            'specialization' => $_POST['specialization'] ?? 'Computer Science',
            'skills' => $_POST['skills'] ?? 'Programming',
            'certifications' => $_POST['certifications'] ?? 'None',
            'cgpa' => floatval($_POST['cgpa'] ?? 85),
            'skills_score' => floatval($_SESSION['user']['skills_score'] ?? 80),
            'interests' => $_SESSION['user']['interest_profile'] ?? 'Investigative'
        ];
    }

    $recommendations = callMLAPI($data);

    $_SESSION['recommendations'] = $recommendations;
    $_SESSION['user_data'] = $data;

    // Log this recommendation & update current user top career if logged in
    $top = $recommendations[0] ?? null;
    if ($top) {
        $student_name = $_SESSION['user']['name'] ?? 'Guest Student';
        $email = $_SESSION['user']['email'] ?? 'guest@careerai.com';
        log_recommendation($student_name, $email, $top['title'], $top['score'], $data['cgpa']);

        if (isset($_SESSION['user'])) {
            $_SESSION['user']['top_career'] = $top['title'];
            $_SESSION['user'] = save_user_account($_SESSION['user']);
        }
    }

    header('Location: recommendations.php');
    exit;
} else {
    header('Location: index.php');
    exit;
}
?>