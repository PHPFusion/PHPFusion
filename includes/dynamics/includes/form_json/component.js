(function () {
  'use strict';

  const instances = new WeakMap();

  function parseConfig(root) {
    try {
      return JSON.parse(root.getAttribute('data-dynamics-json') || '{}');
    } catch (error) {
      return {};
    }
  }

  function parseEntryValue(raw) {
    const value = String(raw || '').trim();
    if (value === '') return '';
    return value.includes(',')
      ? value.split(',').map(function (item) { return item.trim(); }).filter(Boolean)
      : value;
  }

  function formatEntryValue(value) {
    if (Array.isArray(value) && value.every(function (item) { return typeof item === 'string'; })) {
      return value.join(', ');
    }
    return typeof value === 'string' ? value : JSON.stringify(value);
  }

  function encodePointerToken(value) {
    return String(value).replace(/~/g, '~0').replace(/\//g, '~1');
  }

  function decodePointerToken(value) {
    return String(value).replace(/~1/g, '/').replace(/~0/g, '~');
  }

  function collectParentOptions(value, path, label, options, depth) {
    if (!value || typeof value !== 'object' || depth > 20) return options;
    options.push({ value: path, label: label });
    Object.keys(value).forEach(function (key) {
      const child = value[key];
      if (!child || typeof child !== 'object') return;
      const token = encodePointerToken(key);
      const childPath = path + '/' + token;
      const childLabel = label === 'Root'
        ? (Array.isArray(value) ? '[' + key + ']' : key)
        : label + ' › ' + (Array.isArray(value) ? '[' + key + ']' : key);
      collectParentOptions(child, childPath, childLabel, options, depth + 1);
    });
    return options;
  }

  class DynamicsJsonEditor {
    constructor(root) {
      this.root = root;
      this.config = parseConfig(root);
      this.strings = Object.assign({
        invalidStored: 'The stored JSON is invalid and must be corrected before changes can be applied.',
        propertyRequired: 'Enter a property name.',
        indexInvalid: 'Enter a valid existing array index, or leave it blank to append an item.',
        removeProperty: 'Enter an existing property name to remove.',
        removeIndex: 'Enter an existing array index to remove.',
        nestedValue: 'This property contains nested values. Select it from Parent to edit its children.'
      }, this.config.strings || {});
      this.storage = root.querySelector('[data-dynamics-json-storage]');
      this.trigger = root.querySelector('[aria-haspopup="dialog"]');
      this.modal = root.querySelector('[data-dynamics-json-modal]');
      this.parentField = root.querySelector('[data-dynamics-json-parent]');
      this.keyField = root.querySelector('[data-dynamics-json-key]');
      this.keyLabel = root.querySelector('[data-dynamics-json-key-label]');
      this.valueField = root.querySelector('[data-dynamics-json-value]');
      this.preview = root.querySelector('[data-dynamics-json-preview]');
      this.summaryCount = root.querySelector('[data-dynamics-json-count]');
      this.modalCount = root.querySelector('[data-dynamics-json-modal-count]');
      this.error = root.querySelector('[data-dynamics-json-error]');
      this.upsertButton = root.querySelector('[data-dynamics-json-upsert]');
      this.removeButton = root.querySelector('[data-dynamics-json-remove]');
      this.applyButton = root.querySelector('[data-dynamics-json-apply]');
      this.workingValue = {};
      this.rootType = 'object';
      if (!this.storage || !this.trigger || !this.modal || !this.parentField || !this.keyField || !this.valueField || !this.preview) return;
      this.bind();
    }

    bind() {
      this.trigger.addEventListener('click', () => this.open());
      this.parentField.addEventListener('change', () => {
        this.keyField.value = '';
        this.valueField.value = '';
        this.showError('');
        this.setTargetLanguage();
      });
      this.keyField.addEventListener('change', () => this.loadEntry());
      this.upsertButton.addEventListener('click', () => this.upsert(true));
      this.removeButton.addEventListener('click', () => this.remove());
      this.applyButton.addEventListener('click', () => this.apply());
      [this.keyField, this.valueField].forEach((field) => {
        field.addEventListener('keydown', (event) => {
          const submitFromKey = field === this.keyField && event.key === 'Enter';
          const submitFromValue = field === this.valueField && event.key === 'Enter';
          if (submitFromKey || submitFromValue) {
            event.preventDefault();
            this.upsertButton.click();
          }
        });
      });
    }

    parseStorage() {
      try {
        const parsed = JSON.parse(this.storage.value || (this.config.rootType === 'array' ? '[]' : '{}'));
        if (!parsed || typeof parsed !== 'object') throw new Error('The JSON root must be an object or array.');
        this.rootType = Array.isArray(parsed) ? 'array' : 'object';
        this.workingValue = Array.isArray(parsed) ? parsed.slice() : Object.assign(Object.create(null), parsed);
        return true;
      } catch (error) {
        this.rootType = this.config.rootType === 'array' ? 'array' : 'object';
        this.workingValue = this.rootType === 'array' ? [] : Object.create(null);
        this.showError(this.strings.invalidStored + ' ' + error.message);
        return false;
      }
    }

    open() {
      const valid = this.parseStorage();
      this.keyField.value = '';
      this.valueField.value = '';
      this.renderParentOptions('');
      this.setTargetLanguage();
      this.renderPreview();
      this.parentField.disabled = !valid;
      this.upsertButton.disabled = !valid;
      this.removeButton.disabled = !valid;
      this.applyButton.disabled = !valid;
      if (valid) this.showError('');
      window.setTimeout(() => this.parentField.focus(), 120);
    }

    resolveParent(path) {
      if (!path) return this.workingValue;
      const tokens = path.split('/').slice(1).map(decodePointerToken);
      let value = this.workingValue;
      for (const token of tokens) {
        if (!value || typeof value !== 'object' || !Object.prototype.hasOwnProperty.call(value, token)) return null;
        value = value[token];
      }
      return value && typeof value === 'object' ? value : null;
    }

    renderParentOptions(selectedPath) {
      const options = collectParentOptions(this.workingValue, '', 'Root', [], 0);
      this.parentField.replaceChildren();
      options.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.value;
        option.textContent = item.label;
        this.parentField.appendChild(option);
      });
      this.parentField.value = options.some((item) => item.value === selectedPath) ? selectedPath : '';
    }

    targetValue() {
      return this.resolveParent(this.parentField.value) || this.workingValue;
    }

    setTargetLanguage() {
      const isArray = Array.isArray(this.targetValue());
      this.keyLabel.textContent = isArray ? 'Index (optional)' : 'Property name';
      this.keyField.placeholder = isArray ? 'Leave blank to append' : 'communication_style';
      this.upsertButton.textContent = isArray ? 'Add or update item' : 'Add or update';
      this.removeButton.textContent = isArray ? 'Remove item' : 'Remove property';
    }

    labelCount() {
      const count = this.rootType === 'array' ? this.workingValue.length : Object.keys(this.workingValue).length;
      const noun = this.rootType === 'array'
        ? (count === 1 ? 'item' : 'items')
        : (count === 1 ? 'property' : 'properties');
      return count + ' ' + noun;
    }

    showError(message) {
      this.error.textContent = message || '';
      this.error.hidden = !message;
    }

    loadEntry() {
      const key = this.keyField.value.trim();
      const target = this.targetValue();
      if (Array.isArray(target)) {
        if (/^\d+$/.test(key) && Number(key) < target.length) {
          const entry = target[Number(key)];
          if (entry && typeof entry === 'object' && !Array.isArray(entry)) {
            this.valueField.value = '';
            this.showError(this.strings.nestedValue);
            return;
          }
          this.valueField.value = formatEntryValue(entry);
          this.showError('');
        }
        return;
      }
      if (key && Object.prototype.hasOwnProperty.call(target, key)) {
        const entry = target[key];
        if (entry && typeof entry === 'object' && !Array.isArray(entry)) {
          this.valueField.value = '';
          this.showError(this.strings.nestedValue);
          return;
        }
        this.valueField.value = formatEntryValue(entry);
        this.showError('');
      }
    }

    upsert(focusAfter) {
      const key = this.keyField.value.trim();
      const value = parseEntryValue(this.valueField.value);
      const selectedParent = this.parentField.value;
      const target = this.targetValue();
      if (Array.isArray(target)) {
        if (key === '') {
          target.push(value);
        } else if (/^\d+$/.test(key) && Number(key) <= target.length) {
          const index = Number(key);
          if (index === target.length) target.push(value);
          else target[index] = value;
        } else {
          this.showError(this.strings.indexInvalid);
          this.keyField.focus();
          return false;
        }
      } else {
        if (!key) {
          this.showError(this.strings.propertyRequired);
          this.keyField.focus();
          return false;
        }
        target[key] = value;
      }
      this.keyField.value = '';
      this.valueField.value = '';
      this.showError('');
      this.renderParentOptions(selectedParent);
      this.setTargetLanguage();
      this.renderPreview();
      if (focusAfter) this.keyField.focus();
      return true;
    }

    remove() {
      const key = this.keyField.value.trim();
      const selectedParent = this.parentField.value;
      const target = this.targetValue();
      if (Array.isArray(target)) {
        if (!/^\d+$/.test(key) || Number(key) >= target.length) {
          this.showError(this.strings.removeIndex);
          this.keyField.focus();
          return;
        }
        target.splice(Number(key), 1);
      } else {
        if (!key || !Object.prototype.hasOwnProperty.call(target, key)) {
          this.showError(this.strings.removeProperty);
          this.keyField.focus();
          return;
        }
        delete target[key];
      }
      this.keyField.value = '';
      this.valueField.value = '';
      this.showError('');
      this.renderParentOptions(selectedParent);
      this.setTargetLanguage();
      this.renderPreview();
      this.keyField.focus();
    }

    renderPreview() {
      const count = this.labelCount();
      this.preview.textContent = JSON.stringify(this.workingValue, null, 2);
      if (this.modalCount) this.modalCount.textContent = count;
    }

    apply() {
      if (this.keyField.value.trim() !== '' || this.valueField.value.trim() !== '') {
        if (!this.upsert(false)) return;
      }
      this.storage.value = JSON.stringify(this.workingValue, null, 2);
      this.storage.dispatchEvent(new Event('input', { bubbles: true }));
      this.storage.dispatchEvent(new Event('change', { bubbles: true }));
      if (this.summaryCount) this.summaryCount.textContent = this.labelCount();
      const modalElement = this.modal.closest('[role="dialog"], .modal');
      const closeControl = modalElement
        ? modalElement.querySelector('[data-tailwind-modal-close], [data-bs-dismiss="modal"], [data-dismiss="modal"]')
        : null;
      if (closeControl) closeControl.click();
      window.setTimeout(() => this.trigger.focus(), 0);
    }
  }

  function enhance(root) {
    if (!root || instances.has(root)) return instances.get(root) || null;
    const instance = new DynamicsJsonEditor(root);
    instances.set(root, instance);
    return instance;
  }

  function initialize(scope) {
    const root = scope && scope.querySelectorAll ? scope : document;
    if (root.matches && root.matches('[data-dynamics-json]')) enhance(root);
    root.querySelectorAll('[data-dynamics-json]').forEach(enhance);
  }

  function start() {
    initialize(document);
    new MutationObserver(function (records) {
      records.forEach(function (record) {
        record.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) initialize(node);
        });
      });
    }).observe(document.body, { childList: true, subtree: true });
  }

  window.PHPFusionDynamicsJson = { enhance: enhance, initialize: initialize };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
