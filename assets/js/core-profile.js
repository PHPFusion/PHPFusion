(function () {
    "use strict";

    function toast(type, message) {
        if (window.ProfileToast && typeof window.ProfileToast[type] === "function") {
            window.ProfileToast[type](message);
        }
    }

    function setBusy(form, busy) {
        form.setAttribute("aria-busy", busy ? "true" : "false");
        form.querySelectorAll("button[type='submit']").forEach(function (button) {
            button.disabled = busy;
        });
        const label = form.querySelector("[data-profile-button-label]");
        if (label) {
            label.dataset.idleLabel = label.dataset.idleLabel || label.textContent.trim();
            label.textContent = busy ? "Saving…" : label.dataset.idleLabel;
        }
    }

    function updateToken(form, payload) {
        const token = payload && payload.data && payload.data.fusion_token;
        const input = form.querySelector("input[name='fusion_token']");
        if (token && input) {
            input.value = token;
        }
    }

    function clearErrors(form) {
        form.querySelectorAll("[aria-invalid='true']").forEach(function (control) {
            control.removeAttribute("aria-invalid");
        });
        form.querySelectorAll("[data-profile-field-error]").forEach(function (error) {
            error.textContent = "";
        });
    }

    function showErrors(form, errors) {
        let first = null;
        Object.keys(errors || {}).forEach(function (name) {
            const control = form.querySelector("[name='" + CSS.escape(name) + "']");
            const error = form.querySelector("[data-profile-field-error='" + CSS.escape(name) + "']");
            if (control) {
                control.setAttribute("aria-invalid", "true");
                first = first || control;
            }
            if (error) {
                error.textContent = Array.isArray(errors[name]) ? errors[name][0] : String(errors[name]);
            }
        });
        if (first) {
            first.focus();
        }
    }

    async function submitAjax(form, submitter) {
        clearErrors(form);
        setBusy(form, true);
        const status = form.querySelector("[data-profile-save-status]") || document.querySelector("[data-public-profile-status]");
        if (status) {
            status.className = "small text-secondary";
            status.textContent = "Saving…";
        }

        try {
            const formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.set(submitter.name, submitter.value);
            }
            const response = await fetch(form.action, {
                method: "POST",
                body: formData,
                credentials: "same-origin",
                headers: {"Accept": "application/json", "X-Requested-With": "XMLHttpRequest"},
            });
            const payload = await response.json();
            updateToken(form, payload);
            if (!response.ok || !payload.success) {
                showErrors(form, payload.errors || {});
                throw new Error(payload.message || "The profile could not be updated.");
            }

            const avatar = payload.data && payload.data.avatar_url;
            if (avatar) {
                document.querySelectorAll("[data-public-preview-avatar], [data-public-avatar-preview], [data-profile-avatar-preview]").forEach(function (image) {
                    image.src = avatar;
                });
            }
            if (status) {
                status.className = "small text-success";
                status.textContent = payload.message || "Profile updated.";
            }
            toast("success", payload.message || "Profile updated.");
        } catch (error) {
            if (status) {
                status.className = "small text-danger";
                status.textContent = error.message;
            }
            toast("error", error.message);
        } finally {
            setBusy(form, false);
        }
    }

    function initSettingsTabs() {
        const shell = document.querySelector("[data-profile-settings]");
        if (!shell) {
            return;
        }
        const tabs = Array.from(shell.querySelectorAll("[data-profile-settings-tab]"));
        const panels = Array.from(shell.querySelectorAll("[data-profile-settings-panel]"));
        tabs.forEach(function (tab) {
            tab.addEventListener("click", function (event) {
                event.preventDefault();
                const key = tab.dataset.profileSettingsTab;
                tabs.forEach(function (item) {
                    const active = item === tab;
                    item.classList.toggle("active", active);
                    item.setAttribute("aria-selected", active ? "true" : "false");
                    item.setAttribute("tabindex", active ? "0" : "-1");
                });
                panels.forEach(function (panel) {
                    panel.classList.toggle("d-none", panel.dataset.profileSettingsPanel !== key);
                });
                history.replaceState(null, "", tab.getAttribute("href"));
            });
            tab.addEventListener("keydown", function (event) {
                const current = tabs.indexOf(tab);
                let next = current;
                if (event.key === "ArrowRight" || event.key === "ArrowDown") {
                    next = (current + 1) % tabs.length;
                } else if (event.key === "ArrowLeft" || event.key === "ArrowUp") {
                    next = (current - 1 + tabs.length) % tabs.length;
                } else if (event.key === "Home") {
                    next = 0;
                } else if (event.key === "End") {
                    next = tabs.length - 1;
                } else {
                    return;
                }
                event.preventDefault();
                tabs[next].focus();
                tabs[next].click();
            });
        });

        const hashTab = tabs.find(function (tab) { return tab.getAttribute("href") === window.location.hash; });
        if (hashTab) {
            hashTab.click();
        }
    }

    function initLivePreview() {
        document.querySelectorAll("[data-public-preview-input]").forEach(function (input) {
            const key = input.dataset.publicPreviewInput;
            const output = document.querySelector("[data-public-preview-output='" + CSS.escape(key) + "']");
            const previewLink = document.querySelector("[data-public-preview-link='" + CSS.escape(key) + "']");
            if (!output && !previewLink) {
                return;
            }
            input.addEventListener("input", function () {
                if (output) {
                    output.textContent = input.value.trim() || (key === "bio" ? "Add a short bio to introduce yourself." : "");
                }
                if (key === "location") {
                    const line = document.querySelector("[data-public-preview-line='location']");
                    if (line) {
                        line.classList.toggle("d-none", input.value.trim() === "");
                    }
                }
                if (previewLink) {
                    const value = input.value.trim();
                    previewLink.classList.toggle("d-none", value === "");
                }
            });
        });

        document.querySelectorAll("[data-public-avatar-input], [data-profile-avatar-input]").forEach(function (input) {
            input.addEventListener("change", function () {
                const file = input.files && input.files[0];
                if (!file || !file.type.startsWith("image/")) {
                    return;
                }
                const url = URL.createObjectURL(file);
                document.querySelectorAll("[data-public-preview-avatar], [data-public-avatar-preview], [data-profile-avatar-preview]").forEach(function (image) {
                    image.src = url;
                });
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        initSettingsTabs();
        initLivePreview();
        document.querySelectorAll("[data-profile-module-form], [data-public-profile-form]").forEach(function (form) {
            form.addEventListener("submit", function (event) {
                event.preventDefault();
                submitAjax(form, event.submitter || null);
            });
        });
    });
}());
