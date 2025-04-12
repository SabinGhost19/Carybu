// script.js

// Configurare API
const API_BASE_URL = "http://localhost:3000/api";

// Elemente DOM
const tabButtons = document.querySelectorAll(".tab-btn");
const tabContents = document.querySelectorAll(".tab-content");
const dropArea = document.getElementById("drop-area");
const fileInput = document.getElementById("file-input");
const uploadBtn = document.getElementById("upload-btn");
const fileLanguage = document.getElementById("file-language");
const inputText = document.getElementById("input-text");
const textLanguage = document.getElementById("text-language");
const analyzeTextBtn = document.getElementById("analyze-text-btn");
const historyList = document.getElementById("history-list");
const resultsContainer = document.getElementById("results-container");
const resultsContent = document.getElementById("results-content");
const loadingOverlay = document.getElementById("loading-overlay");

// Variabile globale
let selectedFile = null;
let historyData = [];

// Gestionare taburi
tabButtons.forEach((button) => {
  button.addEventListener("click", () => {
    // Eliminăm clasa active de pe toate butoanele și conținutul taburilor
    tabButtons.forEach((btn) => btn.classList.remove("active"));
    tabContents.forEach((content) => content.classList.remove("active"));

    // Adăugăm clasa active pe butonul curent și conținutul corespunzător
    button.classList.add("active");
    document
      .getElementById(`${button.dataset.tab}-tab`)
      .classList.add("active");

    // Dacă am selectat tab-ul istorie, încărcăm istoricul
    if (button.dataset.tab === "history") {
      loadHistory();
    }
  });
});

// Gestionare drag and drop
["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
  dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
  e.preventDefault();
  e.stopPropagation();
}

["dragenter", "dragover"].forEach((eventName) => {
  dropArea.addEventListener(
    eventName,
    () => {
      dropArea.classList.add("drag-over");
    },
    false
  );
});

["dragleave", "drop"].forEach((eventName) => {
  dropArea.addEventListener(
    eventName,
    () => {
      dropArea.classList.remove("drag-over");
    },
    false
  );
});

dropArea.addEventListener("drop", handleDrop, false);

function handleDrop(e) {
  const dt = e.dataTransfer;
  const files = dt.files;

  if (files.length > 0) {
    handleFiles(files);
  }
}

// Gestionare selecție fișier
fileInput.addEventListener("change", function () {
  if (this.files.length > 0) {
    handleFiles(this.files);
  }
});

function handleFiles(files) {
  selectedFile = files[0];
  const fileName = selectedFile.name;

  // Afișăm numele fișierului în zona de drop
  dropArea.innerHTML = `
        <i class="fas fa-file-alt"></i>
        <p class="file-name">${fileName}</p>
        <p>Fișier selectat</p>
        <label for="file-input" class="file-label">Schimbă fișierul</label>
    `;

  // Activăm butonul de upload
  uploadBtn.disabled = false;
}

// Gestionare încărcare fișier
uploadBtn.addEventListener("click", uploadFile);

async function uploadFile() {
  if (!selectedFile) return;

  showLoading();

  try {
    const formData = new FormData();
    formData.append("file", selectedFile);

    const language = fileLanguage.value;
    if (language) {
      formData.append("language", language);
    }

    const response = await fetch(`${API_BASE_URL}/files/upload`, {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || "Eroare la încărcarea fișierului");
    }

    const result = await response.json();
    displayResults(result);

    // Resetăm zona de drop
    dropArea.innerHTML = `
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Trageți fișierul aici sau</p>
            <label for="file-input" class="file-label">Selectați un fișier</label>
            <input type="file" id="file-input" accept=".txt,.docx,.pdf,.rtf">
            <p class="file-types">Formate acceptate: .txt, .docx, .pdf, .rtf</p>
        `;

    // Dezactivăm butonul de upload
    uploadBtn.disabled = true;

    // Resetăm selecția de fișier
    selectedFile = null;
    fileInput.value = "";
  } catch (error) {
    showError(error.message);
  } finally {
    hideLoading();
  }
}

