// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
// ***********************************************************

import './commands';

// Ignore uncaught exceptions from third-party scripts (e.g. Chart.js CDN, bootstrap)
Cypress.on('uncaught:exception', (err, runnable) => {
  return false;
});
