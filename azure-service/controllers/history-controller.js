// controllers/history-controller.js
const dbService = require("../services/db-service");

/**
 * Obține istoricul analizelor
 */
async function getHistory(req, res, next) {
  try {
    const limit = parseInt(req.query.limit) || 100;
    const offset = parseInt(req.query.offset) || 0;

    const history = await dbService.getHistory(limit, offset);

    res.json(history);
  } catch (error) {
    next(error);
  }
}

/**
 * Obține detaliile unei analize specifice
 */
async function getHistoryDetail(req, res, next) {
  try {
    const { id } = req.params;

    const detail = await dbService.getHistoryDetail(id);

    if (!detail) {
      return res.status(404).json({ error: "Înregistrarea nu a fost găsită" });
    }

    res.json(detail);
  } catch (error) {
    next(error);
  }
}

module.exports = {
  getHistory,
  getHistoryDetail,
};
