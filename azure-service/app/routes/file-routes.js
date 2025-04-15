// routes/file-routes.js
const express = require("express");
const fileController = require("../controllers/file-controller");

const router = express.Router();

// Route pentru încărcarea și analiza unui fișier
router.post("/upload", fileController.uploadAndAnalyze);

// Route pentru analiza unui text direct (fără fișier)
router.post("/analyze-text", fileController.analyzeText);

// Route pentru descărcarea unui fișier
router.get("/download/:id", fileController.downloadFile);

// Route pentru ștergerea unui fișier
router.delete("/:id", fileController.deleteFile);

module.exports = router;
