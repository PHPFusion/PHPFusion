(function () {
    "use strict";

    const categoryHashPrefix = "#profile-edit-category-";
    const moduleHashPrefix = "#profile-edit-module-";

    function toast(type, message) {
        if (window.ProfileToast && typeof window.ProfileToast[type] === "function") {
            window.ProfileToast[type](message);
            return;
        }

        window.addEventListener("profile-global-toaster-ready", function notifyWhenReady() {
            if (window.ProfileToast && typeof window.ProfileToast[type] === "function") {
                window.ProfileToast[type](message);
            }
        }, { once: true });
    }

    function setBusy(form, busy) {
        form.setAttribute("aria-busy", busy ? "true" : "false");
        form.querySelectorAll("button[type='submit']").forEach(function (button) {
            button.disabled = busy;
            button.classList.toggle("is-loading", busy);
        });

        const label = form.querySelector("[data-profile-button-label]");
        if (label) {
            if (!label.dataset.profileIdleLabel) {
                label.dataset.profileIdleLabel = label.textContent.trim();
            }
            label.textContent = busy ? "Saving…" : label.dataset.profileIdleLabel;
        }
    }

    function clearErrors(form) {
        form.querySelectorAll("[data-profile-field].is-invalid").forEach(function (field) {
            field.classList.remove("is-invalid");
        });
        form.querySelectorAll("[aria-invalid='true']").forEach(function (control) {
            control.removeAttribute("aria-invalid");
        });
        form.querySelectorAll("[data-profile-field-error]").forEach(function (error) {
            const field = error.closest("[data-profile-field]");
            const control = field ? field.querySelector("[name]:not([type='hidden'])") : null;
            if (control && error.id) {
                const describedBy = (control.getAttribute("aria-describedby") || "")
                    .split(/\s+/)
                    .filter(function (id) { return id && id !== error.id; });
                if (describedBy.length) {
                    control.setAttribute("aria-describedby", describedBy.join(" "));
                } else {
                    control.removeAttribute("aria-describedby");
                }
            }
            error.textContent = "";
        });
    }

    function showErrors(form, errors) {
        let firstInvalidControl = null;

        Object.keys(errors || {}).forEach(function (name) {
            const escapedName = CSS.escape(name);
            const field = form.querySelector("[data-profile-field='" + escapedName + "']");
            const error = form.querySelector("[data-profile-field-error='" + escapedName + "']");
            const control = form.querySelector("[name='" + escapedName + "']:not([type='hidden'])");

            if (field) {
                field.classList.add("is-invalid");
            }
            if (control) {
                control.setAttribute("aria-invalid", "true");
                if (error && error.id) {
                    const describedBy = new Set((control.getAttribute("aria-describedby") || "").split(/\s+/).filter(Boolean));
                    describedBy.add(error.id);
                    control.setAttribute("aria-describedby", Array.from(describedBy).join(" "));
                }
                firstInvalidControl = firstInvalidControl || control;
            }
            if (error) {
                error.textContent = Array.isArray(errors[name]) ? errors[name][0] : errors[name];
            }
        });

        if (firstInvalidControl) {
            firstInvalidControl.focus();
        }
    }

    function updateToken(form, payload) {
        const token = payload && payload.data && payload.data.fusion_token;
        if (!token) {
            return;
        }

        const input = form.querySelector("input[name='fusion_token']");
        if (input) {
            input.value = token;
        }
    }

    function updateVisibleProfile(payload) {
        const values = payload && payload.data && payload.data.values ? payload.data.values : {};

        Object.keys(values).forEach(function (key) {
            document.querySelectorAll("[data-profile-header-value='" + CSS.escape(key) + "']").forEach(function (node) {
                node.textContent = values[key];
            });
        });

        if (values.avatar_url) {
            document.querySelectorAll("[data-profile-avatar-global], [data-profile-avatar-preview]").forEach(function (image) {
                image.src = values.avatar_url;
            });
        }
    }

    function fieldControl(form, name) {
        return form.querySelector("[name='" + CSS.escape(name) + "']:not([type='hidden'])");
    }

    function applyModuleValues(form, module) {
        const values = module && module.values ? module.values : {};
        const schema = module && Array.isArray(module.schema) ? module.schema : [];

        schema.forEach(function (field) {
            const name = field.name || "";
            const type = field.type || "text";
            const value = Object.prototype.hasOwnProperty.call(values, name) ? values[name] : field.value;
            const control = fieldControl(form, name);

            if (!control || type === "avatar") {
                return;
            }

            if (type === "switch" || type === "checkbox") {
                control.checked = Boolean(Number(value));
                return;
            }

            if (control.type !== "file") {
                control.value = value == null ? "" : String(value);
            }
        });

        if (values.avatar_url) {
            const preview = form.querySelector("[data-profile-avatar-preview]");
            if (preview) {
                preview.src = values.avatar_url;
            }
        }
    }

    function formSummary(form, values) {
        const summaryName = form.dataset.profileSummaryField || "";
        if (summaryName && values && Object.prototype.hasOwnProperty.call(values, summaryName)) {
            const summaryValue = String(values[summaryName] || "").trim();
            return summaryValue ? (summaryValue.length > 76 ? summaryValue.slice(0, 75) + "…" : summaryValue) : "Not set";
        }

        const field = form.querySelector("[data-profile-field-type]");
        if (!field) {
            return "Not set";
        }

        const type = field.dataset.profileFieldType || "text";
        const name = field.dataset.profileFieldName || "";
        const control = fieldControl(form, name);

        if (type === "avatar") {
            return values && values.avatar_name ? "Photo added" : "Add a profile photo";
        }

        if (!control) {
            return "Not set";
        }

        if (type === "switch" || type === "checkbox") {
            return control.checked ? "On" : "Off";
        }

        if (type === "select") {
            const option = control.options[control.selectedIndex];
            return option ? option.textContent.trim() : "Not set";
        }

        const value = String(control.value || "").trim();
        return value ? (value.length > 76 ? value.slice(0, 75) + "…" : value) : "Not set";
    }

    function updateModuleSummary(form, values) {
        const moduleInput = form.querySelector("input[name='module']");
        if (!moduleInput) {
            return;
        }

        document.querySelectorAll("[data-profile-module-summary='" + CSS.escape(moduleInput.value) + "']").forEach(function (summary) {
            summary.textContent = formSummary(form, values || {});
        });
    }

    function updateAdminModuleState(form, data) {
        if (!form.classList.contains("profile-global-admin-form") || !data) {
            return;
        }

        const label = form.querySelector("[data-profile-enabled-label]");
        const checkbox = form.querySelector("input[type='checkbox'][name='enabled']");
        if (!label || !checkbox) {
            return;
        }

        checkbox.checked = Boolean(data.enabled);
        label.textContent = checkbox.disabled ? "Required" : (checkbox.checked ? "Enabled" : "Disabled");

        const enabledCount = document.querySelector("[data-profile-enabled-count]");
        if (enabledCount) {
            enabledCount.textContent = String(
                Array.from(document.querySelectorAll(".profile-global-admin-form input[type='checkbox'][name='enabled']"))
                    .filter(function (input) { return input.checked; })
                    .length
            );
        }
    }

    async function saveForm(form, options) {
        const settings = options || {};
        clearErrors(form);
        setBusy(form, true);

        const status = form.querySelector("[data-profile-save-status]");
        if (status) {
            status.textContent = "Saving…";
        }

        try {
            const formData = new FormData(form);
            if (settings.submitter && settings.submitter.name) {
                formData.set(settings.submitter.name, settings.submitter.value);
            }

            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            const payload = await response.json();
            updateToken(form, payload);

            if (!response.ok || !payload.success) {
                showErrors(form, payload.errors || {});
                throw new Error(payload.message || "The changes could not be saved.");
            }

            updateVisibleProfile(payload);
            updateModuleSummary(form, payload.data && payload.data.values ? payload.data.values : {});
            updateAdminModuleState(form, payload.data || {});

            const orderInput = form.querySelector("input[name='order']");
            if (orderInput && payload.data && payload.data.order !== undefined) {
                orderInput.value = String(payload.data.order);
                orderInput.dataset.profileSavedOrder = orderInput.value;
            }

            if (status) {
                status.textContent = "Saved";
                window.setTimeout(function () {
                    status.textContent = "";
                }, 2200);
            }
            if (!settings.silentSuccess) {
                toast("success", payload.message || "Changes saved.");
            }
            return payload;
        } catch (error) {
            if (status) {
                status.textContent = "Not saved";
            }
            if (!settings.silentError) {
                toast("error", error.message || "The changes could not be saved.");
            }
            if (settings.throwOnError) {
                throw error;
            }
            return null;
        } finally {
            setBusy(form, false);
        }
    }

    function submitForm(event) {
        const form = event.target.closest("[data-profile-module-form], .profile-global-admin-form");
        if (!form) {
            return;
        }

        event.preventDefault();
        saveForm(form, { submitter: event.submitter });
    }

    function setOrderStatus(message, state) {
        const status = document.querySelector("[data-profile-order-status]");
        if (!status) {
            return;
        }

        status.textContent = message;
        status.dataset.state = state || "";
    }

    function updateMoveButtons(list) {
        const rows = Array.from(list.querySelectorAll(".profile-global-admin-form"));

        rows.forEach(function (row, index) {
            const up = row.querySelector("[data-profile-move='up']");
            const down = row.querySelector("[data-profile-move='down']");

            if (up) {
                up.disabled = index === 0;
            }
            if (down) {
                down.disabled = index === rows.length - 1;
            }
        });
    }

    async function persistOrder(list) {
        if (list.dataset.profileSavingOrder === "true") {
            return;
        }

        const changedForms = [];
        Array.from(list.querySelectorAll(".profile-global-admin-form")).forEach(function (form, index) {
            const orderInput = form.querySelector("input[name='order']");
            const nextOrder = String((index + 1) * 10);

            if (!orderInput) {
                return;
            }

            orderInput.value = nextOrder;
            if (orderInput.dataset.profileSavedOrder === nextOrder) {
                return;
            }
            changedForms.push(form);
        });

        if (!changedForms.length) {
            updateMoveButtons(list);
            return;
        }

        list.dataset.profileSavingOrder = "true";
        list.setAttribute("aria-busy", "true");
        setOrderStatus("Saving orderâ€¦", "saving");

        try {
            for (const form of changedForms) {
                await saveForm(form, {
                    silentSuccess: true,
                    silentError: true,
                    throwOnError: true,
                });
            }

            setOrderStatus("Order saved", "success");
            toast("success", (list.dataset.profileCategoryLabel || "Module") + " order saved.");
            window.setTimeout(function () {
                setOrderStatus("", "");
            }, 2200);
        } catch (error) {
            setOrderStatus("Order not saved", "error");
            toast("error", error.message || "The new module order could not be saved.");
        } finally {
            delete list.dataset.profileSavingOrder;
            list.removeAttribute("aria-busy");
            updateMoveButtons(list);
        }
    }

    function moveRow(row, direction) {
        const list = row.closest("[data-profile-sortable]");
        if (!list || list.dataset.profileSavingOrder === "true") {
            return;
        }

        if (direction === "up" && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
        } else if (direction === "down" && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
        } else {
            return;
        }

        row.classList.add("is-reordered");
        window.setTimeout(function () {
            row.classList.remove("is-reordered");
        }, 360);
        updateMoveButtons(list);
        persistOrder(list);
    }

    function bindAdminControls() {
        const admin = document.querySelector("[data-profile-admin]");
        if (!admin) {
            return;
        }

        admin.classList.add("profile-global-enhanced");

        const categoryMenuLabel = admin.querySelector("[data-profile-category-menu-label]");
        const categorySearch = admin.querySelector("[data-profile-category-search]");
        const categoryEmpty = admin.querySelector("[data-profile-category-empty]");
        const categorySelectionCount = admin.querySelector("[data-profile-category-selection-count]");
        const categoryClear = admin.querySelector("[data-profile-category-clear]");
        const categoryLinks = Array.from(admin.querySelectorAll("[data-profile-admin-category-jump]"));

        function setCategorySelection(selectedLink) {
            categoryLinks.forEach(function (link) {
                const selected = link === selectedLink;
                const check = link.querySelector("[data-profile-category-check]");
                link.classList.toggle("active", selected);
                if (selected) {
                    link.setAttribute("aria-current", "true");
                } else {
                    link.removeAttribute("aria-current");
                }
                if (check) {
                    check.hidden = !selected;
                }
            });

            if (categoryMenuLabel) {
                categoryMenuLabel.textContent = selectedLink && selectedLink.dataset.profileCategoryLabel
                    ? selectedLink.dataset.profileCategoryLabel
                    : "Jump to section";
            }
            if (categorySelectionCount) {
                categorySelectionCount.textContent = selectedLink ? "1 selected" : "0 selected";
            }
            if (categoryClear) {
                categoryClear.disabled = !selectedLink;
            }
        }

        function filterCategories() {
            const query = categorySearch ? categorySearch.value.trim().toLowerCase() : "";
            let visibleCount = 0;

            categoryLinks.forEach(function (link) {
                const option = link.closest("[data-profile-category-option]");
                const label = (link.dataset.profileCategoryLabel || link.textContent || "").trim().toLowerCase();
                const visible = !query || label.includes(query);
                if (option) {
                    option.hidden = !visible;
                }
                visibleCount += visible ? 1 : 0;
            });

            if (categoryEmpty) {
                categoryEmpty.hidden = visibleCount !== 0;
            }
        }

        categoryLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                setCategorySelection(link);
            });
        });

        if (categorySearch) {
            categorySearch.addEventListener("click", function (event) {
                event.stopPropagation();
            });
            categorySearch.addEventListener("keydown", function (event) {
                if (event.key !== "Escape") {
                    event.stopPropagation();
                }
            });
            categorySearch.addEventListener("input", filterCategories);

            const categoryDropdown = categorySearch.closest(".dropdown");
            if (categoryDropdown) {
                categoryDropdown.addEventListener("shown.bs.dropdown", function () {
                    categorySearch.focus();
                });
            }
        }

        if (categoryClear) {
            categoryClear.addEventListener("click", function (event) {
                event.preventDefault();
                event.stopPropagation();
                setCategorySelection(null);
                if (categorySearch) {
                    categorySearch.value = "";
                    filterCategories();
                    categorySearch.focus();
                }
            });
        }

        admin.querySelectorAll(".profile-global-admin-form input[type='checkbox'][name='enabled']").forEach(function (checkbox) {
            if (checkbox.disabled) {
                return;
            }

            checkbox.addEventListener("change", function () {
                const form = checkbox.closest(".profile-global-admin-form");
                if (form) {
                    saveForm(form);
                }
            });
        });

        admin.querySelectorAll("[data-profile-sortable]").forEach(function (list) {
            let draggedRow = null;
            let orderChanged = false;

            list.querySelectorAll("input[name='order']").forEach(function (input) {
                input.dataset.profileSavedOrder = input.value;
            });
            updateMoveButtons(list);

            list.querySelectorAll("[data-profile-drag-handle]").forEach(function (handle) {
                handle.draggable = true;

                handle.addEventListener("dragstart", function (event) {
                    draggedRow = handle.closest(".profile-global-admin-form");
                    if (!draggedRow || list.dataset.profileSavingOrder === "true") {
                        event.preventDefault();
                        return;
                    }

                    orderChanged = false;
                    draggedRow.classList.add("is-dragging");
                    handle.setAttribute("aria-pressed", "true");
                    event.dataTransfer.effectAllowed = "move";
                    event.dataTransfer.setData("text/plain", draggedRow.querySelector("input[name='module']").value);
                });

                handle.addEventListener("dragend", function () {
                    if (!draggedRow) {
                        return;
                    }

                    draggedRow.classList.remove("is-dragging");
                    handle.removeAttribute("aria-pressed");
                    const shouldSave = orderChanged;
                    draggedRow = null;
                    orderChanged = false;

                    if (shouldSave) {
                        persistOrder(list);
                    }
                });

                handle.addEventListener("keydown", function (event) {
                    if (event.key !== "ArrowUp" && event.key !== "ArrowDown") {
                        return;
                    }

                    event.preventDefault();
                    const row = handle.closest(".profile-global-admin-form");
                    if (row) {
                        moveRow(row, event.key === "ArrowUp" ? "up" : "down");
                        handle.focus({ preventScroll: true });
                    }
                });
            });

            list.addEventListener("dragover", function (event) {
                if (!draggedRow) {
                    return;
                }

                const target = event.target.closest(".profile-global-admin-form");
                if (!target || target === draggedRow || target.parentElement !== list) {
                    return;
                }

                event.preventDefault();
                event.dataTransfer.dropEffect = "move";
                const bounds = target.getBoundingClientRect();
                const insertBefore = event.clientY < bounds.top + bounds.height / 2;
                list.insertBefore(draggedRow, insertBefore ? target : target.nextElementSibling);
                orderChanged = true;
                updateMoveButtons(list);
            });

            list.addEventListener("drop", function (event) {
                if (draggedRow) {
                    event.preventDefault();
                }
            });

            list.querySelectorAll("[data-profile-move]").forEach(function (button) {
                button.addEventListener("click", function () {
                    const row = button.closest(".profile-global-admin-form");
                    if (row) {
                        moveRow(row, button.dataset.profileMove);
                    }
                });
            });
        });
    }

    function closeModuleEditors(categoryKey) {
        document.querySelectorAll("[data-profile-module-panel]").forEach(function (panel) {
            panel.hidden = true;
            panel.removeAttribute("aria-busy");
        });

        document.querySelectorAll("[data-profile-module-link]").forEach(function (link) {
            link.setAttribute("aria-expanded", "false");
        });

        document.querySelectorAll("[data-profile-category-overview]").forEach(function (overview) {
            overview.hidden = overview.dataset.profileCategoryOverview !== categoryKey;
        });
    }

    function activateCategory(key, updateHash) {
        const targetPanel = document.querySelector("[data-profile-category-panel='" + CSS.escape(key) + "']");
        if (!targetPanel) {
            return;
        }

        document.querySelectorAll("[data-profile-category-link]").forEach(function (link) {
            const active = link.dataset.profileCategoryLink === key;
            link.classList.toggle("active", active);
            if (active) {
                link.setAttribute("aria-current", "page");
            } else {
                link.removeAttribute("aria-current");
            }
        });

        document.querySelectorAll("[data-profile-category-panel]").forEach(function (panel) {
            const active = panel.dataset.profileCategoryPanel === key;
            panel.hidden = !active;
        });

        closeModuleEditors(key);

        if (updateHash) {
            history.replaceState(null, "", categoryHashPrefix + encodeURIComponent(key));
        }
    }

    async function refreshModule(link, panel) {
        const endpoint = link.dataset.profileModuleEndpoint;
        const form = panel.querySelector("[data-profile-module-form]");
        if (!endpoint || !form) {
            return;
        }

        panel.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(endpoint, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });
            const payload = await response.json();
            if (!response.ok || !payload.success || !payload.data || !payload.data.module) {
                throw new Error(payload.message || "This setting could not be refreshed.");
            }
            applyModuleValues(form, payload.data.module);
            updateModuleSummary(form, payload.data.module.values || {});
        } catch (error) {
            toast("error", error.message || "This setting could not be refreshed.");
        } finally {
            panel.removeAttribute("aria-busy");
        }
    }

    function openModule(moduleKey, updateHash) {
        const link = document.querySelector("[data-profile-module-link='" + CSS.escape(moduleKey) + "']");
        const panel = document.querySelector("[data-profile-module-panel='" + CSS.escape(moduleKey) + "']");
        if (!link || !panel) {
            return;
        }

        const categoryKey = panel.dataset.profileModuleCategory;
        activateCategory(categoryKey, false);

        const overview = document.querySelector("[data-profile-category-overview='" + CSS.escape(categoryKey) + "']");
        if (overview) {
            overview.hidden = true;
        }

        panel.hidden = false;
        link.setAttribute("aria-expanded", "true");

        const heading = panel.querySelector("h2");
        if (heading) {
            heading.setAttribute("tabindex", "-1");
            heading.focus({ preventScroll: true });
        }

        if (window.matchMedia("(max-width: 899px)").matches) {
            panel.scrollIntoView({ block: "start" });
        }

        if (updateHash) {
            history.replaceState(null, "", moduleHashPrefix + encodeURIComponent(moduleKey));
        }

        refreshModule(link, panel);
    }

    function bindNavigation() {
        document.querySelectorAll("[data-profile-category-link]").forEach(function (link) {
            link.addEventListener("click", function (event) {
                event.preventDefault();
                activateCategory(link.dataset.profileCategoryLink, true);
            });
        });

        document.querySelectorAll("[data-profile-module-link]").forEach(function (link) {
            link.addEventListener("click", function (event) {
                event.preventDefault();
                openModule(link.dataset.profileModuleLink, true);
            });
        });

        document.querySelectorAll("[data-profile-module-back]").forEach(function (button) {
            button.addEventListener("click", function () {
                activateCategory(button.dataset.profileModuleBack, true);
            });
        });

        if (window.location.hash.indexOf(moduleHashPrefix) === 0) {
            openModule(decodeURIComponent(window.location.hash.substring(moduleHashPrefix.length)), false);
            return;
        }

        if (window.location.hash.indexOf(categoryHashPrefix) === 0) {
            activateCategory(decodeURIComponent(window.location.hash.substring(categoryHashPrefix.length)), false);
        }
    }

    function bindAvatarPreview() {
        document.querySelectorAll("[data-profile-avatar-input]").forEach(function (input) {
            input.addEventListener("change", function () {
                const file = input.files && input.files[0];
                const form = input.closest("form");
                const preview = form ? form.querySelector("[data-profile-avatar-preview]") : null;
                if (!file || !preview) {
                    return;
                }

                const reader = new FileReader();
                reader.addEventListener("load", function () {
                    preview.src = reader.result;
                }, { once: true });
                reader.readAsDataURL(file);
            });
        });
    }

    function init() {
        document.addEventListener("submit", submitForm);
        bindNavigation();
        bindAvatarPreview();
        bindAdminControls();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
}());
