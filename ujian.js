// Konfigurasi
const TOTAL_QUESTIONS = 110;
const EXAM_TIME = 100 * 60; // 100 menit dalam detik
const CATEGORIES = {
  TWK: { total: 30, points: 5 },
  TIU: { total: 35, points: 5 },
  TKP: { total: 45, points: "variable" },
};

// Variabel global
let questions = [];
let userAnswers = {};
let currentQuestion = 0;
let examStarted = false;
let timerInterval;
let timeLeft = EXAM_TIME;
let studentData = {};
let autoSaveInterval;

// Soal default (akan diambil dari soal.json)
const defaultQuestions = Array(TOTAL_QUESTIONS)
  .fill()
  .map((_, i) => ({
    id: i + 1,
    category: i < 30 ? "TWK" : i < 65 ? "TIU" : "TKP",
    question: `Ini adalah contoh soal nomor ${
      i + 1
    }. Pilih jawaban yang benar?`,
    options: {
      A: "Pilihan A",
      B: "Pilihan B",
      C: "Pilihan C",
      D: "Pilihan D",
    },
    correctAnswer: ["A", "B", "C", "D"][Math.floor(Math.random() * 4)],
    points: i < 65 ? 5 : "variable",
    ...(i >= 65 && {
      scoring: {
        A: 1,
        B: 2,
        C: 3,
        D: 4,
        E: 5,
      },
    }),
  }));

// Inisialisasi dengan cek backup
async function init() {
  await loadQuestions();

  // Cek backup data dari localStorage
  const backup = checkForBackup();
  if (backup) {
    // Restore dari backup
    studentData = backup.studentData || {};
    userAnswers = backup.userAnswers || {};
    currentQuestion = backup.currentQuestion || 0;
    timeLeft = backup.timeLeft || EXAM_TIME;

    // Tampilkan data di form login
     document.getElementById("nama").value = studentData.nama || "";
    document.getElementById("nik").value = studentData.nik || "";
    document.getElementById("formasi1").value = studentData.formasi1 || "";
    document.getElementById("formasi2").value = studentData.formasi2 || "";

    // Tampilkan notifikasi recovery
    showRecoveryNotification(backup.savedAt);

    // Tampilkan warning di form login
    document.getElementById("recoveryWarning").style.display = "flex";
  }

  updateQuestionNumbers();

  // Tambah event listener untuk beforeunload
  window.addEventListener("beforeunload", handleBeforeUnload);
}

// Cek data backup di localStorage
function checkForBackup() {
  try {
    const backup = localStorage.getItem("exam_backup");
    if (backup) {
      const data = JSON.parse(backup);
      const savedTime = new Date(data.savedAt);
      const now = new Date();
      const diffMinutes = (now - savedTime) / (1000 * 60);

      // Jika backup kurang dari 2 jam yang lalu, tawarkan recovery
      if (diffMinutes < 120) {
        return data;
      } else {
        // Hapus backup lama (lebih dari 2 jam)
        localStorage.removeItem("exam_backup");
        console.log("Backup expired, removed");
      }
    }
  } catch (e) {
    console.error("Error checking backup:", e);
    localStorage.removeItem("exam_backup"); // Hapus backup yang corrupt
  }
  return null;
}

// Tampilkan notifikasi recovery
function showRecoveryNotification(savedAt) {
  const notification = document.createElement("div");
  notification.className = "recovery-notification";
  notification.innerHTML = `
        <div style="position: fixed; top: 20px; right: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10000; max-width: 300px; animation: slideIn 0.5s ease-out;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-history" style="font-size: 20px;"></i>
                <div>
                    <strong>📁 Data Ujian Ditemukan!</strong>
                    <p style="margin: 5px 0; font-size: 12px;">Tersimpan: ${new Date(
                      savedAt
                    ).toLocaleTimeString()}</p>
                    <p style="margin: 0; font-size: 12px;">Isi Nama & NIK yang sama lalu klik "Mulai Ujian"</p>
                </div>
            </div>
        </div>
    `;
  document.body.appendChild(notification);

  // Hapus setelah 15 detik
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 15000);
}

// Handle beforeunload event
function handleBeforeUnload(e) {
  if (examStarted && Object.keys(userAnswers).length > 0) {
    // Simpan terakhir kali sebelum tutup
    saveToLocalStorage();

    // Tampilkan konfirmasi browser
    e.preventDefault();
    e.returnValue =
      "Jawaban Anda belum disimpan secara permanen. Yakin ingin meninggalkan halaman?";
    return e.returnValue;
  }
}

