<?php
session_start();

// Password admin
$ADMIN_PASSWORD = "@pkp1309";

// Check logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Check login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['last_data_hash'] = ''; // Untuk cache invalidation
        header("Location: admin.php");
        exit();
    } else {
        $error = "Password salah!";
    }
}

// If not logged in, show login form
if (!isset($_SESSION['admin_logged_in'])) {
    // SIMPLE LOGIN PAGE - Minimal HTML untuk loading cepat
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Ujian SKD</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { 
                background: linear-gradient(135deg, #2f4298ff 0%, #d7d0ddff 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .login-box {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                width: 90%;
                max-width: 400px;
                text-align: center;
            }
            .logo { margin-bottom: 30px; }
            .logo i { font-size: 48px; color: #26336aff; margin-bottom: 15px; }
            .logo h1 { color: #333; font-size: 24px; margin-bottom: 5px; }
            .logo p { color: #666; font-size: 14px; }
            input {
                width: 100%;
                padding: 12px 15px;
                margin: 10px 0;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            input:focus {
                border-color: #667eea;
                outline: none;
            }
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #2f4298ff 0%, #d7d0ddff 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                margin-top: 10px;
                transition: transform 0.2s;
            }
            button:hover { transform: translateY(-2px); }
            button:active { transform: translateY(0); }
            .error {
                background: #ffeaea;
                color: #d32f2f;
                padding: 12px;
                border-radius: 8px;
                margin: 15px 0;
                border: 1px solid #ffcdd2;
            }
            .loading { 
                display: none;
                color: #666;
                margin-top: 15px;
                font-size: 14px;
            }
            .loading i { animation: spin 1s linear infinite; }
            @keyframes spin { 
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-lock"></i>
                <h1>Admin Panel</h1>
                <p>Ujian SKD Magang Nasional</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm" onsubmit="showLoginLoading()">
                <input type="password" name="password" placeholder="Masukkan password" required>
                <button type="submit" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <div class="loading" id="loginLoading">
                    <i class="fas fa-spinner"></i> Memproses...
                </div>
            </form>
            
            <script>
                function showLoginLoading() {
                    document.getElementById('loginBtn').style.display = 'none';
                    document.getElementById('loginLoading').style.display = 'block';
                }
            </script>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Session timeout (8 hours)
$session_timeout = 8 * 60 * 60;
if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time'] > $session_timeout)) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Preload data jika belum ada di session
if (!isset($_SESSION['cached_data'])) {
    $_SESSION['cached_data'] = [];
    $_SESSION['cached_stats'] = [];
    $_SESSION['cache_time'] = time();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Ujian SKD</title>
    
    <!-- Inline minimal CSS untuk loading cepat -->
    <style id="main-css">
        /* CRITICAL CSS - Load immediately */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            opacity: 0;
            transition: opacity 0.3s;
        }
        body.loaded { opacity: 1; }
        
        /* Header */
        .admin-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        /* Loading overlay */
        #initial-loading {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.3s;
        }
        .spinner-large {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        /* Tabs basic */
        .content-section { display: none; }
        .content-section.active { display: block; }
        
        @keyframes spin { 
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <!-- Defer non-critical CSS -->
    <style id="deferred-css">
        /* Deferred CSS - Load after page is ready */
        .header-left h1 { color: #333; font-size: 24px; }
        .header-left p { color: #666; font-size: 14px; }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .user-info { display: flex; align-items: center; gap: 10px; color: #666; }
        .user-info i { color: #667eea; }
        .btn-logout {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
            text-decoration: none;
        }
        .btn-logout:hover { background: #ff5252; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .tab {
            padding: 12px 25px;
            background: #ddd;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .tab.active {
            background: #667eea;
            color: white;
        }
        
        /* Content */
        .content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #667eea;
            color: white;
            position: sticky;
            top: 0;
        }
        tr:hover { background: #f9f9f9; }
        
        /* Buttons */
        .btn {
            padding: 6px 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }
        .btn:hover { opacity: 0.9; }
        .btn-danger { background: #ff6b6b; }
        .btn-success { background: #4CAF50; }
        .btn-warning { background: #ff9800; }
        
        /* Filters */
        .filters {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .filters input, .filters select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 150px;
        }
        
        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        /* Session Info */
        .session-info {
            background: #e8f4ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 13px;
        }
        
        /* Loading states */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 4px;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 0 10px; }
            .admin-header { flex-direction: column; gap: 10px; text-align: center; }
            .header-right { justify-content: center; }
            .filters { flex-direction: column; }
            .filters input, .filters select { width: 100%; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
            .stat-card { padding: 12px; }
        }
    </style>
    
    <!-- Font Awesome deferred -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
</head>
<body>
    <!-- Initial loading screen -->
    <div id="initial-loading">
        <div class="spinner-large"></div>
        <h3 style="color: #333; margin-bottom: 10px;">Memuat Admin Panel...</h3>
        <p style="color: #666; font-size: 14px;">Menyiapkan dashboard</p>
    </div>

    <!-- Main content (hidden initially) -->
    <div id="main-content" style="display: none;">
        <!-- Header -->
        <div class="admin-header">
            <div class="header-left">
                <h1><i class="fas fa-cogs"></i> Admin Panel - Ujian SKD</h1>
                <p>Manajemen Hasil Ujian Tes SKD Magang Nasional Batch III BPVP Pangkep</p>
            </div>
            <div class="header-right">
                <div class="user-info">
                    <i class="fas fa-user-shield"></i>
                    <span>Administrator</span>
                </div>
                <a href="admin.php?logout=true" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="container">
            <!-- Session Info -->
            <div class="session-info">
                <div>
                    <i class="fas fa-clock"></i>
                    <span class="session-time">
                        Login: <?php echo date('d/m/Y H:i:s', $_SESSION['admin_login_time']); ?>
                    </span>
                </div>
                <div>
                    <i class="fas fa-shield-alt"></i>
                    <span>Session akan berakhir dalam 8 jam</span>
                </div>
                <div>
                    <i class="fas fa-database"></i>
                    <span id="data-count">Memuat data...</span>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('dashboard')" id="tab-dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </button>
                <button class="tab" onclick="showTab('hasil')" id="tab-hasil">
                    <i class="fas fa-list-alt"></i> Hasil Ujian
                </button>
                <button class="tab" onclick="showTab('export')" id="tab-export">
                    <i class="fas fa-download"></i> Export Data
                </button>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Dashboard -->
                <div id="dashboard" class="content-section active">
                    <h2><i class="fas fa-chart-line"></i> Dashboard</h2>
                    <div class="stats" id="stats">
                        <!-- Skeleton loading -->
                        <div class="stat-card skeleton" style="height: 80px;"></div>
                        <div class="stat-card skeleton" style="height: 80px;"></div>
                        <div class="stat-card skeleton" style="height: 80px;"></div>
                        <div class="stat-card skeleton" style="height: 80px;"></div>
                        <div class="stat-card skeleton" style="height: 80px;"></div>
                    </div>
                    <div id="recentResults">
                        <h3><i class="fas fa-history"></i> Hasil Terbaru</h3>
                        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 10px;">
                            <div class="skeleton" style="height: 20px; width: 60%; margin-bottom: 10px;"></div>
                            <div class="skeleton" style="height: 20px; width: 80%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Hasil Ujian -->
                <div id="hasil" class="content-section">
                    <h2><i class="fas fa-users"></i> Hasil Ujian Peserta</h2>
                    <div class="filters">
                        <input type="text" id="searchNama" placeholder="Cari nama..." onkeyup="filterResults()">
                        <input type="text" id="searchNIK" placeholder="Cari NIK..." onkeyup="filterResults()">
                        <select id="filterBy" onchange="filterResults()">
                            <option value="all">Semua</option>
                            <option value="twk_high">TWK Tertinggi</option>
                            <option value="tiu_high">TIU Tertinggi</option>
                            <option value="tkp_high">TKP Tertinggi</option>
                            <option value="total_high">Total Tertinggi</option>
                        </select>
                        <button class="btn" onclick="loadResults(true)" title="Refresh">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                    <div style="overflow-x: auto;">
                        <table id="resultsTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Formasi 1</th>
                                    <th>Formasi 2</th>
                                    <th>TWK</th>
                                    <th>TIU</th>
                                    <th>TKP</th>
                                    <th>Total</th>
                                    <th>%</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody">
                                <!-- Skeleton rows -->
                                <tr><td colspan="10" style="text-align: center; padding: 20px;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="resultsInfo" style="text-align: center; margin-top: 15px; color: #666;"></div>
                </div>

                <!-- Export Data -->
                <div id="export" class="content-section">
                    <h2><i class="fas fa-file-export"></i> Export Data</h2>
                    <div style="display: flex; gap: 15px; margin: 30px 0; flex-wrap: wrap;">
                        <button class="btn" onclick="exportJSON()">
                            <i class="fas fa-file-code"></i> Export JSON
                        </button>
                        <button class="btn" onclick="exportCSV()">
                            <i class="fas fa-file-csv"></i> Export CSV
                        </button>
                        <button class="btn btn-success" onclick="backupData()">
                            <i class="fas fa-database"></i> Backup Data
                        </button>
                        <button class="btn btn-danger" onclick="clearAllData()">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </div>

                    <div id="exportPreview" style="margin-top: 30px;">
                        <h3><i class="fas fa-eye"></i> Preview Data</h3>
                        <pre id="dataPreview" style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 400px; min-height: 100px;">
Memuat preview data...
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript dengan optimasi loading -->
    <script>
        // ============================================
        // INITIALIZATION - Load immediately
        // ============================================
        
        // Global variables
        let allResults = [];
        let currentResults = [];
        let dataCache = null;
        let cacheTime = null;
        
        // Show main content after initial load
        document.addEventListener('DOMContentLoaded', function() {
            // Apply deferred CSS
            document.getElementById('deferred-css').media = 'all';
            
            // Load cached data from PHP session if available
            <?php if (!empty($_SESSION['cached_data'])): ?>
                dataCache = <?php echo json_encode($_SESSION['cached_data']); ?>;
                cacheTime = <?php echo $_SESSION['cache_time']; ?>;
                allResults = dataCache;
                currentResults = [...dataCache];
                
                // Update count immediately
                document.getElementById('data-count').textContent = 
                    dataCache.length + ' data tersimpan';
                
                // Hide loading screen quickly
                setTimeout(() => {
                    document.getElementById('initial-loading').style.opacity = '0';
                    setTimeout(() => {
                        document.getElementById('initial-loading').style.display = 'none';
                        document.getElementById('main-content').style.display = 'block';
                        document.body.classList.add('loaded');
                        
                        // Load dashboard with cached data
                        loadDashboardFast();
                    }, 300);
                }, 500);
            <?php else: ?>
                // No cache, load fresh data
                loadInitialData();
            <?php endif; ?>
        });

        // ============================================
        // DATA LOADING OPTIMIZATION
        // ============================================
        
        async function loadInitialData() {
            try {
                // Show progress
                document.querySelector('#initial-loading p').textContent = 'Mengambil data dari server...';
                
                // Fetch data with timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);
                
                const response = await fetch('get_data.php?cache=' + Date.now(), {
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                
                // Process data
                allResults = data.results || [];
                currentResults = [...allResults];
                
                // Cache in session via AJAX
                fetch('cache_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ data: allResults })
                }).catch(e => console.log('Cache failed:', e));
                
                // Update UI
                document.getElementById('data-count').textContent = 
                    allResults.length + ' data tersimpan';
                
                // Hide loading and show content
                setTimeout(() => {
                    document.getElementById('initial-loading').style.opacity = '0';
                    setTimeout(() => {
                        document.getElementById('initial-loading').style.display = 'none';
                        document.getElementById('main-content').style.display = 'block';
                        document.body.classList.add('loaded');
                        
                        // Load dashboard
                        loadDashboardFast();
                    }, 300);
                }, 500);
                
            } catch (error) {
                console.error('Initial load error:', error);
                
                // Fallback to cached file
                try {
                    const fallbackResponse = await fetch('hasil.json?t=' + Date.now());
                    const fallbackData = await fallbackResponse.json();
                    allResults = fallbackData || [];
                    currentResults = [...allResults];
                    
                    document.getElementById('data-count').textContent = 
                        allResults.length + ' data (cached)';
                    
                    document.getElementById('initial-loading').style.opacity = '0';
                    setTimeout(() => {
                        document.getElementById('initial-loading').style.display = 'none';
                        document.getElementById('main-content').style.display = 'block';
                        document.body.classList.add('loaded');
                        loadDashboardFast();
                    }, 300);
                    
                } catch (fallbackError) {
                    // Show error
                    document.getElementById('initial-loading').innerHTML = `
                        <div style="text-align: center; color: #666;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ff6b6b; margin-bottom: 20px;"></i>
                            <h3>Gagal Memuat Data</h3>
                            <p>${error.message}</p>
                            <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                <i class="fas fa-redo"></i> Coba Lagi
                            </button>
                        </div>
                    `;
                }
            }
        }
        
        // Fast dashboard loading with cached data
        function loadDashboardFast() {
            if (allResults.length === 0) {
                document.getElementById('stats').innerHTML = `
                    <div class="stat-card">
                        <div class="stat-number">0</div>
                        <div>Total Peserta</div>
                    </div>
                `;
                document.getElementById('recentResults').innerHTML = `
                    <h3><i class="fas fa-history"></i> Hasil Terbaru</h3>
                    <p style="text-align: center; color: #666; padding: 20px;">Belum ada data</p>
                `;
                return;
            }
            
            // Calculate stats (optimized)
            const results = allResults;
            const total = results.length;
            
            // Use cached calculations if available
            const stats = calculateStatsFast(results);
            
            // Update stats with animation
            document.getElementById('stats').innerHTML = `
                <div class="stat-card">
                    <div class="stat-number">${total}</div>
                    <div>Total Peserta</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.avgTWK}</div>
                    <div>Rata TWK</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.avgTIU}</div>
                    <div>Rata TIU</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.avgTKP}</div>
                    <div>Rata TKP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.avgTotal}</div>
                    <div>Rata Total</div>
                </div>
            `;
            
            // Show recent results (max 5)
            const recent = results.slice(-5).reverse();
            document.getElementById('recentResults').innerHTML = `
                <h3><i class="fas fa-history"></i> Hasil Terbaru</h3>
                ${recent.length > 0 ? `
                <div style="overflow-x: auto;">
                    <table style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIK</th>
                                <th>Formasi 1</th>
                                <th>Formasi 2</th>
                                <th>TWK</th>
                                <th>TIU</th>
                                <th>TKP</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${recent.map(r => `
                                <tr>
                                    <td>${r.nama?.substring(0, 20) || '-'}${r.nama?.length > 20 ? '...' : ''}</td>
                                    <td><code>${r.nik?.substring(0, 10) || '-'}...</code></td>
                                    <td>${r.formasi1?.substring(0, 20) || '-'}${r.formasi1?.length > 20 ? '...' : ''}</td>
                                    <td>${r.formasi2?.substring(0, 20) || '-'}${r.formasi2?.length > 20 ? '...' : ''}</td>
                                    
                                    <td>${r.twk || 0}</td>
                                    <td>${r.tiu || 0}</td>
                                    <td>${r.tkp || 0}</td>
                                    <td><strong>${r.total || 0}</strong></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                ` : '<p style="text-align: center; color: #666; padding: 20px;">Belum ada data</p>'}
            `;
            
            // Load results table in background
            setTimeout(() => {
                if (document.getElementById('hasil').classList.contains('active')) {
                    loadResults();
                }
            }, 100);
        }
        
        // Optimized stats calculation
        function calculateStatsFast(results) {
            if (results.length === 0) {
                return { avgTWK: 0, avgTIU: 0, avgTKP: 0, avgTotal: 0 };
            }
            
            let sumTWK = 0, sumTIU = 0, sumTKP = 0, sumTotal = 0;
            
            // Process in chunks to avoid blocking
            const chunkSize = 50;
            for (let i = 0; i < results.length; i += chunkSize) {
                const chunk = results.slice(i, i + chunkSize);
                chunk.forEach(r => {
                    sumTWK += r.twk || 0;
                    sumTIU += r.tiu || 0;
                    sumTKP += r.tkp || 0;
                    sumTotal += r.total || 0;
                });
            }
            
            return {
                avgTWK: (sumTWK / results.length).toFixed(1),
                avgTIU: (sumTIU / results.length).toFixed(1),
                avgTKP: (sumTKP / results.length).toFixed(1),
                avgTotal: (sumTotal / results.length).toFixed(1)
            };
        }
        
        // ============================================
        // TAB NAVIGATION
        // ============================================
        
        function showTab(tabName) {
            // Update tabs
            document.querySelectorAll(".tab").forEach(tab => tab.classList.remove("active"));
            event.target.classList.add("active");
            
            // Update content
            document.querySelectorAll(".content-section").forEach(section => {
                section.classList.remove("active");
            });
            document.getElementById(tabName).classList.add("active");
            
            // Load data if needed
            if (tabName === 'hasil' && allResults.length > 0) {
                loadResults();
            } else if (tabName === 'export') {
                loadExportPreview();
            }
        }
        
        // ============================================
        // RESULTS MANAGEMENT (OPTIMIZED)
        // ============================================
        
        async function loadResults(forceRefresh = false) {
            const tbody = document.getElementById('resultsBody');
            const infoEl = document.getElementById('resultsInfo');
            
            // Show loading state
            tbody.innerHTML = `
                <tr><td colspan="10" style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                </td></tr>
            `;
            
            try {
                // Use cached data if not forcing refresh
                if (!forceRefresh && allResults.length > 0) {
                    displayResultsFast(allResults);
                    return;
                }
                
                // Fetch fresh data
                const response = await fetch('hasil.json?t=' + Date.now());
                if (!response.ok) throw new Error('Failed to load');
                
                const results = await response.json();
                allResults = results || [];
                currentResults = [...allResults];
                
                // Update cache
                fetch('cache_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ data: allResults })
                }).catch(e => console.log('Cache update failed'));
                
                displayResultsFast(allResults);
                
            } catch (error) {
                console.error('Load results error:', error);
                tbody.innerHTML = `
                    <tr><td colspan="10" style="text-align: center; padding: 30px; color: #666;">
                        <i class="fas fa-exclamation-triangle"></i> Gagal memuat data
                    </td></tr>
                `;
                infoEl.textContent = 'Error loading data';
            }
        }
        
        function displayResultsFast(results) {
            const tbody = document.getElementById('resultsBody');
            const infoEl = document.getElementById('resultsInfo');
            
            if (!results || results.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 30px; color: #666;">
                            <i class="fas fa-database"></i> Belum ada data
                        </td>
                    </tr>
                `;
                infoEl.textContent = '0 data';
                return;
            }
            
            // Create document fragment for faster DOM manipulation
            const fragment = document.createDocumentFragment();
            
            // Add rows in chunks
            const chunkSize = 30;
            const displayCount = Math.min(results.length, 100); // Limit display
            
            for (let i = 0; i < displayCount; i++) {
                const r = results[i];
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${i + 1}</td>
                    <td><strong title="${r.nama || ''}">${(r.nama || '-').substring(0, 20)}${(r.nama || '').length > 20 ? '...' : ''}</strong></td>
                    <td><code title="${r.nik || ''}">${(r.nik || '-').substring(0, 10)}...</code></td>
                    <td><strong title="${r.formasi1 || ''}">${(r.formasi1 || '-').substring(0, 20)}${(r.formasi1 || '').length > 20 ? '...' : ''}</strong></td>
                    <td><strong title="${r.formasi2 || ''}">${(r.formasi2 || '-').substring(0, 20)}${(r.formasi2 || '').length > 20 ? '...' : ''}</strong></td>
                    <td>${r.twk || 0}</td>
                    <td>${r.tiu || 0}</td>
                    <td>${r.tkp || 0}</td>
                    <td><strong>${r.total || 0}</strong></td>
                    <td>${r.percentage ? r.percentage.toFixed(1) + '%' : 'N/A'}</td>
                    <td>${formatDateShort(r.submitted_at || r.timestamp)}</td>
                    <td>
                        <button class="btn" onclick="viewDetail(${i})" title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-danger" onclick="deleteResult(${i})" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                fragment.appendChild(row);
            }
            
            tbody.innerHTML = '';
            tbody.appendChild(fragment);
            
            infoEl.innerHTML = `Menampilkan ${displayCount} dari ${results.length} data`;
        }
        
        function formatDateShort(dateString) {
            try {
                const date = new Date(dateString);
                const now = new Date();
                const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));
                
                if (diffDays === 0) {
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                } else if (diffDays === 1) {
                    return 'Kemarin';
                } else if (diffDays < 7) {
                    return date.toLocaleDateString('id-ID', { weekday: 'short' });
                } else {
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
                }
            } catch {
                return '-';
            }
        }
        
        // ============================================
        // DELETE FUNCTION (OPTIMIZED)
        // ============================================
        
        async function deleteResult(index) {
    if (!confirm('Hapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    
    try {
        // Show loading
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Remove from local array
        const deletedItem = allResults.splice(index, 1)[0];
        currentResults = [...allResults];
        
        // Debug: Log data yang akan dikirim
        console.log('Deleting item:', deletedItem);
        console.log('Remaining items:', allResults.length);
        
        // PERBAIKAN: Gunakan file yang benar - save_result.php
        // dan kirim dalam format array
        const response = await fetch('save_result.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(allResults)
        });
        
        const result = await response.json();
        console.log('Server response:', result);
        
        if (result.success) {
            // Update UI
            displayResultsFast(currentResults);
            loadDashboardFast();
            loadExportPreview(); // Refresh preview juga
            
            // Update count
            document.getElementById('data-count').textContent = 
                allResults.length + ' data tersimpan';
            
            // Show success message
            showNotification('Data berhasil dihapus!', 'success');
            
            // Update cache
            fetch('cache_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ data: allResults })
            }).catch(e => console.log('Cache update failed:', e));
            
        } else {
            // Restore data if failed
            allResults.splice(index, 0, deletedItem);
            currentResults = [...allResults];
            throw new Error(result.error || 'Delete failed');
        }
        
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Gagal menghapus: ' + error.message, 'error');
        
        // Reload data to restore
        loadResults(true);
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}
        
        // ============================================
        // EXPORT FUNCTIONS
        // ============================================
        
        async function loadExportPreview() {
            const preview = document.getElementById('dataPreview');
            preview.textContent = 'Mengambil data...';
            
            try {
                const response = await fetch('hasil.json?t=' + Date.now());
                const data = await response.json();
                
                // Show limited preview for performance
                const previewData = data.slice(0, 10); // Only first 10 records
                preview.textContent = JSON.stringify(previewData, null, 2);
                
                if (data.length > 10) {
                    preview.textContent += `\n\n... dan ${data.length - 10} data lainnya`;
                }
            } catch (error) {
                preview.textContent = 'Error: ' + error.message;
            }
        }
        
        function exportJSON() {
            const data = {
                hasil_ujian: allResults,
                exported_at: new Date().toISOString(),
                total: allResults.length
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `hasil_ujian_${new Date().toISOString().split('T')[0]}.json`;
            a.click();
            
            showNotification('JSON berhasil diunduh', 'success');
        }
        
        function exportCSV() {
            if (allResults.length === 0) {
                showNotification('Tidak ada data untuk diexport', 'error');
                return;
            }
            
            const headers = ['Nama', 'NIK','Formasi 1','Formasi 2', 'TWK', 'TIU', 'TKP', 'Total', 'Persentase', 'Waktu'];
            const rows = allResults.map(r => [
                `"${(r.nama || '').replace(/"/g, '""')}"`,
                `"${(r.nik || '').replace(/"/g, '""')}"`,
                `"${(r.formasi1 || '').replace(/"/g, '""')}"`,
                `"${(r.formasi2 || '').replace(/"/g, '""')}"`,
                r.twk || 0,
                r.tiu || 0,
                r.tkp || 0,
                r.total || 0,
                r.percentage ? r.percentage.toFixed(2) : 0,
                `"${(r.submitted_at || r.timestamp || '').replace(/"/g, '""')}"`
            ]);
            
            const csv = [headers.join(','), ...rows].join('\n');
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `hasil_ujian_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            
            showNotification('CSV berhasil diunduh', 'success');
        }
        
        async function clearAllData() {
    if (!confirm('HAPUS SEMUA DATA? Tindakan ini akan menghapus semua data peserta dan tidak dapat dikembalikan!')) {
        return;
    }
    
    const button = event.target;
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        const response = await fetch('save_result.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify([]) // Kirim array kosong
        });
        
        const result = await response.json();
        console.log('Clear response:', result);
        
        if (result.success) {
            // Clear local data
            allResults = [];
            currentResults = [];
            
            // Update UI
            displayResultsFast([]);
            loadDashboardFast();
            loadExportPreview();
            
            // Update count
            document.getElementById('data-count').textContent = '0 data tersimpan';
            
            // Clear cache
            fetch('cache_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ data: [] })
            }).catch(e => console.log('Cache clear failed:', e));
            
            showNotification('Semua data berhasil dihapus!', 'success');
        } else {
            throw new Error(result.error || 'Clear failed');
        }
    } catch (error) {
        console.error('Clear all error:', error);
        showNotification('Gagal menghapus data: ' + error.message, 'error');
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}
        
        // ============================================
        // UTILITY FUNCTIONS
        // ============================================
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
                display: flex;
                align-items: center;
                gap: 8px;
                max-width: 300px;
            `;
            
            notification.className = `notification-${type}`;
            notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            // Auto remove
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        function viewDetail(index) {
            const result = currentResults[index];
            if (!result) return;
            
            const detail = `
📊 DETAIL HASIL UJIAN
===================

👤 IDENTITAS
----------
Nama: ${result.nama || '-'}
NIK: ${result.nik || '-'}
NIK: ${result.formasi || '-'}

🎯 SKOR
-------
TWK: ${result.twk || 0} / 30
TIU: ${result.tiu || 0} / 35
TKP: ${result.tkp || 0} / 225

📈 TOTAL: ${result.total || 0} / 290
📊 Persentase: ${result.percentage ? result.percentage.toFixed(2) : 0}%

⏱️ WAKTU
--------
Submit: ${new Date(result.submitted_at || result.timestamp).toLocaleString()}
${result.timeSpent ? `Durasi: ${Math.floor(result.timeSpent / 60)} menit ${result.timeSpent % 60} detik` : ''}
            `;
            
            alert(detail);
        }
        
        function filterResults() {
            const searchNama = document.getElementById('searchNama').value.toLowerCase();
            const searchNIK = document.getElementById('searchNIK').value.toLowerCase();
            const filterBy = document.getElementById('filterBy').value;
            
            let filtered = allResults.filter(r => {
                const namaMatch = (r.nama || '').toLowerCase().includes(searchNama);
                const nikMatch = (r.nik || '').toLowerCase().includes(searchNIK);
                return namaMatch && nikMatch;
            });
            
            // Apply sorting
            if (filterBy !== 'all') {
                filtered.sort((a, b) => {
                    switch(filterBy) {
                        case 'twk_high': return (b.twk || 0) - (a.twk || 0);
                        case 'tiu_high': return (b.tiu || 0) - (a.tiu || 0);
                        case 'tkp_high': return (b.tkp || 0) - (a.tkp || 0);
                        case 'total_high': return (b.total || 0) - (a.total || 0);
                        default: return 0;
                    }
                });
            }
            
            currentResults = filtered;
            displayResultsFast(filtered);
        }
        
        function backupData() {
            // Simple backup by downloading current data
            exportJSON();
        }
        
        function showImport() {
            alert('Fitur import data akan datang!');
        }
    </script>
</body>
</html>