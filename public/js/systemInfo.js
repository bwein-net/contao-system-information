/*
 * This file is part of System Information Bundle for Contao Open Source CMS.
 *
 * (c) eikona-media.de
 * (c) bwein.net
 *
 * @license MIT
 */

(() => {
  // Config
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

  // Helpers
  const isOnSystemInfo = () => !!document.getElementById(CONTAINER_ID);

  const scheduleNext = () => {
    if (timeoutId) clearTimeout(timeoutId);
    timeoutId = setTimeout(updateSystemLoad, intervalMs);
  };

  const setTextIfExists = (id, text) => {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  };

  const clearBars = () => {
    const container = document.getElementById(CONTAINER_ID);
    if (!container) return;
    while (container.lastChild) container.removeChild(container.lastChild);
  };

  const resetState = () => {
    counter = 0;
    intervalMs = 2000;
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  };

  // Main poller
  async function updateSystemLoad() {
    // stop early if page changed
    if (!isOnSystemInfo()) {
      stop();
      return;
    }

    try {
      const resp = await fetch(URL, { cache: 'no-store' });
      if (!resp.ok) {
        scheduleNext();
        return;
      }

      const loadResponse = await resp.json();

      const now = Number(loadResponse?.now) || 0;
      const factor = Number(loadResponse?.factor) || 1;
      const five = loadResponse?.['5min'] ?? '';
      const fifteen = loadResponse?.['15min'] ?? '';

      setTextIfExists(TEXT_IDS.last1, String(now));
      setTextIfExists(TEXT_IDS.last5, String(five));
      setTextIfExists(TEXT_IDS.last15, String(fifteen));

      const container = document.getElementById(CONTAINER_ID);
      if (!container) {
        scheduleNext();
        return;
      }

      counter++;
      if (counter > 300) intervalMs = 5000;
      else if (counter > 150) intervalMs = 3000;

      let height = Math.floor((now / factor) * MAX_HEIGHT);
      height = Math.max(0, Math.min(MAX_HEIGHT, height));
      const marginTop = MAX_HEIGHT - height;

      const bar = document.createElement('div');
      bar.id = `load_bar_${counter}`;
      bar.className = 'load_bar';
      bar.style.height = `${height}px`;
      bar.style.marginTop = `${marginTop}px`;

      container.appendChild(bar);

      if (counter > 300) {
        const old = document.getElementById(`load_bar_${counter - 300}`);
        if (old) old.remove();
      }
    } catch (err) {
      // ignore transient errors
    } finally {
      // schedule only if still running and still on the correct page
      if (running && isOnSystemInfo()) scheduleNext();
      else stop();
    }
  }

  // Controls
  const start = () => {
    if (running) return;
    if (!isOnSystemInfo()) return;
    running = true;
    resetState(); // fresh start
    // immediate first run
    updateSystemLoad();
  };

  const stop = () => {
    running = false;
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
    clearBars();
    resetState();
  };

  // Boot on initial load if we're on the system info page
  const boot = () => {
    if (isOnSystemInfo()) start();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }

  // Turbo / Turbolinks / SPA handling:
  // - start on new page load
  // - stop & cleanup before Turbo cache or visit
  const safeListener = (evtName, handler) => {
    try { document.addEventListener(evtName, handler, { passive: true }); } catch (e) { /* ignore */ }
  };

  safeListener('turbo:load', () => { resetState(); clearBars(); start(); });
  safeListener('turbolinks:load', () => { resetState(); clearBars(); start(); });

  safeListener('turbo:before-cache', () => { stop(); });
  safeListener('turbolinks:before-cache', () => { stop(); });

  // turbo:visit happens when navigating away — stop immediately
  safeListener('turbo:visit', () => { stop(); });

  // fallback for native navigation
  window.addEventListener('popstate', () => {
    // slight delay to let DOM settle on back/forward navigation
    setTimeout(() => {
      if (isOnSystemInfo()) start();
      else stop();
    }, 50);
  }, { passive: true });
})();