// Simpan ke localStorage
function saveToLocalStorage() {
  if (!examStarted || Object.keys(studentData).length === 0) return;

  const examState = {
    studentData: studentData,
    userAnswers: userAnswers,
    currentQuestion: currentQuestion,
    timeLeft: timeLeft,
    savedAt: new Date().toISOString(),
  };

  try {
    localStorage.setItem("exam_backup", JSON.stringify(examState));

    // Tampilkan indicator save (opsional)
    showSaveIndicator();
  } catch (e) {
    console.error("Failed to save to localStorage:", e);

    // Coba hapus data lama jika quota penuh
    if (e.name === "QuotaExceededError") {
      localStorage.removeItem("exam_backup");
      setTimeout(() => saveToLocalStorage(), 1000); // Coba lagi
    }
  }
}

// Tampilkan indicator save berhasil
function showSaveIndicator() {
  const indicator = document.getElementById("saveIndicator");
  if (!indicator) {
    const indicator = document.createElement("div");
    indicator.id = "saveIndicator";
    indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 5px;
        `;
    document.body.appendChild(indicator);
  }

  const indicatorEl = document.getElementById("saveIndicator");
  indicatorEl.innerHTML = `<i class="fas fa-save"></i> Tersimpan`;
  indicatorEl.style.opacity = "1";

  // Hilangkan setelah 2 detik
  setTimeout(() => {
    indicatorEl.style.opacity = "0";
  }, 2000);
}

// Mulai auto-save interval
function startAutoSave() {
  // Simpan pertama kali
  saveToLocalStorage();

  // Set interval setiap 30 detik
  autoSaveInterval = setInterval(() => {
    saveToLocalStorage();
  }, 30000); // 30 detik

  // Juga simpan ke sessionStorage setiap 10 detik
  setInterval(() => {
    saveToSessionStorage();
  }, 10000);
}

// Hentikan auto-save
function stopAutoSave() {
  if (autoSaveInterval) {
    clearInterval(autoSaveInterval);
  }

  // Hapus backup
  localStorage.removeItem("exam_backup");
  sessionStorage.removeItem("exam_session");

  // Hapus indicator jika ada
  const indicator = document.getElementById("saveIndicator");
  if (indicator) {
    indicator.remove();
  }
}

// Backup tambahan ke sessionStorage
function saveToSessionStorage() {
  if (!examStarted) return;

  const sessionData = {
    userAnswers: userAnswers,
    currentQuestion: currentQuestion,
    timeLeft: timeLeft,
    timestamp: Date.now(),
  };

  try {
    sessionStorage.setItem("exam_session", JSON.stringify(sessionData));
  } catch (e) {
    console.error("Failed to save to sessionStorage:", e);
  }
}

// Cek sessionStorage saat halaman dimuat ulang
function checkSessionStorage() {
  try {
    const sessionData = sessionStorage.getItem("exam_session");
    if (sessionData) {
      const data = JSON.parse(sessionData);
      const timeDiff = Date.now() - data.timestamp;

      // Jika kurang dari 5 menit, gunakan data ini
      if (timeDiff < 5 * 60 * 1000) {
        userAnswers = data.userAnswers || {};
        currentQuestion = data.currentQuestion || 0;
        timeLeft = data.timeLeft || EXAM_TIME;
        return true;
      }
    }
  } catch (e) {
    console.error("Error checking sessionStorage:", e);
  }
  return false;
}

// Muat soal dari file
async function loadQuestions() {
  try {
    const response = await fetch("soal.json");
    if (response.ok) {
      const data = await response.json();
      if (Array.isArray(data) && data.length >= TOTAL_QUESTIONS) {
        questions = data.slice(0, TOTAL_QUESTIONS);
      } else {
        questions = defaultQuestions;
        console.warn(
          "Menggunakan soal default: Data soal tidak cukup atau format salah"
        );
      }
    } else {
      questions = defaultQuestions;
      console.warn("Menggunakan soal default: Gagal memuat soal.json");
    }
  } catch (error) {
    console.error("Menggunakan soal default:", error);
    questions = defaultQuestions;
  }
}

// Mulai ujian
function startExam() {
  const nama = document.getElementById("nama").value.trim();
  const nik = document.getElementById("nik").value.trim();
  const formasi1 = document.getElementById("formasi1").value;
  const formasi2 = document.getElementById("formasi2").value;
  // const formasi = formasiSelect.value; // Ambil nilai dari dropdown

  if (!nama || !nik || !formasi1) {
    alert("Harap isi Nama Lengkap, NIK, dan Formasi!");
    return;
  }

  if (nik.length < 16) {
    alert("NIK minimal 16 karakter!");
    return;
  }

  // Cek apakah ada backup dari peserta lain
  const backup = localStorage.getItem("exam_backup");
  if (backup) {
    const backupData = JSON.parse(backup);
    if (backupData.studentData.nik && backupData.studentData.nik !== nik) {
      if (
        !confirm(
          "⚠️ Ada data ujian dari peserta lain yang tersimpan.\n\nLanjutkan dengan data baru? (Data lama akan dihapus)"
        )
      ) {
        return;
      }
      localStorage.removeItem("exam_backup");
      userAnswers = {};
      currentQuestion = 0;
      timeLeft = EXAM_TIME;
    }
  }

  // Mode recovery atau baru
  // const isRecovery =
  //   Object.keys(userAnswers).length > 0 &&
  //   studentData.nik === nik &&
  //   studentData.nama === nama &&
  //   studentData.formasi === formasi;

  const isRecovery =
    Object.keys(userAnswers).length > 0 &&
    studentData.nik === nik &&
    studentData.nama === nama &&
    studentData.formasi1 === formasi1;

  // Set data peserta - SIMPAN KEDUA FORMASI
  studentData = { 
    nama, 
    nik, 
    formasi1, 
    formasi2: formasi2 || "" // Jika kosong, simpan string kosong
  };

  // Set data peserta
  studentData = { nama, nik, formasi1, formasi2 };

  // Jika recovery mode, tampilkan konfirmasi
  // if (isRecovery) {
  //   const answeredCount = Object.keys(userAnswers).length;
  //   const continueExam = confirm(
  //     `📂 DATA UJIAN DITEMUKAN!\n\n` +
  //       `Nama: ${nama}\n` +
  //       `NIK: ${nik}\n\n` +
  //       `Status: ${answeredCount} soal terjawab\n` +
  //       `Soal terakhir: No. ${currentQuestion + 1}\n\n` +
  //       `Lanjutkan ujian dari posisi terakhir?`
  //   );

  if (isRecovery) {
    const answeredCount = Object.keys(userAnswers).length;
    const continueExam = confirm(
      `📂 DATA UJIAN DITEMUKAN!\n\n` +
        `Nama: ${nama}\n` +
        `NIK: ${nik}\n` +
        `Formasi: ${formasi1} ${formasi2 ? `+ ${formasi2}` : ''}\n\n` +
        `Status: ${answeredCount} soal terjawab\n` +
        `Soal terakhir: No. ${currentQuestion + 1}\n\n` +
        `Lanjutkan ujian dari posisi terakhir?`
    );

    if (!continueExam) {
      // Reset jika tidak ingin lanjut
      userAnswers = {};
      currentQuestion = 0;
      timeLeft = EXAM_TIME;
      localStorage.removeItem("exam_backup");
      sessionStorage.removeItem("exam_session");
    }
  } else {
    // Mode baru
    userAnswers = {};
    currentQuestion = 0;
    timeLeft = EXAM_TIME;
  }

  // Masuk fullscreen
  enterFullscreen();

  // Update UI
  document.getElementById("studentName").textContent = nama;
  document.getElementById("studentNIK").textContent = nik;
  document.getElementById("studentFormasi").textContent = 
    `${formasi1} ${formasi2 ? `+ ${formasi2}` : ''}`;

  // Ganti halaman
  document.getElementById("loginPage").classList.remove("active");
  document.getElementById("examPage").classList.add("active");

  // Set status
  examStarted = true;

  // Mulai timer dan tampilkan soal
  startTimer();
  showQuestion(currentQuestion);
  updateProgress();

  // Mulai auto-save system
  startAutoSave();

  // Aktifkan proteksi anti curang
  enableAntiCheat();

  // Tampilkan welcome message
  showWelcomeMessage(isRecovery);
}

// Masuk fullscreen
function enterFullscreen() {
  const elem = document.documentElement;
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.webkitRequestFullscreen) {
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) {
    elem.msRequestFullscreen();
  }
}

// Tampilkan welcome message
function showWelcomeMessage(isRecovery) {
  const answeredCount = Object.keys(userAnswers).length;
  const message = isRecovery
    ? `Selamat datang kembali! ${answeredCount} soal sudah terjawab.`
    : "Selamat mengerjakan! Anda memiliki 100 menit untuk 110 soal.";

  const welcomeDiv = document.createElement("div");
  welcomeDiv.style.cssText = `
        position: fixed;
        top: 80px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 30px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 9998;
        animation: fadeInOut 3s ease-in-out;
        text-align: center;
        max-width: 80%;
    `;
  welcomeDiv.innerHTML = `
        <i class="fas fa-bell"></i> ${message}
        <div style="margin-top: 5px; font-size: 12px; opacity: 0.9;">
            <i class="fas fa-save"></i> Jawaban otomatis tersimpan setiap 30 detik
        </div>
    `;
  document.body.appendChild(welcomeDiv);

  // Hapus setelah 3 detik
  setTimeout(() => {
    if (welcomeDiv.parentNode) {
      welcomeDiv.remove();
    }
  }, 3000);
}

// Aktifkan proteksi anti curang
function enableAntiCheat() {
  // Blokir inspect element
  document.addEventListener("keydown", blockInspectKeys);

  // Blokir klik kanan
  document.addEventListener("contextmenu", function (e) {
    e.preventDefault();
    showWarning("Klik kanan dinonaktifkan selama ujian.");
  });

  // Deteksi devtools
  startDevToolsDetection();

  // Deteksi keluar fullscreen
  document.addEventListener("fullscreenchange", handleFullscreenChange);
  document.addEventListener("webkitfullscreenchange", handleFullscreenChange);
  document.addEventListener("msfullscreenchange", handleFullscreenChange);
}

// Blokir shortcut inspect
function blockInspectKeys(e) {
  const blockedKeys = ["F12", "F11"];

  const blockedCombos = [
    e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "i",
    e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "j",
    e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "c",
    e.ctrlKey && e.key.toLowerCase() === "u",
    e.ctrlKey && e.key.toLowerCase() === "s",
  ];

  if (blockedKeys.includes(e.key) || blockedCombos.includes(true)) {
    e.preventDefault();
    showWarning("Shortcut ini dinonaktifkan selama ujian.");
    return false;
  }
}

// Tampilkan warning
function showWarning(message) {
  console.warn(message);
}

// Deteksi devtools
function startDevToolsDetection() {
  const threshold = 160;
  let devtoolsOpen = false;

  const checkDevTools = setInterval(() => {
    if (!examStarted) {
      clearInterval(checkDevTools);
      return;
    }

    if (
      window.outerWidth - window.innerWidth > threshold ||
      window.outerHeight - window.innerHeight > threshold
    ) {
      if (!devtoolsOpen) {
        devtoolsOpen = true;
        alert(
          "⚠️ Developer Tools terdeteksi! Silakan tutup untuk melanjutkan ujian."
        );
      }
    } else {
      devtoolsOpen = false;
    }
  }, 1000);
}

// Handle fullscreen change
function handleFullscreenChange() {
  if (
    !document.fullscreenElement &&
    !document.webkitFullscreenElement &&
    !document.msFullscreenElement
  ) {
    if (examStarted) {
      alert(
        "Anda keluar dari mode fullscreen. Harap kembali ke fullscreen untuk melanjutkan."
      );
      enterFullscreen();
    }
  }
}

// Tampilkan soal
function showQuestion(index) {
  if (index < 0 || index >= questions.length) return;

  currentQuestion = index;
  const question = questions[index];

  // Update nomor soal
  document.getElementById("currentQuestionNum").textContent = index + 1;

  // Tampilkan soal
  const questionText = document.getElementById("questionText");

  // ================================
  // TAMPILKAN SOAL
  // ================================
  if (question.type === "image") {
    // Jika soal berupa gambar
    questionText.innerHTML = `<img src="${
      question.questionImage || question.question
    }" class="question-image">`;
  } else if (Array.isArray(question.question)) {
    // Jika soal berupa array (multi-baris)
    questionText.innerHTML = question.question.replace(/\n/g, "<br>");
  } else {
    // Jika soal teks biasa
    questionText.textContent = question.question;
  }

  // ================================
  // TAMPILKAN OPSI (SELALU TEKS)
  // ================================
  const optionsDiv = document.getElementById("options");
  optionsDiv.innerHTML = "";

  Object.entries(question.options).forEach(([key, value]) => {
    const optionDiv = document.createElement("div");
    optionDiv.className = `option ${
      userAnswers[question.id] === key ? "selected" : ""
    }`;

    optionDiv.onclick = () => selectAnswer(question.id, key);

    optionDiv.innerHTML = `
      <div class="option-letter">${key}</div>
      <div class="option-text">${value}</div>
    `;

    optionsDiv.appendChild(optionDiv);
  });

  updateNavigation();
  updateQuestionNumbers();
}

