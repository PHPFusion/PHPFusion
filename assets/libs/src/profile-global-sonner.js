import React from "react";
import { createRoot } from "react-dom/client";
import { Toaster, toast } from "sonner";

function mountProfileToaster() {
    let mount = document.getElementById("profile-global-toaster");
    if (!mount) {
        mount = document.createElement("div");
        mount.id = "profile-global-toaster";
        document.body.appendChild(mount);
    }

    createRoot(mount).render(
        React.createElement(Toaster, {
            position: window.matchMedia("(max-width: 575px)").matches ? "top-center" : "bottom-right",
            richColors: true,
            closeButton: true,
            duration: 3600,
            visibleToasts: 4,
            toastOptions: {
                className: "profile-global-sonner-toast",
            },
        })
    );

    window.ProfileToast = {
        success(message, options = {}) {
            return toast.success(message, options);
        },
        error(message, options = {}) {
            return toast.error(message, options);
        },
        info(message, options = {}) {
            return toast.info(message, options);
        },
        warning(message, options = {}) {
            return toast.warning(message, options);
        },
        loading(message, options = {}) {
            return toast.loading(message, options);
        },
        dismiss(id) {
            toast.dismiss(id);
        },
    };

    window.dispatchEvent(new CustomEvent("profile-global-toaster-ready"));
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", mountProfileToaster, { once: true });
} else {
    mountProfileToaster();
}
