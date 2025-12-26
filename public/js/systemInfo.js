/*
 * This file is part of System Information Bundle for Contao Open Source CMS.
 *
 * (c) eikona-media.de
 * (c) bwein.net
 *
 * @license MIT
 */

(() => {
  // Configuration
  const URL = '/contao/system_information/system_load';
  const CONTAINER_ID = 'system_info_load_graph_inner';
  const TEXT_IDS = {
    last1: 'system_load_last_1_minute',
    last5: 'system_load_last_5_minutes',
    last15: 'system_load_last_15_minutes'
  };
  const MAX_HEIGHT = 80;

  // State
  let counter = 0;
  let intervalMs = 2000;
  let timeoutId = null;
  let running = false;

  // Schedule next poll (clears previous timeout)
  const scheduleNext = () => {
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
    timeoutId = setTimeout(updateSystemLoad, intervalMs);
  };

  // Safely update text content if element exists
  const setTextIfExists = (id, text) => {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  };

  // Main update function
  async function updateSystemLoad() {
    try {
      const resp = await fetch(URL, { cache: 'no-store' });
      if (!resp.ok) {
        console.warn('System load request failed with status', resp.status);
        scheduleNext();
        return;
      }

      const loadResponse = await resp.json();

      // Parse values defensively
      const now = Number(loadResponse?.now) || 0;
      const factor = Number(loadResponse?.factor) || 1;
      const five = loadResponse?.['5min'] ?? '';
      const fifteen = loadResponse?.['15min'] ?? '';

      // Update textual readouts (if present)
      setTextIfExists(TEXT_IDS.last1, String(now));
      setTextIfExists(TEXT_IDS.last5, String(five));
      setTextIfExists(TEXT_IDS.last15, String(fifteen));

      // Find container and bail out gracefully if missing
      const container = document.getElementById(CONTAINER_ID);
      if (!container) {
        console.warn(`#${CONTAINER_ID} not found — will retry after ${intervalMs}ms`);
        scheduleNext();
        return;
      }

      // Increment counter only when we actually render a bar
      counter++;
      // Adjust polling frequency based on counter
      if (counter > 300) {
        intervalMs = 5000;
      } else if (counter > 150) {
        intervalMs = 3000;
      }

      // Compute height and clamp into range
      let height = Math.floor((now / factor) * MAX_HEIGHT);
      height = Math.max(0, Math.min(MAX_HEIGHT, height));
      const marginTop = MAX_HEIGHT - height;

      // Create bar element using DOM methods (faster and safer than innerHTML)
      const bar = document.createElement('div');
      bar.id = `load_bar_${counter}`;
      bar.className = 'load_bar';
      // Set style properties directly
      bar.style.height = `${height}px`;
      bar.style.marginTop = `${marginTop}px`;

      container.appendChild(bar);

      // Remove oldest bar when exceeding 300 bars (if present)
      if (counter > 300) {
        const oldBar = document.getElementById(`load_bar_${counter - 300}`);
        if (oldBar) oldBar.remove();
      }
    } catch (err) {
      console.error('Error updating system load:', err);
    } finally {
      // Always schedule the next run
      scheduleNext();
    }
  }

  // Start/stop helpers
  const start = () => {
    if (running) return;
    running = true;
    // Immediate first run
    updateSystemLoad();
  };

  const stop = () => {
    running = false;
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  };

  // Start when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }

  // Expose stop for debugging/cleanup if needed
  window.systemLoadMonitor = { stop };
})();