// Gestionare analiză text
analyzeTextBtn.addEventListener("click", analyzeText);

async function analyzeText() {
  const text = inputText.value.trim();

  if (!text) {
    showError("Introduceți textul pentru analiză");
    return;
  }

  showLoading();

  try {
    const language = textLanguage.value;

    const response = await fetch(`${API_BASE_URL}/files/analyze-text`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        text,
        language: language || undefined,
      }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.error || "Eroare la analiza textului");
    }

    const result = await response.json();
    displayResults(result);

    // Nu resetăm textul introdus pentru a permite utilizatorului să facă modificări
  } catch (error) {
    showError(error.message);
  } finally {
    hideLoading();
  }
}

// Încărcare istoric
async function loadHistory() {
  showLoading();

  try {
    const response = await fetch(`${API_BASE_URL}/history`);

    if (!response.ok) {
      throw new Error("Eroare la încărcarea istoricului");
    }

    historyData = await response.json();

    if (historyData.length === 0) {
      historyList.innerHTML =
        '<p class="empty-history">Nicio analiză în istoric</p>';
      return;
    }

    historyList.innerHTML = "";

    historyData.forEach((item) => {
      const date = new Date(item.uploadedAt);
      const formattedDate = `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;

      const historyItem = document.createElement("div");
      historyItem.className = "history-item";
      historyItem.dataset.id = item.id;

      // Determinăm iconița în funcție de tipul de fișier
      let fileIcon = "fa-file-alt";
      if (item.contentType.includes("pdf")) {
        fileIcon = "fa-file-pdf";
      } else if (item.contentType.includes("word")) {
        fileIcon = "fa-file-word";
      } else if (item.contentType.includes("text")) {
        fileIcon = "fa-file-text";
      }

      historyItem.innerHTML = `
                <div class="history-file-info">
                    <i class="fas ${fileIcon}"></i>
                    <div>
                        <div class="file-name">${item.fileName}</div>
                        <div class="history-date">${formattedDate}</div>
                    </div>
                </div>
                <div class="sentiment-badge sentiment-${
                  item.overallSentiment || "neutral"
                }">${item.overallSentiment || "Neutru"}</div>
            `;

      historyItem.addEventListener("click", () => loadHistoryDetail(item.id));

      historyList.appendChild(historyItem);
    });
  } catch (error) {
    historyList.innerHTML = `<p class="empty-history">Eroare: ${error.message}</p>`;
  } finally {
    hideLoading();
  }
}

// Încărcare detalii istoric
async function loadHistoryDetail(id) {
  showLoading();

  try {
    const response = await fetch(`${API_BASE_URL}/history/${id}`);

    if (!response.ok) {
      throw new Error("Eroare la încărcarea detaliilor");
    }

    const detail = await response.json();
    displayResults(detail);
  } catch (error) {
    showError(error.message);
  } finally {
    hideLoading();
  }
}

// Afișare rezultate
function displayResults(result) {
  resultsContainer.style.display = "block";

  // Determinăm clasa pentru badge-ul de sentiment
  const sentimentClass = result.sentimentResult?.documentSentiment || "neutral";
  const sentimentText =
    (result.sentimentResult?.documentSentiment || "neutru")
      .charAt(0)
      .toUpperCase() +
    (result.sentimentResult?.documentSentiment || "neutru").slice(1);

  // Formatăm scorurile de încredere
  const formatScore = (score) => Math.round(score * 100) + "%";

  // Determinăm iconița în funcție de tipul de fișier
  let fileIcon = "fa-file-alt";
  if (result.fileName) {
    if (
      result.fileName.endsWith(".pdf") ||
      (result.contentType && result.contentType.includes("pdf"))
    ) {
      fileIcon = "fa-file-pdf";
    } else if (
      result.fileName.endsWith(".docx") ||
      result.fileName.endsWith(".doc") ||
      (result.contentType && result.contentType.includes("word"))
    ) {
      fileIcon = "fa-file-word";
    } else if (
      result.fileName.endsWith(".txt") ||
      (result.contentType && result.contentType.includes("text"))
    ) {
      fileIcon = "fa-file-text";
    }
  }

  resultsContent.innerHTML = `
        <div class="result-card">
            <div class="result-header">
                <div class="file-info">
                    <i class="fas ${fileIcon} file-icon"></i>
                    <div class="file-name">${
                      result.fileName || "Text introdus"
                    }</div>
                </div>
                <div class="sentiment-badge sentiment-${sentimentClass}">${sentimentText}</div>
            </div>
            
            <div class="confidence-scores">
                <div class="score-item">
                    <div class="score-label">Pozitiv</div>
                    <div class="score-value" style="color: #2ecc71;">
                        ${formatScore(
                          result.sentimentResult?.confidenceScores?.positive ||
                            0
                        )}
                    </div>
                </div>
                <div class="score-item">
                    <div class="score-label">Neutru</div>
                    <div class="score-value" style="color: #95a5a6;">
                        ${formatScore(
                          result.sentimentResult?.confidenceScores?.neutral || 0
                        )}
                    </div>
                </div>
                <div class="score-item">
                    <div class="score-label">Negativ</div>
                    <div class="score-value" style="color: #e74c3c;">
                        ${formatScore(
                          result.sentimentResult?.confidenceScores?.negative ||
                            0
                        )}
                    </div>
                </div>
            </div>
            
            ${
              result.extractedText
                ? `
                <h3>Text analizat</h3>
                <div class="extracted-text">${result.extractedText}</div>
                ${
                  result.textLength > 1000
                    ? `<div class="text-preview">(Fragment din textul complet de ${result.textLength} caractere)</div>`
                    : ""
                }
            `
                : ""
            }
            
            ${
              result.sentimentResult?.sentences &&
              result.sentimentResult.sentences.length > 0
                ? `
                <h3>Analiza pe propoziții</h3>
                <div class="sentences-analysis">
                    ${result.sentimentResult.sentences
                      .slice(0, 5)
                      .map(
                        (sentence) => `
                        <div class="sentence-item">
                            <div class="sentence-text">"${sentence.text}"</div>
                            <div class="sentence-sentiment sentiment-badge sentiment-${
                              sentence.sentiment
                            }">
                                ${
                                  sentence.sentiment.charAt(0).toUpperCase() +
                                  sentence.sentiment.slice(1)
                                }
                            </div>
                        </div>
                    `
                      )
                      .join("")}
                    ${
                      result.sentimentResult.sentences.length > 5
                        ? `<div class="text-preview">(Sunt afișate primele 5 din ${result.sentimentResult.sentences.length} propoziții)</div>`
                        : ""
                    }
                </div>
            `
                : ""
            }
            
            ${
              result.blobUrl
                ? `
                <div class="download-section">
                    <a href="${API_BASE_URL}/files/download/${result.id}" class="action-btn" download>
                        <i class="fas fa-download"></i> Descarcă fișierul
                    </a>
                </div>
            `
                : ""
            }
        </div>
    `;

  // Scroll către rezultate
  resultsContainer.scrollIntoView({ behavior: "smooth" });
}

// Utilități
function showLoading() {
  loadingOverlay.style.display = "flex";
}

function hideLoading() {
  loadingOverlay.style.display = "none";
}

function showError(message) {
  resultsContainer.style.display = "block";
  resultsContent.innerHTML = `
        <div class="result-card" style="border-left: 4px solid #e74c3c; padding-left: 1.5rem;">
            <h3 style="color: #e74c3c;">Eroare</h3>
            <p>${message}</p>
        </div>
    `;

  // Scroll către rezultate
  resultsContainer.scrollIntoView({ behavior: "smooth" });
}
