import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.data(
    "shippingLabelPage",
    ({ nextIndex = 0, showErrors = false } = {}) => ({
        nextIndex,
        showErrors,
        submitting: false,
        submitLabel: "Create PDF",

        init() {
            const btn = this.$refs.submit;
            this.submitLabel = (btn?.textContent || "").trim() || "Create PDF";

            window.addEventListener("pageshow", () => this.resetSubmitting());
            document.addEventListener("visibilitychange", () => {
                if (!document.hidden) this.resetSubmitting();
            });
        },

        lineCount() {
            return this.$refs.lines
                ? this.$refs.lines.querySelectorAll(".order-line").length
                : 0;
        },

        setLineNamesAndIds(root, index) {
            const map = {
                name: `lines[${index}][name]`,
                sku: `lines[${index}][sku]`,
                quantity: `lines[${index}][quantity]`,
                ean: `lines[${index}][ean]`,
            };

            Object.entries(map).forEach(([field, name]) => {
                const input = root.querySelector(`[data-field="${field}"]`);
                if (!input) return;

                const id = `lines_${index}_${field}`;
                input.name = name;
                input.id = id;
            });
        },

        addLine() {
            const template = this.$refs.lineTemplate;
            const lines = this.$refs.lines;
            if (!template || !lines) return;

            const index = Number(this.nextIndex || 0);
            const fragment = template.content.cloneNode(true);
            const lineEl = fragment.firstElementChild;
            if (!lineEl) return;

            this.setLineNamesAndIds(lineEl, index);
            lines.appendChild(fragment);

            this.nextIndex = index + 1;

            const first = lines.querySelector(`#lines_${index}_name`);
            if (first) first.focus();
        },

        onLinesClick(e) {
            const btn = e.target.closest("[data-remove-line]");
            if (!btn) return;
            this.removeLine(btn);
        },

        removeLine(btn) {
            if (this.lineCount() <= 1) return;

            const lineEl = btn.closest(".order-line");
            if (!lineEl) return;

            lineEl.remove();
        },

        setSubmitting() {
            this.showErrors = false;
            if (this.submitting) return;

            this.submitting = true;
            window.setTimeout(() => this.resetSubmitting(), 1500);
        },

        resetSubmitting() {
            this.submitting = false;
        },
    })
);

Alpine.start();
