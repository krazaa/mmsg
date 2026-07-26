

import Alpine from 'alpinejs';
import { Passkeys } from '@laravel/passkeys';
import $ from 'jquery';
import attachSelect2 from 'select2';

window.Alpine = Alpine;
window.$ = window.jQuery = $;
attachSelect2(window, $);

Alpine.start();

document.querySelectorAll('[data-allotment-selects]').forEach((form) => {
    const project = $(form).find('[data-project-select]');
    const booking = $(form).find('[data-booking-select]');
    const plot = $(form).find('[data-plot-select]');
    project.select2({
        width: '100%',
        placeholder: 'Search project',
        allowClear: true,
    });
    booking.select2({
        width: '100%',
        placeholder: 'Search active booking',
        allowClear: true,
        matcher: (params, data) => {
            if (!data.id) return data;
            if (data.element?.dataset.project !== String(project.val() || '')) return null;
            if (!params.term || data.text.toLowerCase().includes(params.term.toLowerCase())) return data;
            return null;
        },
    });
    plot.select2({
        width: '100%',
        placeholder: 'Search matching plot',
        allowClear: true,
        matcher: (params, data) => {
            if (!data.id) return data;
            if (data.element?.dataset.booking !== String(booking.val() || '')) return null;
            if (!params.term || data.text.toLowerCase().includes(params.term.toLowerCase())) return data;
            return null;
        },
    });

    const filterBookings = () => {
        const projectId = String(project.val() || '');
        booking.val(null).prop('disabled', !projectId).trigger('change');
    };

    const filterPlots = () => {
        const bookingId = String(booking.val() || '');
        plot.val(null).prop('disabled', !bookingId).trigger('change');
    };

    project.on('change', filterBookings);
    booking.on('change', filterPlots);
    filterBookings();
    filterPlots();
});

const bookingFilterSelect = $('[data-booking-filter-select]').select2({
    width: '100%',
    placeholder: 'Search booking or customer',
    allowClear: false,
});
bookingFilterSelect.on('select2:open', () => {
    window.setTimeout(() => {
        document.querySelector('.select2-container--open .select2-search__field')?.focus();
    });
});

function showPasskeyMessage(element, message, isError = false) {
    if (!element) return;
    element.textContent = message;
    element.classList.toggle('text-rose-600', isError);
    element.classList.toggle('text-emerald-600', !isError);
    element.hidden = false;
}

document.querySelectorAll('[data-passkey-login]').forEach((button) => {
    const message = document.querySelector(button.dataset.messageTarget);

    if (!Passkeys.isSupported()) {
        button.hidden = true;
        showPasskeyMessage(message, 'Passkeys are not supported by this browser.', true);
        return;
    }

    button.addEventListener('click', async () => {
        const original = button.innerHTML;
        button.disabled = true;
        button.textContent = 'Checking your passkey…';

        try {
            const response = await Passkeys.verify({
                routes: {
                    options: button.dataset.optionsUrl,
                    submit: button.dataset.submitUrl,
                },
            });
            window.location.assign(response.redirect || button.dataset.fallbackUrl);
        } catch (error) {
            showPasskeyMessage(message, error.message || 'Passkey sign-in was not completed.', true);
            button.disabled = false;
            button.innerHTML = original;
        }
    });
});

document.querySelectorAll('[data-passkey-register]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    const input = form.querySelector('input[name="passkey_name"]');
    const message = form.querySelector('[data-passkey-message]');

    if (!Passkeys.isSupported()) {
        button.disabled = true;
        showPasskeyMessage(message, 'This browser or device does not support passkeys.', true);
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const name = input.value.trim();
        if (!name) {
            showPasskeyMessage(message, 'Enter a name for this device.', true);
            input.focus();
            return;
        }

        const original = button.textContent;
        button.disabled = true;
        button.textContent = 'Creating passkey…';

        try {
            await Passkeys.register({
                name,
                routes: {
                    options: form.dataset.optionsUrl,
                    submit: form.dataset.submitUrl,
                },
            });
            showPasskeyMessage(message, 'Passkey created successfully.');
            window.location.reload();
        } catch (error) {
            showPasskeyMessage(message, error.message || 'The passkey could not be created.', true);
            button.disabled = false;
            button.textContent = original;
        }
    });
});

const MAX_PROOF_BYTES = 300 * 1024;

async function canvasBlob(canvas, quality) {
    return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
}

async function compressPaymentProof(file) {
    if (file.size <= MAX_PROOF_BYTES) return file;
    if (!file.type.startsWith('image/')) {
        throw new Error('PDF payment proofs must already be 300 KB or smaller.');
    }

    const image = await createImageBitmap(file);
    let scale = Math.min(1, 2000 / Math.max(image.width, image.height));
    let quality = 0.86;
    let blob;

    for (let attempt = 0; attempt < 12; attempt += 1) {
        const canvas = document.createElement('canvas');
        canvas.width = Math.max(1, Math.round(image.width * scale));
        canvas.height = Math.max(1, Math.round(image.height * scale));
        const context = canvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
        blob = await canvasBlob(canvas, quality);

        if (blob && blob.size <= MAX_PROOF_BYTES) break;
        scale *= 0.82;
        quality = Math.max(0.48, quality - 0.07);
    }

    image.close();
    if (!blob || blob.size > MAX_PROOF_BYTES) {
        throw new Error('This image could not be reduced below 300 KB. Please select a smaller image.');
    }

    const baseName = file.name.replace(/\.[^.]+$/, '');
    return new File([blob], `${baseName}-compressed.jpg`, {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-compress-proof]');
    if (!form || form.dataset.proofReady === 'true') return;

    const input = form.querySelector('input[type="file"][name="proof"]');
    const file = input?.files?.[0];
    if (!file) return;

    event.preventDefault();
    const button = form.querySelector('button[type="submit"], button:not([type])');
    const originalText = button?.textContent;

    try {
        if (button) {
            button.disabled = true;
            button.textContent = 'Submitting…';
        }

        const compressed = await compressPaymentProof(file);
        if (compressed !== file) {
            const transfer = new DataTransfer();
            transfer.items.add(compressed);
            input.files = transfer.files;
        }

        form.dataset.proofReady = 'true';
        form.requestSubmit();
    } catch (error) {
        alert(error.message);
        if (button) {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
});
