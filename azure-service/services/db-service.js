// services/db-service.js
const sql = require("mssql");
const config = require("../config/config");

// SQL connection pool
let pool = null;

/**
 * Inițializează conexiunea la baza de date
 */
async function initializePool() {
  try {
    pool = await new sql.ConnectionPool(config.azure.database).connect();
    console.log("Database connection established");

    // Setup tables if not exists
    await setupDatabase();
  } catch (error) {
    console.error("Error initializing database connection:", error);
    throw error;
  }
}

/**
 * Configurează tabelele necesare în baza de date dacă acestea nu există
 */
async function setupDatabase() {
  try {
    await pool.request().query(`
      IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'FileHistory')
      BEGIN
        CREATE TABLE FileHistory (
          id INT IDENTITY(1,1) PRIMARY KEY,
          fileName NVARCHAR(255) NOT NULL,
          blobUrl NVARCHAR(512) NOT NULL,
          contentType NVARCHAR(100),
          fileSize BIGINT,
          uploadedAt DATETIME DEFAULT GETDATE(),
          processedAt DATETIME,
          sentimentResult NVARCHAR(MAX),
          overallSentiment NVARCHAR(50),
          confidencePositive FLOAT,
          confidenceNegative FLOAT,
          confidenceNeutral FLOAT
        )
      END
    `);
    console.log("Database setup completed");
  } catch (error) {
    console.error("Error setting up database:", error);
    throw error;
  }
}

/**
 * Adaugă o înregistrare nouă în istoricul fișierelor
 */
async function addFileRecord(fileData) {
  try {
    if (!pool) {
      await initializePool();
    }

    const result = await pool
      .request()
      .input("fileName", sql.NVarChar, fileData.fileName)
      .input("blobUrl", sql.NVarChar, fileData.blobUrl)
      .input("contentType", sql.NVarChar, fileData.contentType)
      .input("fileSize", sql.BigInt, fileData.fileSize).query(`
        INSERT INTO FileHistory (fileName, blobUrl, contentType, fileSize)
        OUTPUT INSERTED.id
        VALUES (@fileName, @blobUrl, @contentType, @fileSize)
      `);

    return result.recordset[0].id;
  } catch (error) {
    console.error("Error adding file record:", error);
    throw error;
  }
}

/**
 * Actualizează înregistrarea cu rezultatele analizei de sentiment
 */
async function updateSentimentResult(id, sentimentData) {
  try {
    if (!pool) {
      await initializePool();
    }

    await pool
      .request()
      .input("id", sql.Int, id)
      .input(
        "sentimentResult",
        sql.NVarChar(sql.MAX),
        JSON.stringify(sentimentData)
      )
      .input("overallSentiment", sql.NVarChar, sentimentData.documentSentiment)
      .input(
        "confidencePositive",
        sql.Float,
        sentimentData.confidenceScores.positive
      )
      .input(
        "confidenceNegative",
        sql.Float,
        sentimentData.confidenceScores.negative
      )
      .input(
        "confidenceNeutral",
        sql.Float,
        sentimentData.confidenceScores.neutral
      ).query(`
        UPDATE FileHistory
        SET sentimentResult = @sentimentResult,
            overallSentiment = @overallSentiment,
            confidencePositive = @confidencePositive,
            confidenceNegative = @confidenceNegative,
            confidenceNeutral = @confidenceNeutral,
            processedAt = GETDATE()
        WHERE id = @id
      `);
  } catch (error) {
    console.error("Error updating sentiment result:", error);
    throw error;
  }
}

/**
 * Obține istoricul analizelor
 */
async function getHistory(limit = 100, offset = 0) {
  try {
    if (!pool) {
      await initializePool();
    }

    const result = await pool
      .request()
      .input("limit", sql.Int, limit)
      .input("offset", sql.Int, offset).query(`
        SELECT id, fileName, blobUrl, contentType, fileSize, 
               uploadedAt, processedAt, overallSentiment,
               confidencePositive, confidenceNegative, confidenceNeutral
        FROM FileHistory
        ORDER BY uploadedAt DESC
        OFFSET @offset ROWS
        FETCH NEXT @limit ROWS ONLY
      `);

    return result.recordset;
  } catch (error) {
    console.error("Error getting history:", error);
    throw error;
  }
}

/**
 * Obține detaliile unei analize specifice
 */
async function getHistoryDetail(id) {
  try {
    if (!pool) {
      await initializePool();
    }

    const result = await pool.request().input("id", sql.Int, id).query(`
        SELECT * FROM FileHistory WHERE id = @id
      `);

    if (result.recordset.length === 0) {
      return null;
    }

    const record = result.recordset[0];

    // Convertește rezultatul sentiment din JSON string în obiect
    if (record.sentimentResult) {
      record.sentimentResult = JSON.parse(record.sentimentResult);
    }

    return record;
  } catch (error) {
    console.error("Error getting history detail:", error);
    throw error;
  }
}

// Inițializează pool-ul la pornirea aplicației
initializePool().catch(console.error);

module.exports = {
  addFileRecord,
  updateSentimentResult,
  getHistory,
  getHistoryDetail,
};
