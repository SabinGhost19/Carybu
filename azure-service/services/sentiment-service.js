// services/sentiment-service.js
const {
  TextAnalyticsClient,
  AzureKeyCredential,
} = require("@azure/ai-text-analytics");
const config = require("../config/config");

// Creare client pentru Text Analytics
const textAnalyticsClient = new TextAnalyticsClient(
  config.azure.textAnalytics.endpoint,
  new AzureKeyCredential(config.azure.textAnalytics.apiKey)
);

/**
 * Analizează sentimentul unui text
 */
async function analyzeSentiment(text, language = null) {
  try {
    // Limitarea textului pentru a respecta limitele API-ului (5120 caractere)

    const documents = splitTextIntoChunks(text).map((chunk, index) => ({
      id: index.toString(), // Add this line to provide a unique ID for each document
      text: chunk,
      language: language || undefined,
    }));

    // Analizăm sentimentul pentru fiecare fragment
    const results = [];

    for (let i = 0; i < documents.length; i += 10) {
      // Maxim 10 documente per cerere
      const batch = documents.slice(i, i + 10);
      const batchResults = await textAnalyticsClient.analyzeSentiment(batch);
      results.push(...batchResults);
    }

    // Combinăm rezultatele pentru a obține un rezultat general
    return aggregateResults(results, text);
  } catch (error) {
    console.error("Error analyzing sentiment:", error);
    throw error;
  }
}

/**
 * Împarte textul în fragmente mai mici pentru a respecta limitele API-ului
 */
function splitTextIntoChunks(text, maxChunkSize = 5000) {
  const chunks = [];
  let currentChunk = "";

  // Împarte textul în propoziții
  const sentences = text.split(/(?<=[.!?])\s+/);

  for (const sentence of sentences) {
    // Dacă adăugarea propoziției curente ar depăși limita, adaugă fragmentul curent și începe unul nou
    if (currentChunk.length + sentence.length > maxChunkSize) {
      if (currentChunk.length > 0) {
        chunks.push(currentChunk);
        currentChunk = "";
      }

      // Dacă propoziția este mai mare decât dimensiunea maximă a unui fragment, împarte-o
      if (sentence.length > maxChunkSize) {
        let remainingSentence = sentence;
        while (remainingSentence.length > 0) {
          chunks.push(remainingSentence.slice(0, maxChunkSize));
          remainingSentence = remainingSentence.slice(maxChunkSize);
        }
      } else {
        currentChunk = sentence;
      }
    } else {
      currentChunk += (currentChunk.length > 0 ? " " : "") + sentence;
    }
  }

  // Adaugă ultimul fragment dacă există
  if (currentChunk.length > 0) {
    chunks.push(currentChunk);
  }

  return chunks;
}

/**
 * Agregă rezultatele pentru mai multe fragmente într-un singur rezultat
 */
function aggregateResults(results, originalText) {
  // Inițializăm scorurile generale
  let totalPositive = 0;
  let totalNegative = 0;
  let totalNeutral = 0;
  let totalMixed = 0;

  // Inițializăm array-ul pentru toate propozițiile
  const allSentences = [];

  // Parcurgem toate rezultatele și agregăm informațiile
  results.forEach((result) => {
    if (result.error) {
      console.error("Error in sentiment analysis result:", result.error);
      return;
    }

    // Adăugăm scorul ponderat pentru document
    const documentWeight =
      result.sentences.reduce(
        (sum, sentence) => sum + sentence.text.length,
        0
      ) / originalText.length;
    totalPositive += result.confidenceScores.positive * documentWeight;
    totalNegative += result.confidenceScores.negative * documentWeight;
    totalNeutral += result.confidenceScores.neutral * documentWeight;

    // Adăugăm toate propozițiile
    allSentences.push(...result.sentences);
  });

  // Determinăm sentimentul general pe baza scorurilor
  let overallSentiment;
  if (totalPositive > totalNegative && totalPositive > totalNeutral) {
    overallSentiment = "positive";
  } else if (totalNegative > totalPositive && totalNegative > totalNeutral) {
    overallSentiment = "negative";
  } else if (totalNeutral > totalPositive && totalNeutral > totalNegative) {
    overallSentiment = "neutral";
  } else {
    overallSentiment = "mixed";
  }

  // Construim și returnăm rezultatul agregat
  return {
    documentSentiment: overallSentiment,
    confidenceScores: {
      positive: totalPositive,
      negative: totalNegative,
      neutral: totalNeutral,
    },
    sentences: allSentences,
  };
}

module.exports = {
  analyzeSentiment,
};
