// services/text-extraction-service.js
const mammoth = require("mammoth");
const pdfParse = require("pdf-parse");
const textract = require("textract");
const iconv = require("iconv-lite");
const chardet = require("chardet");

/**
 * Extrage text din diferite tipuri de fișiere
 */
async function extractTextFromBuffer(buffer, contentType, fileName) {
  try {
    switch (contentType) {
      case "text/plain":
        return extractFromTextFile(buffer);

      case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
      case "application/msword":
        return extractFromDocx(buffer);

      case "application/pdf":
        return extractFromPdf(buffer);

      case "application/rtf":
      case "text/rtf":
        return extractFromRtf(buffer);

      default:
        // Pentru alte tipuri de fișiere, încercăm să extragem text folosind textract
        return new Promise((resolve, reject) => {
          const options = {
            preserveLineBreaks: true,
            encoding: "utf8",
          };

          textract.fromBufferWithName(
            fileName,
            buffer,
            options,
            (error, text) => {
              if (error) {
                reject(
                  new Error(
                    `Nu s-a putut extrage text din fișierul cu formatul ${contentType}: ${error.message}`
                  )
                );
              } else {
                resolve(text);
              }
            }
          );
        });
    }
  } catch (error) {
    console.error("Eroare la extragerea textului:", error);
    throw new Error(
      `Nu s-a putut extrage text din fișierul cu formatul ${contentType}: ${error.message}`
    );
  }
}

/**
 * Extrage text din fișiere text (.txt)
 */
async function extractFromTextFile(buffer) {
  try {
    // Detectează codificarea
    const encoding = chardet.detect(buffer);

    // Convertește buffer-ul în text folosind codificarea detectată
    const text = iconv.decode(buffer, encoding || "utf8");

    return text;
  } catch (error) {
    console.error("Eroare la extragerea textului din fișier text:", error);
    throw error;
  }
}

/**
 * Extrage text din fișiere Word (.docx, .doc)
 */
async function extractFromDocx(buffer) {
  try {
    const result = await mammoth.extractRawText({ buffer });
    return result.value;
  } catch (error) {
    console.error("Eroare la extragerea textului din docx:", error);
    throw error;
  }
}

/**
 * Extrage text din fișiere PDF
 */
async function extractFromPdf(buffer) {
  try {
    const data = await pdfParse(buffer);
    return data.text;
  } catch (error) {
    console.error("Eroare la extragerea textului din PDF:", error);
    throw error;
  }
}

/**
 * Extrage text din fișiere RTF
 */
async function extractFromRtf(buffer) {
  return new Promise((resolve, reject) => {
    textract.fromBufferWithMime(
      "application/rtf",
      buffer,
      { preserveLineBreaks: true },
      (error, text) => {
        if (error) {
          reject(error);
        } else {
          resolve(text);
        }
      }
    );
  });
}

module.exports = {
  extractTextFromBuffer,
};
