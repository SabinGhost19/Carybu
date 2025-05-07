// controllers/sentiment-controller.js
const sentimentService = require("../services/sentiment-service");

async function analyzeSentiment(req, res, next) {
  try {
    const { text, language } = req.body;

    if (!text || text.trim().length === 0) {
      return res.status(400).json({ error: "Textul este obligatoriu" });
    }

    const result = await sentimentService.analyzeSentiment(text, language);

    res.json(result);
  } catch (error) {
    next(error);
  }
}

async function analyzeSentimentBatch(req, res, next) {
  try {
    const { documents } = req.body;

    if (!documents || !Array.isArray(documents)) {
      return res.status(400).json({
        error: "Documentele sunt obligatorii și trebuie să fie un array",
      });
    }

    const results = [];

    // Procesăm fiecare document individual
    for (let i = 0; i < documents.length; i++) {
      const doc = documents[i];

      if (!doc.text || doc.text.trim().length === 0) {
        results.push({
          id: doc.id || `doc${i}`,
          error: "Textul este obligatoriu",
        });
        continue;
      }

      try {
        const result = await sentimentService.analyzeSentiment(
          doc.text,
          doc.language || null
        );
        results.push({
          id: doc.id || `doc${i}`,
          ...result,
        });
      } catch (error) {
        results.push({
          id: doc.id || `doc${i}`,
          error: error.message,
        });
      }
    }

    res.json(results);
  } catch (error) {
    next(error);
  }
}

module.exports = {
  analyzeSentiment,
  analyzeSentimentBatch,
};
