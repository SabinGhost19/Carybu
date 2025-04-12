// config/config.js
module.exports = {
  azure: {
    textAnalytics: {
      endpoint: process.env.AZURE_TEXT_ANALYTICS_ENDPOINT,
      apiKey: process.env.AZURE_TEXT_ANALYTICS_API_KEY,
    },
    storage: {
      connectionString: process.env.AZURE_STORAGE_CONNECTION_STRING,
      containerName: process.env.AZURE_STORAGE_CONTAINER_NAME || "documents",
    },
    database: {
      server: process.env.AZURE_SQL_SERVER,
      database: process.env.AZURE_SQL_DATABASE,
      user: process.env.AZURE_SQL_USER,
      password: process.env.AZURE_SQL_PASSWORD,
      options: {
        encrypt: true,
        trustServerCertificate: false,
        enableArithAbort: true,
      },
    },
  },
};
