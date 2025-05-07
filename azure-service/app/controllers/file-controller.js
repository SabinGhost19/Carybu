// controllers/file-controller.js
const storageService = require("../services/storage-service");
const textExtractionService = require("../services/text-extraction-service");
const sentimentService = require("../services/sentiment-service");
const dbService = require("../services/db-service");

async function uploadAndAnalyze(req, res, next) {
  try {
    // Verifică dacă a fost furnizat un fișier
    if (!req.files || !req.files.file) {
      return res
        .status(400)
        .json({ error: "Niciun fișier nu a fost încărcat" });
    }

    const file = req.files.file;
    const language = req.body.language || null;

    // Încarcă fișierul în Blob Storage
    const blobInfo = await storageService.uploadFile(
      file.data,
      file.name,
      file.mimetype
    );

    // Salvează înregistrarea în baza de date
    const fileRecordId = await dbService.addFileRecord({
      fileName: file.name,
      blobUrl: blobInfo.blobUrl,
      contentType: file.mimetype,
      fileSize: file.size,
    });

    // Extrage text din fișier
    const extractedText = await textExtractionService.extractTextFromBuffer(
      file.data,
      file.mimetype,
      file.name
    );

    // Verifică dacă s-a putut extrage text
    if (!extractedText || extractedText.trim().length === 0) {
      throw new Error("Nu s-a putut extrage text din fișier");
    }

    // Analizează sentimentul textului extras
    const sentimentResult = await sentimentService.analyzeSentiment(
      extractedText,
      language
    );

    // Actualizează înregistrarea cu rezultatul analizei
    await dbService.updateSentimentResult(fileRecordId, sentimentResult);

    // Răspunde cu rezultatele
    res.status(200).json({
      id: fileRecordId,
      fileName: file.name,
      blobUrl: blobInfo.blobUrl,
      extractedText:
        extractedText.length > 1000
          ? extractedText.substring(0, 1000) + "..."
          : extractedText,
      textLength: extractedText.length,
      sentimentResult,
    });
  } catch (error) {
    next(error);
  }
}

async function analyzeText(req, res, next) {
  try {
    const { text, language } = req.body;

    // Verifică dacă a fost furnizat text
    if (!text || text.trim().length === 0) {
      return res.status(400).json({ error: "Textul este obligatoriu" });
    }

    // Creează un fișier text cu conținutul introdus
    const fileBuffer = Buffer.from(text, "utf8");
    const fileName = `text_${new Date().getTime()}.txt`;

    // Încarcă fișierul în Blob Storage
    const blobInfo = await storageService.uploadFile(
      fileBuffer,
      fileName,
      "text/plain"
    );

    // Salvează înregistrarea în baza de date
    const fileRecordId = await dbService.addFileRecord({
      fileName,
      blobUrl: blobInfo.blobUrl,
      contentType: "text/plain",
      fileSize: fileBuffer.length,
    });

    // Analizează sentimentul textului
    const sentimentResult = await sentimentService.analyzeSentiment(
      text,
      language
    );

    // Actualizează înregistrarea cu rezultatul analizei
    await dbService.updateSentimentResult(fileRecordId, sentimentResult);

    // Răspunde cu rezultatele
    res.status(200).json({
      id: fileRecordId,
      fileName,
      blobUrl: blobInfo.blobUrl,
      extractedText:
        text.length > 1000 ? text.substring(0, 1000) + "..." : text,
      textLength: text.length,
      sentimentResult,
    });
  } catch (error) {
    next(error);
  }
}

/**
 * Descarcă un fișier din Blob Storage
 */
async function downloadFile(req, res, next) {
  try {
    const { id } = req.params;

    // Obține detaliile fișierului din baza de date
    const fileDetails = await dbService.getHistoryDetail(id);

    if (!fileDetails) {
      return res.status(404).json({ error: "Fișierul nu a fost găsit" });
    }

    // Descarcă fișierul din Blob Storage
    const fileBuffer = await storageService.downloadFile(fileDetails.blobUrl);

    // Setează header-urile pentru descărcare
    res.setHeader("Content-Type", fileDetails.contentType);
    res.setHeader(
      "Content-Disposition",
      `attachment; filename=${fileDetails.fileName}`
    );

    // Trimite fișierul
    res.send(fileBuffer);
  } catch (error) {
    next(error);
  }
}

/**
 * Șterge un fișier și înregistrarea sa
 */
async function deleteFile(req, res, next) {
  try {
    const { id } = req.params;

    // Obține detaliile fișierului din baza de date
    const fileDetails = await dbService.getHistoryDetail(id);

    if (!fileDetails) {
      return res.status(404).json({ error: "Fișierul nu a fost găsit" });
    }

    // Șterge fișierul din Blob Storage
    await storageService.deleteFile(fileDetails.blobUrl);

    // Șterge înregistrarea din baza de date
    await dbService.deleteFileRecord(id);

    res.status(200).json({ message: "Fișierul a fost șters cu succes" });
  } catch (error) {
    next(error);
  }
}

module.exports = {
  uploadAndAnalyze,
  analyzeText,
  downloadFile,
  deleteFile,
};