// Pilih jawaban
function selectAnswer(questionId, answer) {
  userAnswers[questionId] = answer;
  showQuestion(currentQuestion);
  updateProgress();

  // Auto-save setelah pilih jawaban
  saveToLocalStorage();
}

// Navigasi soal
function nextQuestion() {
  if (currentQuestion < questions.length - 1) {
    showQuestion(currentQuestion + 1);
  }
}

function prevQuestion() {
  if (currentQuestion > 0) {
    showQuestion(currentQuestion - 1);
  }
}

// Update navigasi tombol
function updateNavigation() {
  document.getElementById("prevBtn").disabled = currentQuestion === 0;
  document.getElementById("nextBtn").disabled =
    currentQuestion === questions.length - 1;
}

// Update nomor soal di sidebar
function updateQuestionNumbers() {
  const container = document.getElementById("questionNumbers");
  if (!container) return;

  container.innerHTML = "";

  questions.forEach((q, index) => {
    const button = document.createElement("button");
    button.className = `number-btn ${
      userAnswers[q.id]
        ? "answered"
        : index === currentQuestion
        ? "current"
        : "unanswered"
    }`;
    button.textContent = index + 1;
    button.onclick = () => showQuestion(index);

    container.appendChild(button);
  });
}

// Update progress bar
function updateProgress() {
  const answered = Object.keys(userAnswers).length;
  const progress = (answered / TOTAL_QUESTIONS) * 100;

  const progressBar = document.getElementById("progressBar");
  const progressText = document.getElementById("progressText");

  if (progressBar) {
    progressBar.style.width = `${progress}%`;
  }
  if (progressText) {
    progressText.textContent = `${answered}/${TOTAL_QUESTIONS}`;
  }
}

