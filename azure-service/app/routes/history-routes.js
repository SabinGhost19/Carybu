// routes/history-routes.js
const express = require("express");
const historyController = require("../controllers/history-controller");

const router = express.Router();

// Route pentru obținerea istoricului
router.get("/", historyController.getHistory);

// Route pentru obținerea detaliilor unei analize
router.get("/:id", historyController.getHistoryDetail);

module.exports = router;
