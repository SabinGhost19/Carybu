// routes/sentiment-routes.js
const express = require("express");
const sentimentController = require("../controllers/sentiment-controller");

const router = express.Router();

// Route pentru analiza sentimentelor unui text
router.post("/analyze", sentimentController.analyzeSentiment);

// Route pentru analiza sentimentelor în batch
router.post("/analyze-batch", sentimentController.analyzeSentimentBatch);

module.exports = router;