// Timer
function startTimer() {
  clearInterval(timerInterval);

  timerInterval = setInterval(() => {
    timeLeft--;

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      submitExam();
      return;
    }

    updateTimerDisplay();
  }, 1000);

  updateTimerDisplay();
}

function updateTimerDisplay() {
  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;
  const timerElement = document.getElementById("countdown");

  if (timerElement) {
    timerElement.textContent = `${minutes.toString().padStart(2, "0")}:${seconds
      .toString()
      .padStart(2, "0")}`;

    // Warna peringatan saat waktu hampir habis
    if (timeLeft < 300) {
      // 5 menit terakhir
      timerElement.style.color = "#ff6b6b";
      timerElement.style.animation =
        timeLeft < 60 ? "pulse 1s infinite" : "none";
    }
  }
}

// ============================================
// FUNGSI SUBMIT & HASIL
// ============================================

// Konfirmasi submit
function showConfirmation() {
  const answered = Object.keys(userAnswers).length;
  const unanswered = TOTAL_QUESTIONS - answered;

  let summaryHTML = `
        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin: 15px 0;">
            <p><strong>Ringkasan Jawaban:</strong></p>
            <p style="color: #4CAF50;">✅ Terjawab: ${answered} soal</p>
            ${
              unanswered > 0
                ? `<p style="color: #ff9800;">⚠️ Belum dijawab: ${unanswered} soal</p>`
                : ""
            }
        </div>
    `;

  if (unanswered > 0) {
    summaryHTML += `<p style="color: #ff6b6b; font-weight: bold;">
            <i class="fas fa-exclamation-triangle"></i>
            Masih ada ${unanswered} soal yang belum dijawab!
        </p>`;
  }

  document.getElementById("summary").innerHTML = summaryHTML;
  document.getElementById("confirmationModal").style.display = "flex";
}

