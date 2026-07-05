import sys
import os
import json
from flask import Flask, request, jsonify

app = Flask(__name__)

def load_career_database():
    db_path = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'data', 'db.json'))
    if os.path.exists(db_path):
        try:
            with open(db_path, 'r', encoding='utf-8') as f:
                data = json.load(f)
                careers_dict = data.get('careers', {})
                if careers_dict and isinstance(careers_dict, dict):
                    return list(careers_dict.values())
        except Exception as e:
            pass
            
    # Default fallback dynamic roles if db.json is inaccessible
    return [
        {
            'id': 'ai_eng',
            'title': 'AI & Machine Learning Engineer',
            'category': 'Artificial Intelligence',
            'description': 'Designs, builds, and deploys scalable machine learning models, deep neural networks, and generative LLM pipelines.',
            'benchmarks': 'Min Technical Score: 85%, CGPA: 85+, Key Skills: PyTorch, Python, Transformers, Vector DBs',
            'salary': '$135,000 / yr',
            'growth': '+34% YoY'
        },
        {
            'id': 'data_sci',
            'title': 'Data Scientist & Big Data Architect',
            'category': 'Data Science & Analytics',
            'description': 'Extracts actionable business insights from petabyte-scale datasets using predictive modeling and statistical inference.',
            'benchmarks': 'Min Technical Score: 80%, CGPA: 82+, Key Skills: Python, SQL, Spark, Statistical Modeling',
            'salary': '$125,000 / yr',
            'growth': '+28% YoY'
        },
        {
            'id': 'cloud_arch',
            'title': 'Cloud & DevOps Solutions Architect',
            'category': 'Cloud Computing & Infrastructure',
            'description': 'Architects resilient, secure, and highly scalable multi-cloud infrastructure and automated CI/CD deployment pipelines.',
            'benchmarks': 'Min Technical Score: 78%, CGPA: 80+, Key Skills: AWS/Azure, Kubernetes, Terraform, Docker',
            'salary': '$130,000 / yr',
            'growth': '+31% YoY'
        },
        {
            'id': 'cyber_sec',
            'title': 'Cybersecurity Threat Analyst & Engineer',
            'category': 'Cybersecurity',
            'description': 'Monitors, defends, and conducts penetration testing on enterprise network infrastructure and cloud native architectures.',
            'benchmarks': 'Min Technical Score: 75%, CGPA: 78+, Key Skills: Ethical Hacking, SIEM, Cryptography, Zero Trust',
            'salary': '$120,000 / yr',
            'growth': '+29% YoY'
        },
        {
            'id': 'fintech_quant',
            'title': 'Quantitative FinTech Developer',
            'category': 'FinTech & Blockchain',
            'description': 'Develops high-frequency trading algorithms, smart contracts, and decentralized financial protocols.',
            'benchmarks': 'Min Technical Score: 88%, CGPA: 88+, Key Skills: C++, Rust, Solidity, Quantitative Mathematics',
            'salary': '$145,000 / yr',
            'growth': '+25% YoY'
        }
    ]

def classify_career(data):
    spec = str(data.get('specialization', '')).strip().lower()
    skills = str(data.get('skills', '')).strip().lower()
    edu = str(data.get('education_level', '')).strip()
    interests = str(data.get('interests', '')).strip().lower()
    cgpa = float(data.get('cgpa', 82))
    skills_score = float(data.get('skills_score', 80))

    careers_list = load_career_database()
    scored_careers = []

    for career in careers_list:
        title = str(career.get('title', '')).lower()
        category = str(career.get('category', '')).lower()
        desc = str(career.get('description', '')).lower()
        benchmarks = str(career.get('benchmarks', ''))

        base_score = 62.0

        # Education weighting
        if edu in ['Master', 'PhD']:
            base_score += 6.0
        elif edu in ['Bachelor']:
            base_score += 4.0

        # Academic & Skill Assessment weighting
        base_score += min(14.0, (cgpa - 65) * 0.35)
        base_score += min(12.0, (skills_score - 60) * 0.25)

        # Keyword matching against Interest Survey Vector
        interest_tokens = [w.strip() for w in interests.replace('&', ' ').split() if len(w.strip()) > 2]
        interest_match_count = 0
        for token in interest_tokens:
            if token in title or token in category or token in desc:
                interest_match_count += 1
            # Domain heuristics mapping RIASEC interests to categories
            if token in ['investigative', 'analytical'] and any(k in category or k in title for k in ['ai', 'data', 'artificial', 'intelligence', 'quant', 'science']):
                interest_match_count += 2
            elif token in ['realistic', 'systems'] and any(k in category or k in title for k in ['cloud', 'devops', 'cyber', 'security', 'infrastructure', 'network']):
                interest_match_count += 2
            elif token in ['artistic', 'creative'] and any(k in category or k in title for k in ['design', 'ux', 'ui', 'creative', 'frontend']):
                interest_match_count += 2
            elif token in ['enterprising', 'leadership'] and any(k in category or k in title for k in ['product', 'management', 'lead', 'architect', 'business', 'fintech']):
                interest_match_count += 2
            elif token in ['conventional', 'structured'] and any(k in category or k in title for k in ['fintech', 'finance', 'quant', 'security', 'analyst']):
                interest_match_count += 2

        base_score += min(24.0, interest_match_count * 6.5)

        # Keyword matching against Specialization & Skills
        if any(w in title or w in category for w in spec.split()):
            base_score += 8.0
        if any(w in desc or w in benchmarks.lower() for w in skills.split() if len(w) > 3):
            base_score += 7.0

        final_score = min(98, max(68, int(base_score)))

        # Extract required skills from benchmarks string
        req_skills = ['System Architecture', 'Problem Solving', 'Domain Expertise']
        if 'Key Skills:' in benchmarks:
            parts = benchmarks.split('Key Skills:')[-1].split(',')
            req_skills = [p.strip() for p in parts if p.strip()]

        scored_careers.append({
            'id': str(career.get('id', '')),
            'title': str(career.get('title', 'Technology Specialist')),
            'score': final_score,
            'description': str(career.get('description', '')),
            'salary_range': str(career.get('salary', '$110,000 - $160,000 / yr')),
            'growth_rate': str(career.get('growth', '+25% YoY')),
            'industries': [str(career.get('category', 'Technology Domain'))],
            'required_skills': req_skills[:4],
            'missing_skills': [] if final_score >= 90 else ['Advanced Certification in ' + req_skills[0] if req_skills else 'Advanced Domain Mastery']
        })

    scored_careers.sort(key=lambda x: x['score'], reverse=True)
    return scored_careers

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        if not data:
            return jsonify({'error': 'No data provided'}), 400
        recs = classify_career(data)
        return jsonify({
            'success': True,
            'recommendations': recs
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    if len(sys.argv) > 2 and sys.argv[1] == '--cli':
        try:
            input_json = json.loads(sys.argv[2])
            results = classify_career(input_json)
            print(json.dumps({'success': True, 'recommendations': results}))
        except Exception as e:
            print(json.dumps({'success': False, 'error': str(e)}))
    else:
        app.run(host='0.0.0.0', port=5000, debug=True)
