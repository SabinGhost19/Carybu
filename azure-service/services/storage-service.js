// services/storage-service.js
const { BlobServiceClient } = require("@azure/storage-blob");
const config = require("../config/config");
const crypto = require("crypto");

// Creează client pentru Blob Storage
const blobServiceClient = BlobServiceClient.fromConnectionString(
  config.azure.storage.connectionString
);

// Referință către container
const containerClient = blobServiceClient.getContainerClient(
  config.azure.storage.containerName
);

/**
 * Asigură că containerul există
 */
async function ensureContainer() {
  try {
    await containerClient.createIfNotExists({
      access: "blob",
    });
    console.log(
      `Container "${config.azure.storage.containerName}" created or already exists`
    );
  } catch (error) {
    console.error("Error creating container:", error);
    throw error;
  }
}

/**
 * Generează un nume de blob unic pentru fișier
 */
function generateUniqueBlobName(fileName) {
  const timestamp = new Date().getTime();
  const randomString = crypto.randomBytes(8).toString("hex");
  const extension = fileName.split(".").pop();
  return `${timestamp}-${randomString}.${extension}`;
}

/**
 * Încarcă un fișier în Azure Blob Storage
 */
async function uploadFile(fileBuffer, fileName, contentType) {
  try {
    // Asigură-te că containerul există
    await ensureContainer();

    // Generează un nume unic pentru blob
    const blobName = generateUniqueBlobName(fileName);

    // Obține un client pentru blob
    const blockBlobClient = containerClient.getBlockBlobClient(blobName);

    // Încarcă fișierul
    await blockBlobClient.upload(fileBuffer, fileBuffer.length, {
      blobHTTPHeaders: {
        blobContentType: contentType,
      },
    });

    // Returnează URL-ul și alte informații despre blob
    return {
      blobUrl: blockBlobClient.url,
      blobName: blobName,
      contentType: contentType,
      fileSize: fileBuffer.length,
    };
  } catch (error) {
    console.error("Error uploading file to Blob Storage:", error);
    throw error;
  }
}

/**
 * Descarcă un fișier din Azure Blob Storage
 */
async function downloadFile(blobUrl) {
  try {
    // Extrage numele blob-ului din URL
    const blobName = blobUrl.split("/").pop();

    // Obține clientul pentru blob
    const blockBlobClient = containerClient.getBlockBlobClient(blobName);

    // Descarcă blob-ul
    const downloadResponse = await blockBlobClient.download(0);

    // Convertește stream-ul în buffer
    let downloadedData = Buffer.from([]);
    for await (const chunk of downloadResponse.readableStreamBody) {
      downloadedData = Buffer.concat([downloadedData, chunk]);
    }

    return downloadedData;
  } catch (error) {
    console.error("Error downloading file from Blob Storage:", error);
    throw error;
  }
}

/**
 * Șterge un fișier din Azure Blob Storage
 */
async function deleteFile(blobUrl) {
  try {
    // Extrage numele blob-ului din URL
    const blobName = blobUrl.split("/").pop();

    // Obține clientul pentru blob
    const blockBlobClient = containerClient.getBlockBlobClient(blobName);

    // Șterge blob-ul
    await blockBlobClient.delete();

    return true;
  } catch (error) {
    console.error("Error deleting file from Blob Storage:", error);
    throw error;
  }
}

// Asigură-te că containerul există la pornirea aplicației
ensureContainer().catch(console.error);

module.exports = {
  uploadFile,
  downloadFile,
  deleteFile,
};