function hideConfirmation() {
  document.getElementById("confirmationModal").style.display = "none";
}

// Hitung skor berdasarkan kategori
// function calculateScore() {
//     let twkScore = 0;
//     let tiuScore = 0;
//     let tkpScore = 0;

//     questions.forEach(q => {
//         const userAnswer = userAnswers[q.id];
//         if (userAnswer) {
//             if (q.category === 'TKP' && q.scoring) {
//                 // TKP: ambil nilai dari scoring table
//                 const points = q.scoring[userAnswer] || 0;
//                 tkpScore += points;
//             } else {
//                 // TWK/TIU: cek jawaban benar
//                 if (userAnswer === q.correctAnswer) {
//                     if (q.category === 'TWK') {
//                         twkScore += q.points || 5;
//                     } else if (q.category === 'TIU') {
//                         tiuScore += q.points || 5;
//                     }
//                 }
//             }
//         }
//     });

//     const totalScore = twkScore + tiuScore + tkpScore;
//     const maxTWKTIU = (CATEGORIES.TWK.total * 5) + (CATEGORIES.TIU.total * 5);
//     const maxTKP = CATEGORIES.TKP.total * 5;
//     const totalMax = maxTWKTIU + maxTKP;
//     const percentage = totalScore > 0 ? (totalScore / totalMax) * 100 : 0;

