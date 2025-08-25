<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Modal Troubleshooting Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #439454 0%, #2d5a37 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .section h2 {
            color: #439454;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .button {
            background-color: #439454;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: background-color 0.2s;
        }

        .button:hover {
            background-color: #2d5a37;
        }

        .button.secondary {
            background-color: #6b7280;
        }

        .button.secondary:hover {
            background-color: #4b5563;
        }

        .button.danger {
            background-color: #dc2626;
        }

        .button.danger:hover {
            background-color: #b91c1c;
        }

        .button:disabled {
            background-color: #d1d5db;
            cursor: not-allowed;
        }

        .result-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
        }

        .status-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-error {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-loading {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
        }

        .card h3 {
            color: #439454;
            margin-bottom: 0.5rem;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #439454;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .summary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .summary h3 {
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .recommendation {
            background: linear-gradient(135deg, #fef3c7 0%, #f59e0b 100%);
            color: #92400e;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .recommendation h4 {
            margin-bottom: 0.5rem;
        }

        .recommendation ul {
            margin-left: 1rem;
        }

        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>History Modal Troubleshooting</h1>
            <p>Systematic debugging untuk GAPURA ANGKASA SDM System</p>
        </div>

        <div class="section">
            <h2>🔍 Step 1: Database Level Verification</h2>
            <p>Test database connection, employee count, dan data 30 hari terakhir</p>
            <button class="button" onclick="runTest('step1-database')">
                <span class="loading" id="loading-step1" style="display: none;"></span>
                Run Database Test
            </button>
            <span id="status-step1" class="status-indicator" style="display: none;"></span>
            <div id="result-step1" class="result-box" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>🔗 Step 2: Model Relationship Verification</h2>
            <p>Test Employee model relationships dengan Unit dan SubUnit</p>
            <button class="button" onclick="runTest('step2-relationships')">
                <span class="loading" id="loading-step2" style="display: none;"></span>
                Run Relationship Test
            </button>
            <span id="status-step2" class="status-indicator" style="display: none;"></span>
            <div id="result-step2" class="result-box" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>🔌 Step 3: API Response Structure Verification</h2>
            <p>Test endpoint /api/dashboard/employee-history dan response structure</p>
            <button class="button" onclick="runTest('step3-api-response')">
                <span class="loading" id="loading-step3" style="display: none;"></span>
                Run API Test
            </button>
            <span id="status-step3" class="status-indicator" style="display: none;"></span>
            <div id="result-step3" class="result-box" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>🩺 Comprehensive Check</h2>
            <p>Run all troubleshooting steps dan generate recommendation</p>
            <button class="button" onclick="runTest('full-check')">
                <span class="loading" id="loading-full" style="display: none;"></span>
                Run Full Troubleshooting
            </button>
            <span id="status-full" class="status-indicator" style="display: none;"></span>
            <div id="result-full" class="result-box" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>🛠️ Quick Fixes</h2>
            <p>Tools untuk memperbaiki masalah yang ditemukan</p>
            
            <div class="grid">
                <div class="card">
                    <h3>Fix Timestamps</h3>
                    <p>Update created_at timestamp untuk testing</p>
                    <button class="button secondary" onclick="fixTimestamps()">
                        Fix Employee Timestamps
                    </button>
                </div>

                <div class="card">
                    <h3>Add Test Employee</h3>
                    <p>Tambahkan karyawan test untuk History Modal</p>
                    <select id="employee-type">
                        <option value="normal">Normal Employee</option>
                        <option value="egm">EGM Employee</option>
                        <option value="gm">GM Employee</option>
                    </select>
                    <button class="button secondary" onclick="addTestEmployee()">
                        Add Test Employee
                    </button>
                </div>

                <div class="card">
                    <h3>Test Organizational Structure</h3>
                    <p>Verify organizational structure building</p>
                    <button class="button secondary" onclick="testOrgStructure()">
                        Test Org Structure
                    </button>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>⚡ Quick Tests</h2>
            <p>Direct tests untuk specific issues</p>
            
            <div class="grid">
                <div class="card">
                    <h3>Raw SQL Test</h3>
                    <button class="button secondary" onclick="quickTest('raw-sql-30-days')">
                        Test Raw Query
                    </button>
                </div>

                <div class="card">
                    <h3>Timezone Test</h3>
                    <button class="button secondary" onclick="quickTest('timezone-test')">
                        Test Timezone
                    </button>
                </div>

                <div class="card">
                    <h3>Direct API Call</h3>
                    <button class="button secondary" onclick="quickTest('direct-history-call')">
                        Test Direct API
                    </button>
                </div>
            </div>

            <div id="quick-test-result" class="result-box" style="display: none;"></div>
        </div>

        <div class="section">
            <h2>📊 Summary & Recommendations</h2>
            <div id="summary-section" style="display: none;">
                <div class="summary" id="summary-content"></div>
                <div class="recommendation" id="recommendation-content"></div>
            </div>
        </div>
    </div>

    <script>
        let testResults = {};

        async function runTest(endpoint) {
            const resultDiv = document.getElementById(`result-${endpoint.replace('step3-api-response', 'step3').replace('step2-relationships', 'step2').replace('step1-database', 'step1').replace('full-check', 'full')}`);
            const statusSpan = document.getElementById(`status-${endpoint.replace('step3-api-response', 'step3').replace('step2-relationships', 'step2').replace('step1-database', 'step1').replace('full-check', 'full')}`);
            const loadingSpan = document.getElementById(`loading-${endpoint.replace('step3-api-response', 'step3').replace('step2-relationships', 'step2').replace('step1-database', 'step1').replace('full-check', 'full')}`);

            try {
                // Show loading
                loadingSpan.style.display = 'inline-block';
                statusSpan.style.display = 'inline-block';
                statusSpan.className = 'status-indicator status-loading';
                statusSpan.textContent = 'Running...';
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = 'Testing in progress...';

                const response = await fetch(`/api/troubleshoot/${endpoint}`);
                const data = await response.json();

                // Hide loading
                loadingSpan.style.display = 'none';

                // Update status
                if (data.success) {
                    statusSpan.className = 'status-indicator status-success';
                    statusSpan.textContent = 'Success';
                } else {
                    statusSpan.className = 'status-indicator status-error';
                    statusSpan.textContent = 'Failed';
                }

                // Show result
                resultDiv.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                
                // Store result for summary
                testResults[endpoint] = data;

                // Update summary if this is full check
                if (endpoint === 'full-check') {
                    updateSummary(data);
                }

            } catch (error) {
                loadingSpan.style.display = 'none';
                statusSpan.className = 'status-indicator status-error';
                statusSpan.textContent = 'Error';
                resultDiv.innerHTML = `<pre>Error: ${error.message}</pre>`;
            }
        }

        async function fixTimestamps() {
            try {
                const response = await fetch('/api/troubleshoot/fix-timestamps', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        count: 5,
                        days_ago: 1
                    })
                });

                const data = await response.json();
                alert(data.success ? 'Timestamps fixed successfully!' : `Error: ${data.error}`);
                
                if (data.success) {
                    console.log('Fixed employees:', data.updated_employees);
                }
                
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        }

        async function addTestEmployee() {
            const type = document.getElementById('employee-type').value;
            
            try {
                const response = await fetch('/api/troubleshoot/add-test-employee', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        unit_organisasi: 'Landside'
                    })
                });

                const data = await response.json();
                alert(data.success ? `Test employee added successfully! ID: ${data.employee_id}` : `Error: ${data.error}`);
                
                if (data.success) {
                    console.log('New employee:', data.employee_data);
                    console.log('Organizational structure:', data.organizational_structure);
                }
                
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        }

        async function testOrgStructure() {
            try {
                const response = await fetch('/api/troubleshoot/fix-organizational-structure', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        count: 5
                    })
                });

                const data = await response.json();
                alert(data.success ? `Org structure tested! Success: ${data.successful_count}/${data.total_processed}` : `Error: ${data.error}`);
                
                if (data.success) {
                    console.log('Org structure results:', data.results);
                }
                
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        }

        async function quickTest(endpoint) {
            const resultDiv = document.getElementById('quick-test-result');
            
            try {
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = 'Running quick test...';

                const response = await fetch(`/api/quick-test/${endpoint}`);
                const data = await response.json();

                resultDiv.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                
            } catch (error) {
                resultDiv.innerHTML = `<pre>Error: ${error.message}</pre>`;
            }
        }

        function updateSummary(data) {
            const summarySection = document.getElementById('summary-section');
            const summaryContent = document.getElementById('summary-content');
            const recommendationContent = document.getElementById('recommendation-content');

            summarySection.style.display = 'block';

            // Overall health
            const overallHealth = data.overall_health || {};
            const healthItems = Object.entries(overallHealth).map(([key, value]) => 
                `<li><strong>${key}:</strong> ${value ? '✅ Healthy' : '❌ Issues found'}</li>`
            ).join('');

            summaryContent.innerHTML = `
                <h3>Overall System Health</h3>
                <ul>${healthItems}</ul>
            `;

            // Recommendations
            const recommendation = data.recommendation || {};
            const issues = recommendation.issues_found || [];
            const actions = recommendation.recommended_actions || [];

            recommendationContent.innerHTML = `
                <h4>Status: ${recommendation.status || 'Unknown'}</h4>
                ${issues.length > 0 ? `
                    <h4>Issues Found:</h4>
                    <ul>${issues.map(issue => `<li>${issue}</li>`).join('')}</ul>
                ` : ''}
                ${actions.length > 0 ? `
                    <h4>Recommended Actions:</h4>
                    <ul>${actions.map(action => `<li>${action}</li>`).join('')}</ul>
                ` : ''}
            `;
        }

        // Auto-run basic checks on page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                console.log('Auto-running database verification...');
                runTest('step1-database');
            }, 1000);
        });
    </script>
</body>
</html>