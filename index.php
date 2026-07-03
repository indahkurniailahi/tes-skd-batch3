<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SKD Pemagangan Nasional Batch III</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #2f4298ff 0%, #d7d0ddff 100%);
            min-height: 100vh;
        }
        
        .page {
            display: none;
        }
        
        .page.active {
            display: block;
        }
        
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 48px;
            /* color: #667eea; */
            margin-bottom: 15px;
            width: 210px;
            height: auto;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .login-form {
            margin-top: 30px;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .input-group input:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .info-box {
            background: #f0f7ff;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            color: #2c5282;
        }
        
        .info-box i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .btn-start {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #334ec6ff 0%, #d3cdd8ff 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .btn-start:hover {
            transform: translateY(-2px);
        }
        
        .btn-start i {
            margin-right: 8px;
        }
        
        /* Exam Page Styles */
        .exam-header {
            background: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .timer {
            background: #ff6b6b;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .timer i {
            margin-right: 8px;
        }
        
        .exam-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
            padding: 0 20px;
        }
        
        .question-nav {
            flex: 0 0 250px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .number-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin: 15px 0;
        }
        
        .number-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            font-weight: 600;
        }
        
        .number-btn.answered {
            background: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        
        .number-btn.current {
            background: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        
        .question-area {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .question-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .options {
            margin-top: 20px;
        }
        
        .option {
            padding: 15px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .option:hover {
            border-color: #667eea;
            background: #f0f7ff;
        }
        
        .option.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .navigation {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
        }
        
        .btn-nav {
            padding: 12px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .btn-nav:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-nav i {
            margin: 0 5px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .warning {
            text-align: center;
            color: #ff6b6b;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .warning i {
            margin-right: 5px;
        }
        
        /* Result Page Styles */
        .result-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .result-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .result-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .result-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            border: 2px solid #e9ecef;
        }
        
        .score-display {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .total-score {
            display: flex;
            justify-content: center;
            align-items: baseline;
            margin-bottom: 10px;
        }
        
        .score-label {
            font-size: 18px;
            color: #6c757d;
            margin-right: 15px;
        }
        
        .score-value {
            font-size: 48px;
            font-weight: bold;
            color: #4a6bff;
        }
        
        .score-max {
            font-size: 24px;
            color: #adb5bd;
            margin-left: 5px;
        }
        
        .percentage {
            font-size: 20px;
            color: #28a745;
            font-weight: 600;
        }
        
        .category-scores {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .category-score {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .category-label {
            display: block;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .category-value {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #4a6bff;
        }
        
        .participant-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f3f5;
            justify-content: center;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
            border-bottom: none;
        }
        
        .info-item i {
            color: #4a6bff;
            width: 25px;
            margin-right: 10px;
        }
        
        .result-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-restart, .btn-detail {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-restart {
            background: #4a6bff;
            color: white;
        }
        
        .btn-restart:hover {
            background: #3a5bef;
        }
        
        .btn-detail {
            background: #6c757d;
            color: white;
        }
        
        .btn-detail:hover {
            background: #5a6268;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .modal-header i {
            font-size: 48px;
            color: #ff6b6b;
            margin-bottom: 15px;
        }
        
        .modal-body {
            margin: 20px 0;
        }
        
        .warning-text {
            color: #ff6b6b;
            text-align: center;
            margin-top: 15px;
        }
        
        .modal-footer {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn-cancel, .btn-confirm {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-confirm {
            background: #4CAF50;
            color: white;
        }

        .formasi-select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    background-color: white;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 15px;
    transition: border-color 0.3s;
    }

    .formasi-select:focus {
    border-color: #667eea;
    outline: none;
}

.formasi-select option {
    padding: 10px;
    font-size: 15px;
}

.formasi-select option[value=""] {
    color: #999;
}

/* Solusi paling sederhana */
.exam-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: white;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.exam-container {
    padding-top: 10px; /* Tambahkan sedikit padding agar konten tidak menempel ke header */
}
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Halaman Login -->
    <div id="loginPage" class="page active">
        <div class="container">
            <div class="header">
                <img src="logo-kemnaker.png" alt="Logo" class="logo">
                <h1>Tes SKD Peserta Magang Nasional Batch III BPVP Pangkep</h1>
                <p class="subtitle">Masukkan data diri untuk memulai ujian</p>
            </div>
            
            <div class="login-form">
                <div class="input-group">
                    <label for="nama">
                        <i class="fas fa-user"></i> Nama Lengkap
                    </label>
                    <input type="text" id="nama" placeholder="Masukkan nama lengkap">
                </div>
                
                <div class="input-group">
                    <label for="nik">
                        <i class="fas fa-id-card"></i> NIK
                    </label>
                    <input type="text" id="nik" placeholder="Masukkan NIK">
                </div>
                <!-- GANTI INPUT TEXT MENJADI DROPDOWN -->
                <div class="input-group">
                    <label for="formasi">
                        <i class="fas fa-briefcase"></i> Pilih Formasi 1
                    </label>
                    <select id="formasi1" class="formasi-select">
                        <option value="">-- Pilih Formasi 1 --</option>
                        <option value="DIGITAL OFFICE SPECIALIST">DIGITAL OFFICE SPECIALIST</option>
                        <option value="ASISTEN INSTRUKTUR BISMAN">ASISTEN INSTRUKTUR BISMAN</option>
                        <option value="ASISTEN INSTRUKTUR FASHION TEKNOLOGI">ASISTEN INSTRUKTUR FASHION TEKNOLOGI</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN LISTRIK">ASISTEN INSTRUKTUR KEJURUAN LISTRIK</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN PARIWISATA">ASISTEN INSTRUKTUR PARIWISATA</option>
                        <option value="ASISTEN INSTRUKTUR PENGELASAN">ASISTEN INSTRUKTUR PENGELASAN</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN PROCESSING">ASISTEN INSTRUKTUR KEJURUAN PROCESSING</option>
                        <option value="ASISTEN INSTRUKTUR SMART FARMING & MEKANISASI PERTANIAN">ASISTEN INSTRUKTUR SMART FARMING & MEKANISASI PERTANIAN</option>
                        <option value="ASISTEN INSTRUKTUR TIK">ASISTEN INSTRUKTUR TIK</option>
                        <option value="PENGELOLA ARSIP/PENGADMINISTRASI UMUM">PENGELOLA ARSIP/PENGADMINISTRASI UMUM</option>
                        <option value="STAF KOMUNIKASI & DIGITAL">STAF KOMUNIKASI & DIGITAL</option>
                        <option value="TALENT & INNOVATION HUB">TALENT & INNOVATION HUB</option>
                        <option value="TENAGA PERKANTORAN UMUM">TENAGA PERKANTORAN UMUM</option>
                        <option value="TOOLMAN KEJURUAN LISTRIK">TOOLMAN KEJURUAN LISTRIK</option>
                        <option value="TOOLMAN/TEKNISI PERALATAN WORKSHOP PENGELASAN">TOOLMAN/TEKNISI PERALATAN WORKSHOP PENGELASAN</option>
                    </select>
                </div>

                <div class="input-group">
                    <label for="formasi">
                        <i class="fas fa-briefcase"></i> Pilih Formasi 2
                    </label>
                    <select id="formasi2" class="formasi-select">
                        <option value="">-- Pilih Formasi 2 --</option>
                        <option value="DIGITAL OFFICE SPECIALIST">DIGITAL OFFICE SPECIALIST</option>
                        <option value="ASISTEN INSTRUKTUR BISMAN">ASISTEN INSTRUKTUR BISMAN</option>
                        <option value="ASISTEN INSTRUKTUR FASHION TEKNOLOGI">ASISTEN INSTRUKTUR FASHION TEKNOLOGI</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN LISTRIK">ASISTEN INSTRUKTUR KEJURUAN LISTRIK</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN PARIWISATA">ASISTEN INSTRUKTUR PARIWISATA</option>
                        <option value="ASISTEN INSTRUKTUR PENGELASAN">ASISTEN INSTRUKTUR PENGELASAN</option>
                        <option value="ASISTEN INSTRUKTUR KEJURUAN PROCESSING">ASISTEN INSTRUKTUR KEJURUAN PROCESSING</option>
                        <option value="ASISTEN INSTRUKTUR SMART FARMING & MEKANISASI PERTANIAN">ASISTEN INSTRUKTUR SMART FARMING & MEKANISASI PERTANIAN</option>
                        <option value="ASISTEN INSTRUKTUR TIK">ASISTEN INSTRUKTUR TIK</option>
                        <option value="PENGELOLA ARSIP/PENGADMINISTRASI UMUM">PENGELOLA ARSIP/PENGADMINISTRASI UMUM</option>
                        <option value="STAF KOMUNIKASI & DIGITAL">STAF KOMUNIKASI & DIGITAL</option>
                        <option value="TALENT & INNOVATION HUB">TALENT & INNOVATION HUB</option>
                        <option value="TENAGA PERKANTORAN UMUM">TENAGA PERKANTORAN UMUM</option>
                        <option value="TOOLMAN KEJURUAN LISTRIK">TOOLMAN KEJURUAN LISTRIK</option>
                        <option value="TOOLMAN/TEKNISI PERALATAN WORKSHOP PENGELASAN">TOOLMAN/TEKNISI PERALATAN WORKSHOP PENGELASAN</option>
                    </select>
                </div>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Ujian terdiri dari 110 soal pilihan ganda. Waktu: 100 menit.</p>
                </div>
                
                <div class="warning-box" id="recoveryWarning" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                <div>
                <strong>Data ujian sebelumnya ditemukan!</strong>
                <p style="margin: 5px 0 0 0; font-size: 12px;">
                Isi Nama dan NIK yang sama untuk melanjutkan ujian yang terputus.
                </p>
        </div>
        </div>
    </div>      
                <button onclick="startExam()" class="btn-start">
                    <i class="fas fa-play"></i> Mulai Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- Halaman Ujian -->
    <div id="examPage" class="page">
        <div class="exam-header">
            <div class="exam-info">
                <h2><i class="fas fa-user-graduate"></i> <span id="studentName"></span></h2>
                <p>NIK: <span id="studentNIK"></span></p>
                <p>Formasi: <span id="studentFormasi"></span></p>
            </div>
            <div class="timer">
                <i class="fas fa-clock"></i>
                <span id="countdown">100:00</span>
            </div>
        </div>

        <div class="exam-container">
            <!-- Sidebar Navigasi Soal -->
            <div class="question-nav">
                <h3><i class="fas fa-list-ol"></i> Daftar Soal</h3>
                <div id="questionNumbers" class="number-grid"></div>
                <div class="progress">
                    <div class="progress-bar" id="progressBar" style="width: 0%; height: 10px; background: #4CAF50; border-radius: 5px;"></div>
                    <span id="progressText">0/110</span>
                </div>
            </div>

            <!-- Area Soal -->
            <div class="question-area">
                <div class="question-header">
                    <h3>Soal <span id="currentQuestionNum">1</span> dari 110</h3>
                    <!-- <div class="question-status">
                        <span class="status answered"><i class="fas fa-circle"></i> Terjawab</span>
                        <span class="status current"><i class="fas fa-circle"></i> Sedang dikerjakan</span>
                        <span class="status unanswered"><i class="fas fa-circle"></i> Belum dijawab</span>
                    </div> -->
                </div>

                <div class="question-card">
                    <h4 id="questionText">Memuat soal...</h4>
                    <div id="options" class="options">
                        <!-- Opsi akan diisi JavaScript -->
                    </div>
                </div>

                <!-- Navigasi Soal -->
                <div class="navigation">
                    <button onclick="prevQuestion()" class="btn-nav" id="prevBtn">
                        <i class="fas fa-arrow-left"></i> Soal Sebelumnya
                    </button>
                    
                    <button onclick="nextQuestion()" class="btn-nav" id="nextBtn">
                        Soal Berikutnya <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Tombol Submit -->
                <div class="submit-section">
                    <button onclick="showConfirmation()" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Selesai & Kirim Jawaban
                    </button>
                    <p class="warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Pastikan semua soal telah dicek sebelum submit
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Submit -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Konfirmasi Submit</h3>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mengirim jawaban?</p>
                <div id="summary">
                    <!-- Ringkasan jawaban akan ditampilkan di sini -->
                </div>
                <p class="warning-text">
                    <i class="fas fa-info-circle"></i>
                    Setelah submit, Anda tidak dapat mengubah jawaban.
                </p>
            </div>
            <div class="modal-footer">
                <button onclick="hideConfirmation()" class="btn-cancel">
                    <i class="fas fa-times"></i> Kembali Periksa
                </button>
                <button onclick="submitExam()" class="btn-confirm">
                    <i class="fas fa-check"></i> Ya, Submit Jawaban
                </button>
            </div>
        </div>
    </div>

    <!-- Halaman Hasil -->
    <div id="resultPage" class="page">
        <div class="result-container">
            <div class="result-card">
                <div class="result-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h2>Hasil Tes SKD</h2>
                
                <div class="result-summary">
                    <div class="score-display">
                        <div class="total-score">
                            <span class="score-label">Total Skor</span>
                            <span class="score-value" id="totalScoreDisplay">0</span>
                            <span class="score-max" id="maxScoreDisplay">/550</span>
                        </div>
                        <div class="percentage" id="percentageDisplay">0%</div>
                    </div>
                    
                    <div class="category-scores">
                        <div class="category-score">
                            <span class="category-label">TWK</span>
                            <span class="category-value" id="twkScoreDisplay">0/150</span>
                        </div>
                        <div class="category-score">
                            <span class="category-label">TIU</span>
                            <span class="category-value" id="tiuScoreDisplay">0/175</span>
                        </div>
                        <div class="category-score">
                            <span class="category-label">TKP</span>
                            <span class="category-value" id="tkpScoreDisplay">0/225</span>
                        </div>
                    </div>
                </div>

                <div class="participant-info">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span id="resultName"></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-id-card"></i>
                        <span id="resultNIK"></span>
                    </div>
                    <div class="info-item">
        <i class="fas fa-briefcase"></i>
        <span id="resultFormasi1"></span>
    </div>
    <div class="info-item" id="formasi2Item" style="display: none;">
        <i class="fas fa-briefcase"></i>
        <span id="resultFormasi2"></span>
    </div>
    <div class="info-item">
        <i class="fas fa-clock"></i>
        <span id="submitTime"></span>
    </div>
                </div>

                <div class="result-actions">
                    <button onclick="restartExam()" class="btn-restart">
                        <i class="fas fa-redo"></i> Keluar
                    </button>
                    <button onclick="showDetail()" class="btn-detail">
                        <i class="fas fa-list"></i> Detail Jawaban
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="ujian.js"></script>
</body>
</html>