//     return {
//         nama: studentData.nama,
//         nik: studentData.nik,
//         twk: twkScore,
//         tiu: tiuScore,
//         tkp: tkpScore,
//         total: totalScore,
//         maxTWKTIU: maxTWKTIU,
//         maxTKP: maxTKP,
//         totalMax: totalMax,
//         percentage: percentage,
//         timeSpent: EXAM_TIME - timeLeft,
//         answers: userAnswers
//     };
// }

function calculateScore() {
  let twkScore = 0;
  let tiuScore = 0;
  let tkpScore = 0;

  // Cek jika questions kosong
  if (!questions || questions.length === 0) {
    console.error("Questions array is empty!");
    questions = defaultQuestions;
  }

  questions.forEach((q) => {
    const userAnswer = userAnswers[q.id];

    if (userAnswer) {
      if (q.category === "TKP") {
        // Ambil indeks jawaban user (A=0, B=1, dst.)
        const optionIndex = "ABCDE".indexOf(userAnswer.toUpperCase());
        if (optionIndex !== -1 && q.scoringTable) {
          tkpScore += q.scoringTable[optionIndex]; // Nilai sesuai tabel
        }
      } else if (q.category === "TIU" || q.category === "TWK") {
        const isCorrect = userAnswer === q.correctAnswer;
        if (isCorrect) {
          if (q.category === "TIU") tiuScore += 5;
          else if (q.category === "TWK") twkScore += 5;
        }
      }
    }
  });

  console.log("TKP:", tkpScore, "TIU:", tiuScore, "TWK:", twkScore);
}

  // Hitung maksimal
  const maxTWK = 30 * 5; // 150
  const maxTIU = 35 * 5; // 175
  const maxTKP = 45 * 5; // 225
  const totalScore = twkScore + tiuScore + tkpScore;
  const totalMax = maxTWK + maxTIU + maxTKP; // 550
  const percentage = totalScore > 0 ? (totalScore / totalMax) * 100 : 0;

console.log("SCORE CALCULATION:");
  console.log(`TWK: ${twkScore}/${maxTWK}`);
  console.log(`TIU: ${tiuScore}/${maxTIU}`);
  console.log(`TKP: ${tkpScore}/${maxTKP}`);
  console.log(`TOTAL: ${totalScore}/${totalMax} (${percentage.toFixed(2)}%)`);

  return {
    nama: studentData.nama,
    nik: studentData.nik,
    formasi1: studentData.formasi1,
    formasi2: studentData.formasi2 || "",
    twk: twkScore,
    tiu: tiuScore,
    tkp: tkpScore,
    total: totalScore,
    maxTWK: maxTWK,
    maxTIU: maxTIU,
    maxTKP: maxTKP,
    totalMax: totalMax,
    percentage: percentage,
    timeSpent: EXAM_TIME - timeLeft,
    answers: userAnswers,
  };

// Submit ujian
async function submitExam() {
  clearInterval(timerInterval);
  hideConfirmation();

  // Hentikan auto-save
  stopAutoSave();

  // Keluar dari fullscreen
  exitFullscreen();

  // Hitung skor
  const result = calculateScore();
  result.submitted_at = new Date().toISOString();
  result.timestamp = new Date().toLocaleString();

  // Simpan IP (opsional)
  try {
    const ipResponse = await fetch("https://api.ipify.org?format=json");
    const ipData = await ipResponse.json();
    result.ip = ipData.ip;
  } catch {
    result.ip = "unknown";
  }

  // Simpan ke window untuk akses di showDetail()
  window.lastResult = result;

  // Kirim ke server
  const saved = await saveResult(result);

  if (!saved) {
    // Coba simpan ke localStorage sebagai fallback
    try {
      const backupKey = `exam_result_${result.nik}_${Date.now()}`;
      localStorage.setItem(backupKey, JSON.stringify(result));
      console.log("Disimpan ke localStorage sebagai backup:", backupKey);
    } catch (e) {
      console.error("Gagal menyimpan ke localStorage:", e);
    }

    // Tampilkan pesan
    alert(
      "⚠️ Gagal menyimpan hasil ke server. Data tersimpan di browser.\nSilakan hubungi admin untuk backup manual."
    );
  }

  // Tampilkan hasil
  showResult(result);

  // Hapus semua backup data
  cleanupBackupData();
}

