// server.js
require("dotenv").config();
const express = require("express");
const bodyParser = require("body-parser");
const cors = require("cors");
const {
  TextAnalyticsClient,
  AzureKeyCredential,
} = require("@azure/ai-text-analytics");

const app = express();
const port = process.env.PORT || 3000;

app.use(cors());
app.use(bodyParser.json());

const textAnalyticsClient = new TextAnalyticsClient(
  process.env.AZURE_TEXT_ANALYTICS_ENDPOINT,
  new AzureKeyCredential(process.env.AZURE_TEXT_ANALYTICS_API_KEY)
);

app.post("/api/analyze-sentiment", async (req, res) => {
  try {
    const { text, language } = req.body;

    if (!text) {
      return res.status(400).json({ error: "Textul este obligatoriu" });
    }

    let sentimentResult;

    if (language === "ro") {
      // for romanian language, we use the default language
      sentimentResult = await textAnalyticsClient.analyzeSentiment([text]);
    } else if (language) {
      // for another language
      try {
        sentimentResult = await textAnalyticsClient.analyzeSentiment([text]);
      } catch (error) {
        console.error(
          "Error with specified language, falling back to default:",
          error
        );
        sentimentResult = await textAnalyticsClient.analyzeSentiment([text]);
      }
    } else {
      // Dacă nu e specificată limba, folosim autodetectarea
      sentimentResult = await textAnalyticsClient.analyzeSentiment([text]);
    }

    const documentResult = sentimentResult[0];

    // Verificare pentru erori
    if (documentResult.error) {
      return res.status(400).json({ error: documentResult.error.message });
    }

    // Formatarea rezultatului
    const result = {
      documentSentiment: documentResult.sentiment,
      confidenceScores: documentResult.confidenceScores,
      sentences: documentResult.sentences.map((sentence) => ({
        text: sentence.text,
        sentiment: sentence.sentiment,
        confidenceScores: sentence.confidenceScores,
      })),
    };

    res.json(result);
  } catch (error) {
    console.error("Error analyzing sentiment:", error);
    res.status(500).json({
      error: "Eroare la procesarea cererii",
      details: error.message,
    });
  }
});

// Ruta pentru analiza sentimentelor în batch
app.post("/api/analyze-sentiment-batch", async (req, res) => {
  try {
    const { documents } = req.body;

    if (!documents || !Array.isArray(documents)) {
      return res.status(400).json({
        error: "Documentele sunt obligatorii și trebuie să fie un array",
      });
    }

    // Extragem doar textele pentru analiză
    const textsToAnalyze = documents.map((doc) => doc.text);

    // Analizăm sentimentul FĂRĂ a trece parametrul language
    const sentimentResults = await textAnalyticsClient.analyzeSentiment(
      textsToAnalyze
    );

    // Formatarea rezultatului
    const results = sentimentResults.map((result, index) => {
      if (result.error) {
        return {
          id: documents[index].id || `doc${index}`,
          error: result.error.message,
        };
      }

      return {
        id: documents[index].id || `doc${index}`,
        documentSentiment: result.sentiment,
        confidenceScores: result.confidenceScores,
        sentences: result.sentences.map((sentence) => ({
          text: sentence.text,
          sentiment: sentence.sentiment,
          confidenceScores: sentence.confidenceScores,
        })),
      };
    });

    res.json(results);
  } catch (error) {
    console.error("Error analyzing sentiment batch:", error);
    res.status(500).json({
      error: "Eroare la procesarea cererii de batch",
      details: error.message,
    });
  }
});

// Pornire server
app.listen(port, () => {
  console.log(`Server running on port ${port}`);
});
