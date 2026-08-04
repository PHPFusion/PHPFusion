(function () {
  'use strict';

  const roots = document.querySelectorAll('[data-admin-dashboard]');

  roots.forEach((root) => {
    const grid = root.querySelector('[data-dashboard-grid]');
    const arrangeButton = root.querySelector('[data-dashboard-arrange]');
    const live = root.querySelector('[data-dashboard-live]');
    const empty = root.querySelector('[data-dashboard-empty]');
    const cookieName = root.dataset.cookieName || '';
    const cookiePath = root.dataset.cookiePath || '/';
    const endpoint = root.dataset.widgetEndpoint || '';
    let arranging = false;
    let draggedCard = null;
    let pointerCard = null;
    let dragPlaceholder = null;
    let dragGhost = null;
    let dragOriginNext = null;
    let dragHideFrame = 0;
    let pointerOffsetX = 0;
    let pointerOffsetY = 0;

    if (!grid || !cookieName || typeof window.Cookies === 'undefined') {
      return;
    }

    const readState = () => {
      try {
        const parsed = JSON.parse(window.Cookies.get(cookieName) || '{}');
        if (parsed && parsed.version === 1) {
          return {
            version: 1,
            visibility: parsed.visibility && typeof parsed.visibility === 'object' ? parsed.visibility : {},
            order: Array.isArray(parsed.order) ? parsed.order.filter((id) => typeof id === 'string') : []
          };
        }
      } catch (error) {
        // Invalid preferences are replaced on the next valid interaction.
      }

      return {version: 1, visibility: {}, order: []};
    };

    let state = readState();

    const allWidgetIds = () => Array.from(root.querySelectorAll('[data-widget-toggle]'))
      .map((input) => input.dataset.widgetToggle)
      .filter(Boolean);

    const currentOrder = () => {
      const domOrder = Array.from(grid.querySelectorAll('[data-dashboard-widget]'))
        .map((card) => card.dataset.widgetId)
        .filter(Boolean);
      return Array.from(new Set([...domOrder, ...state.order, ...allWidgetIds()]));
    };

    const saveState = () => {
      state.order = currentOrder();
      const options = {expires: 365, path: cookiePath, sameSite: 'Lax'};
      if (window.location.protocol === 'https:') {
        options.secure = true;
      }
      window.Cookies.set(cookieName, JSON.stringify(state), options);
    };

    const announce = (message) => {
      if (!live) return;
      live.textContent = '';
      window.requestAnimationFrame(() => {
        live.textContent = message;
      });
    };

    const updateEmptyState = () => {
      if (!empty) return;
      const visible = Array.from(grid.querySelectorAll('[data-dashboard-widget]'))
        .some((card) => !card.hidden);
      empty.hidden = visible;
    };

    const escapeSelector = (value) => {
      if (window.CSS && typeof window.CSS.escape === 'function') {
        return window.CSS.escape(value);
      }
      return String(value).replace(/["\\]/g, '\\$&');
    };

    const widgetSelector = (id) => `[data-widget-id="${escapeSelector(id)}"]`;

    const insertBeforeTarget = (target, clientX, clientY) => {
      const rect = target.getBoundingClientRect();
      const withinRow = clientY >= rect.top && clientY <= rect.bottom;
      if (!withinRow) return clientY < rect.top + rect.height / 2;
      const beforeHorizontally = clientX < rect.left + rect.width / 2;
      return getComputedStyle(grid).direction === 'rtl' ? !beforeHorizontally : beforeHorizontally;
    };

    const createDragPlaceholder = (card) => {
      const placeholder = document.createElement('article');
      const rect = card.getBoundingClientRect();
      placeholder.className = 'admin-dashboard-widget admin-dashboard-drop-placeholder';
      placeholder.setAttribute('aria-hidden', 'true');
      ['spanSm', 'spanMd', 'spanLg', 'spanXl'].forEach((key) => {
        placeholder.dataset[key] = card.dataset[key] || '12';
      });
      placeholder.style.height = `${Math.round(rect.height)}px`;

      const label = document.createElement('span');
      label.className = 'admin-dashboard-drop-placeholder-label';
      label.textContent = root.dataset.dropLabel || '';
      placeholder.appendChild(label);

      return placeholder;
    };

    const createDragGhost = (card, clientX, clientY, nativeDrag) => {
      const rect = card.getBoundingClientRect();
      const ghost = card.cloneNode(true);
      ghost.classList.remove('is-dragging', 'is-drag-source');
      ghost.classList.add('admin-dashboard-drag-ghost');
      ghost.toggleAttribute('data-native-drag-ghost', nativeDrag);
      ghost.removeAttribute('data-dashboard-widget');
      ghost.removeAttribute('data-widget-id');
      ghost.removeAttribute('id');
      ghost.setAttribute('aria-hidden', 'true');
      ghost.querySelectorAll('[id]').forEach((element) => element.removeAttribute('id'));
      ghost.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach((element) => {
        element.tabIndex = -1;
      });
      ghost.style.width = `${Math.round(rect.width)}px`;
      ghost.style.height = `${Math.round(rect.height)}px`;

      pointerOffsetX = Math.max(16, Math.min(rect.width - 16, clientX - rect.left));
      pointerOffsetY = Math.max(16, Math.min(rect.height - 16, clientY - rect.top));
      root.appendChild(ghost);

      return ghost;
    };

    const positionPointerGhost = (clientX, clientY) => {
      if (!dragGhost) return;
      const left = Math.round(clientX - pointerOffsetX);
      const top = Math.round(clientY - pointerOffsetY);
      dragGhost.style.transform = `translate3d(${left}px, ${top}px, 0)`;
    };

    const beginVisualDrag = (card, clientX, clientY, nativeDrag) => {
      dragOriginNext = card.nextSibling;
      dragPlaceholder = createDragPlaceholder(card);
      grid.insertBefore(dragPlaceholder, card);
      dragGhost = createDragGhost(card, clientX, clientY, nativeDrag);

      if (nativeDrag) {
        dragHideFrame = window.requestAnimationFrame(() => card.classList.add('is-drag-source'));
      } else {
        card.classList.add('is-drag-source');
        positionPointerGhost(clientX, clientY);
      }
    };

    const moveDragPlaceholder = (target, clientX, clientY) => {
      if (!dragPlaceholder) return;
      if (!target) {
        grid.appendChild(dragPlaceholder);
        return;
      }
      const before = insertBeforeTarget(target, clientX, clientY);
      grid.insertBefore(dragPlaceholder, before ? target : target.nextElementSibling);
    };

    const finishVisualDrag = (card, commit) => {
      if (!card) return;
      if (dragHideFrame) {
        window.cancelAnimationFrame(dragHideFrame);
        dragHideFrame = 0;
      }

      if (commit && dragPlaceholder?.parentNode === grid) {
        grid.insertBefore(card, dragPlaceholder);
      } else if (dragOriginNext?.parentNode === grid) {
        grid.insertBefore(card, dragOriginNext);
      } else {
        grid.appendChild(card);
      }

      card.classList.remove('is-dragging', 'is-drag-source');
      dragPlaceholder?.remove();
      dragGhost?.remove();
      dragPlaceholder = null;
      dragGhost = null;
      dragOriginNext = null;

      if (commit) {
        saveState();
        announce(root.dataset.moved || '');
      }
    };

    const cancelActiveDrag = () => {
      if (draggedCard) {
        finishVisualDrag(draggedCard, false);
        draggedCard = null;
      }
      if (pointerCard) {
        finishVisualDrag(pointerCard, false);
        pointerCard = null;
      }
    };

    const setDragControls = () => {
      root.querySelectorAll('[data-dashboard-drag]').forEach((handle) => {
        handle.draggable = arranging;
        handle.tabIndex = arranging ? 0 : -1;
      });
      root.querySelectorAll('[data-dashboard-move]').forEach((button) => {
        button.tabIndex = arranging ? 0 : -1;
      });
    };

    const setArrangeMode = (enabled) => {
      if (!enabled) cancelActiveDrag();
      arranging = enabled;
      root.classList.toggle('is-arranging', arranging);
      arrangeButton?.setAttribute('aria-pressed', arranging ? 'true' : 'false');
      const label = arranging ? arrangeButton?.dataset.disableLabel : arrangeButton?.dataset.enableLabel;
      const labelElement = arrangeButton?.querySelector('span');
      if (label && labelElement) labelElement.textContent = label;
      setDragControls();
    };

    const placeBySavedOrder = (card) => {
      const id = card.dataset.widgetId;
      const index = state.order.indexOf(id);
      if (index < 0) {
        grid.appendChild(card);
        return;
      }

      const nextId = state.order.slice(index + 1).find((candidate) =>
        grid.querySelector(widgetSelector(candidate))
      );
      const nextCard = nextId
        ? grid.querySelector(widgetSelector(nextId))
        : null;
      grid.insertBefore(card, nextCard);
    };

    const loadingCard = (input) => {
      const card = document.createElement('article');
      card.className = 'admin-dashboard-widget';
      card.dataset.dashboardWidget = '';
      card.dataset.widgetId = input.dataset.widgetToggle;
      ['sm', 'md', 'lg', 'xl'].forEach((breakpoint) => {
        card.dataset[`span${breakpoint.charAt(0).toUpperCase()}${breakpoint.slice(1)}`] = input.dataset[`span${breakpoint.charAt(0).toUpperCase()}${breakpoint.slice(1)}`] || '12';
      });
      card.innerHTML = '<div class="admin-dashboard-loading" aria-busy="true"></div>';
      return card;
    };

    const loadWidget = async (input) => {
      const id = input.dataset.widgetToggle;
      if (!id || !endpoint) return;
      const current = grid.querySelector(widgetSelector(id));
      if (current?.dataset.loading === '1') return;
      current?.remove();
      let placeholder = loadingCard(input);
      placeholder.dataset.loading = '1';
      placeBySavedOrder(placeholder);
      updateEmptyState();

      try {
        const url = new URL(endpoint, window.location.href);
        url.searchParams.set('widget', id);
        const response = await fetch(url, {
          credentials: 'same-origin',
          headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
        });
        const data = await response.json();
        if (!response.ok || !data.success || !data.data?.html) {
          throw new Error(data.message || root.dataset.loadError || '');
        }

        const template = document.createElement('template');
        template.innerHTML = data.data.html.trim();
        const card = template.content.firstElementChild;
        if (!card) throw new Error(root.dataset.loadError || '');
        placeholder.replaceWith(card);
        placeBySavedOrder(card);
        setDragControls();
      } catch (error) {
        placeholder.dataset.loading = '0';
        placeholder.dataset.loadFailed = '1';
        placeholder.innerHTML = `<div class="admin-dashboard-error" role="alert"><strong>${escapeHtml(error.message || root.dataset.loadError || '')}</strong><button type="button" data-widget-retry="${escapeHtml(id)}">${escapeHtml(root.dataset.retryLabel || '')}</button></div>`;
      }
      updateEmptyState();
    };

    const escapeHtml = (value) => {
      const element = document.createElement('span');
      element.textContent = String(value || '');
      return element.innerHTML;
    };

    root.addEventListener('change', (event) => {
      const input = event.target.closest('[data-widget-toggle]');
      if (!input) return;
      const id = input.dataset.widgetToggle;
      const defaultVisible = input.dataset.defaultVisible === '1';
      if (input.checked === defaultVisible) {
        delete state.visibility[id];
      } else {
        state.visibility[id] = input.checked;
      }
      saveState();

      const existing = grid.querySelector(widgetSelector(id));
      if (!input.checked && existing) {
        existing.hidden = true;
      } else if (input.checked && existing && existing.dataset.loadFailed !== '1') {
        existing.hidden = false;
      } else if (input.checked) {
        loadWidget(input);
      }
      updateEmptyState();
    });

    arrangeButton?.addEventListener('click', () => setArrangeMode(!arranging));

    root.querySelector('[data-dashboard-reset]')?.addEventListener('click', (event) => {
      const message = event.currentTarget.dataset.confirm || '';
      if (message && !window.confirm(message)) return;
      window.Cookies.remove(cookieName, {path: cookiePath});
      window.location.reload();
    });

    root.addEventListener('click', (event) => {
      const retry = event.target.closest('[data-widget-retry]');
      if (retry) {
        const input = root.querySelector(`[data-widget-toggle="${escapeSelector(retry.dataset.widgetRetry || '')}"]`);
        if (input?.checked) loadWidget(input);
        return;
      }

      const move = event.target.closest('[data-dashboard-move]');
      if (!move || !arranging) return;
      const card = move.closest('[data-dashboard-widget]');
      const visibleCards = Array.from(grid.querySelectorAll('[data-dashboard-widget]')).filter((item) => !item.hidden);
      const index = visibleCards.indexOf(card);
      if (move.dataset.dashboardMove === 'previous' && index > 0) {
        grid.insertBefore(card, visibleCards[index - 1]);
      } else if (move.dataset.dashboardMove === 'next' && index >= 0 && index < visibleCards.length - 1) {
        grid.insertBefore(visibleCards[index + 1], card);
      } else {
        return;
      }
      saveState();
      announce(root.dataset.moved || '');
      card.querySelector('[data-dashboard-drag]')?.focus();
    });

    root.addEventListener('keydown', (event) => {
      const handle = event.target.closest('[data-dashboard-drag]');
      if (!handle || !arranging || !event.altKey) return;
      const previous = event.key === 'ArrowUp' || event.key === 'ArrowLeft';
      const next = event.key === 'ArrowDown' || event.key === 'ArrowRight';
      if (!previous && !next) return;
      event.preventDefault();
      const card = handle.closest('[data-dashboard-widget]');
      const cards = Array.from(grid.querySelectorAll('[data-dashboard-widget]')).filter((item) => !item.hidden);
      const index = cards.indexOf(card);
      if (previous && index > 0) grid.insertBefore(card, cards[index - 1]);
      if (next && index >= 0 && index < cards.length - 1) grid.insertBefore(cards[index + 1], card);
      saveState();
      announce(root.dataset.moved || '');
      handle.focus();
    });

    root.addEventListener('dragstart', (event) => {
      const handle = event.target.closest('[data-dashboard-drag]');
      if (!handle || !arranging) return;
      draggedCard = handle.closest('[data-dashboard-widget]');
      if (!draggedCard || !event.dataTransfer) {
        draggedCard = null;
        return;
      }
      draggedCard.classList.add('is-dragging');
      beginVisualDrag(draggedCard, event.clientX, event.clientY, true);
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', draggedCard.dataset.widgetId || '');
      event.dataTransfer.setDragImage(dragGhost, pointerOffsetX, pointerOffsetY);
    });

    grid.addEventListener('dragover', (event) => {
      if (!draggedCard) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      const target = event.target.closest('[data-dashboard-widget]');
      if (target === draggedCard || target?.hidden) return;
      moveDragPlaceholder(target, event.clientX, event.clientY);
    });

    const finishDrag = (commit = false) => {
      if (!draggedCard) return;
      finishVisualDrag(draggedCard, commit);
      draggedCard = null;
    };

    root.addEventListener('dragend', () => finishDrag(false));
    grid.addEventListener('drop', (event) => {
      event.preventDefault();
      finishDrag(true);
    });

    root.addEventListener('pointerdown', (event) => {
      const handle = event.target.closest('[data-dashboard-drag]');
      if (!handle || !arranging || event.pointerType === 'mouse') return;
      pointerCard = handle.closest('[data-dashboard-widget]');
      if (!pointerCard) return;
      event.preventDefault();
      handle.setPointerCapture(event.pointerId);
      pointerCard.classList.add('is-dragging');
      beginVisualDrag(pointerCard, event.clientX, event.clientY, false);
    });

    root.addEventListener('pointermove', (event) => {
      if (!pointerCard) return;
      event.preventDefault();
      positionPointerGhost(event.clientX, event.clientY);
      const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-dashboard-widget]');
      if (target === pointerCard || target?.hidden || (target && !grid.contains(target))) return;
      const pointIsInGrid = document.elementsFromPoint(event.clientX, event.clientY).includes(grid)
        || (target && grid.contains(target));
      if (pointIsInGrid) moveDragPlaceholder(target, event.clientX, event.clientY);
    });

    const finishPointer = (commit) => {
      if (!pointerCard) return;
      finishVisualDrag(pointerCard, commit);
      pointerCard = null;
    };

    root.addEventListener('pointerup', (event) => {
      const releaseTarget = document.elementFromPoint(event.clientX, event.clientY);
      finishPointer(Boolean(releaseTarget && grid.contains(releaseTarget)));
    });
    root.addEventListener('pointercancel', () => finishPointer(false));

    document.addEventListener('click', (event) => {
      const picker = root.querySelector('.admin-dashboard-picker[open]');
      if (picker && !picker.contains(event.target)) picker.removeAttribute('open');
    });

    root.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        root.querySelector('.admin-dashboard-picker[open]')?.removeAttribute('open');
        if (arranging) setArrangeMode(false);
      }
    });

    setArrangeMode(false);
    updateEmptyState();
  });
})();