// Keluar fullscreen
function exitFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
}

// Simpan hasil ke server
async function saveResult(result) {
  try {
    const response = await fetch("save_result.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(result),
    });

    if (response.ok) {
      const data = await response.json();
      return data.success || false;
    }
  } catch (error) {
    console.error("Error saving result:", error);
  }

  return false;
}

// Tampilkan hasil (HANYA SKOR)
function showResult(result) {
  // Hapus class active dari semua page
  document.querySelectorAll(".page").forEach((page) => {
    page.classList.remove("active");
  });

  // Tampilkan result page
  document.getElementById("resultPage").classList.add("active");

  // Info peserta
  document.getElementById("resultName").textContent = result.nama;
  document.getElementById("resultNIK").textContent = result.nik;
  document.getElementById("resultFormasi1").textContent = 
    `Formasi 1: ${result.formasi1}`;
  
  // Tampilkan Formasi 2 jika ada
  const formasi2Item = document.getElementById("formasi2Item");
  const resultFormasi2 = document.getElementById("resultFormasi2");

  if (result.formasi2 && result.formasi2.trim() !== "") {
    formasi2Item.style.display = "flex";
    resultFormasi2.textContent = `Formasi 2: ${result.formasi2}`;
  } else {
    formasi2Item.style.display = "none";
  }
  
  document.getElementById("submitTime").textContent = new Date(
    result.submitted_at || result.timestamp
  ).toLocaleString();

  // Format skor
  document.getElementById("totalScoreDisplay").textContent = result.total;
  document.getElementById("maxScoreDisplay").textContent = `/${
    result.totalMax || 550
  }`;
  document.getElementById("percentageDisplay").textContent = `${
    result.percentage ? result.percentage.toFixed(2) : 0
  }%`;

  // Skor per kategori
  // document.getElementById("twkScoreDisplay").textContent =
  //     `${result.twk || 0}/${CATEGORIES.TWK.total * 5}`;
  // document.getElementById("tiuScoreDisplay").textContent =
  //     `${result.tiu || 0}/${CATEGORIES.TIU.total * 5}`;
  // document.getElementById("tkpScoreDisplay").textContent =
  //     `${result.tkp || 0}/${CATEGORIES.TKP.total * 5}`;

  // Skor per kategori - PAKAI SATU VERSI SAJA
  document.getElementById("twkScoreDisplay").textContent = `${
    result.twk || 0
  }/150`;
  document.getElementById("tiuScoreDisplay").textContent = `${
    result.tiu || 0
  }/175`;
  document.getElementById("tkpScoreDisplay").textContent = `${
    result.tkp || 0
  }/225`;
  document.getElementById("maxScoreDisplay").textContent = `/550`;

  // Tampilkan waktu pengerjaan
  const timeSpentElement = document.getElementById("timeSpent");
  if (timeSpentElement && result.timeSpent) {
    const minutes = Math.floor(result.timeSpent / 60);
    const seconds = result.timeSpent % 60;
    timeSpentElement.textContent = `${minutes} menit ${seconds} detik`;
  }
}

// Bersihkan backup data
function cleanupBackupData() {
  // Hapus semua backup
  localStorage.removeItem("exam_backup");
  sessionStorage.removeItem("exam_session");

  // Hapus semua result backup
  Object.keys(localStorage).forEach((key) => {
    if (key.startsWith("exam_result_")) {
      localStorage.removeItem(key);
    }
  });
}

// Restart ujian
function restartExam() {
  // Hentikan semua interval
  clearInterval(timerInterval);
  clearInterval(autoSaveInterval);

  // Reset semua variabel
  userAnswers = {};
  currentQuestion = 0;
  timeLeft = EXAM_TIME;
  examStarted = false;
  studentData = {};

  // Hapus semua backup
  cleanupBackupData();

  // Hapus event listeners
  document.removeEventListener("keydown", blockInspectKeys);
  document.removeEventListener("contextmenu", function (e) {
    e.preventDefault();
  });

  // Kembali ke halaman login
  document.getElementById("resultPage").classList.remove("active");
  document.getElementById("loginPage").classList.add("active");

  // Reset form
document.getElementById("nama").value = "";
  document.getElementById("nik").value = "";
  document.getElementById("formasi1").value = "";
  document.getElementById("formasi2").value = "";
  document.getElementById("recoveryWarning").style.display = "none";

  // Reset timer display
  updateTimerDisplay();
  updateProgress();
  updateQuestionNumbers();
}

// Detail jawaban
// function showDetail() {
//     const result = window.lastResult || {};
//     const detail = `
//         📊 DETAIL HASIL UJIAN:
//         ===================

//         👤 IDENTITAS:
//         ----------
//         Nama: ${result.nama}
//         NIK: ${result.nik}

//         🎯 SKOR PER KATEGORI:
//         -------------------
//         TWK: ${result.twk || 0} poin dari ${CATEGORIES.TWK.total * 5}
//         TIU: ${result.tiu || 0} poin dari ${CATEGORIES.TIU.total * 5}
//         TKP: ${result.tkp || 0} poin dari ${CATEGORIES.TKP.total * 5}

//         📈 TOTAL: ${result.total || 0} poin dari ${result.totalMax || 290}
//         📊 Persentase: ${result.percentage ? result.percentage.toFixed(2) : 0}%

//         ⏱️ WAKTU:
//         ------
//         Submit: ${new Date(result.submitted_at || result.timestamp).toLocaleString()}
//         ${result.timeSpent ? `Durasi: ${Math.floor(result.timeSpent / 60)} menit ${result.timeSpent % 60} detik` : ''}

//         ${result.ip && result.ip !== 'unknown' ? `🌐 IP Address: ${result.ip}` : ''}

//         💾 Status: ${result.savedToServer ? 'Tersimpan ke server' : 'Tersimpan di browser'}
//     `;

//     alert(detail);
// }

// Detail jawaban - PERBAIKI rumus TKP
function showDetail() {
  const result = window.lastResult || {};
  const maxTWK = 30 * 5; // 150
  const maxTIU = 35 * 5; // 175
  const maxTKP = 45 * 5; // 225
  const totalMax = maxTWK + maxTIU + maxTKP; // 550

  const detail = `
        📊 DETAIL HASIL UJIAN:
        ===================
        
        👤 IDENTITAS:
        ----------
        Nama: ${result.nama}
        NIK: ${result.nik}
        Formasi 1: ${result.formasi1}
        ${result.formasi2 ? `Formasi 2: ${result.formasi2}` : ''}
        
        🎯 SKOR PER KATEGORI:
        -------------------
        TWK: ${result.twk || 0} poin dari ${maxTWK}
        TIU: ${result.tiu || 0} poin dari ${maxTIU}
        TKP: ${result.tkp || 0} poin dari ${maxTKP}
        
        📈 TOTAL: ${result.total || 0} poin dari ${totalMax}
        📊 Persentase: ${result.percentage ? result.percentage.toFixed(2) : 0}%
        
        ⏱️ WAKTU:
        ------
        Submit: ${new Date(
          result.submitted_at || result.timestamp
        ).toLocaleString()}
        ${
          result.timeSpent
            ? `Durasi: ${Math.floor(result.timeSpent / 60)} menit ${
                result.timeSpent % 60
              } detik`
            : ""
        }
        
        ${
          result.ip && result.ip !== "unknown"
            ? `🌐 IP Address: ${result.ip}`
            : ""
        }
    `;

  alert(detail);
}

// ============================================
// INISIALISASI
// ============================================

// Tambah CSS animation untuk save indicator
const style = document.createElement("style");
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        20% { opacity: 1; transform: translateX(-50%) translateY(0); }
        80% { opacity: 1; transform: translateX(-50%) translateY(0); }
        100% { opacity: 0; transform: translateX(-50%) translateY(-20px); }
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .recovery-warning {
        display: none;
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
        align-items: center;
        gap: 10px;
    }
    
    .recovery-warning i {
        color: #ffc107;
        font-size: 20px;
    }
`;
document.head.appendChild(style);

// Inisialisasi saat halaman dimuat
document.addEventListener("DOMContentLoaded", init);
