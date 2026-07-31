

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const appPreloader = document.getElementById('app-preloader');

if (appPreloader) {
    let hidden = false;
    const preloaderStartedAt = performance.now();
    const minimumDisplayTime = 450;

    const hidePreloader = () => {
        if (hidden) return;
        const remainingTime = minimumDisplayTime - (performance.now() - preloaderStartedAt);

        if (remainingTime > 0) {
            window.setTimeout(hidePreloader, remainingTime);
            return;
        }

        hidden = true;
        appPreloader.classList.add('is-hidden');
        window.setTimeout(() => appPreloader.setAttribute('aria-hidden', 'true'), 200);
    };

    const showPreloader = () => {
        hidden = false;
        appPreloader.removeAttribute('aria-hidden');
        appPreloader.classList.remove('is-hidden');
    };

    if (document.readyState === 'complete') {
        window.requestAnimationFrame(hidePreloader);
    } else {
        window.addEventListener('load', () => window.requestAnimationFrame(hidePreloader), { once: true });
    }

    // Never leave the interface covered if a third-party resource stalls.
    window.setTimeout(hidePreloader, 4000);
    window.addEventListener('beforeunload', showPreloader);
    window.addEventListener('pageshow', hidePreloader);
}

const dataVersionMeta = document.querySelector('meta[name="data-version"]');
const dataVersionUrl = document.querySelector('meta[name="data-version-url"]')?.content;

if (dataVersionMeta && dataVersionUrl) {
    let currentDataVersion = Number(dataVersionMeta.content);
    let formIsDirty = false;
    let polling = false;

    const showRefreshButton = () => {
        if (document.querySelector('[data-new-data-refresh]')) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.newDataRefresh = 'true';
        button.className = 'fixed bottom-5 right-5 z-[200] rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white shadow-2xl hover:bg-indigo-700';
        button.textContent = 'New data available — refresh';
        button.addEventListener('click', () => window.location.reload());
        document.body.appendChild(button);
    };

    document.addEventListener('input', (event) => {
        if (event.target.closest('form')) formIsDirty = true;
    });
    document.addEventListener('change', (event) => {
        if (event.target.closest('form')) formIsDirty = true;
    });
    document.addEventListener('submit', () => {
        formIsDirty = false;
    });

    const pollForChanges = async () => {
        if (polling || document.visibilityState !== 'visible') return;
        polling = true;

        try {
            const response = await fetch(dataVersionUrl, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) return;

            const { version } = await response.json();
            if (Number(version) <= currentDataVersion) return;
            currentDataVersion = Number(version);

            const editing = formIsDirty
                || document.activeElement?.matches('input, textarea, select, [contenteditable="true"]');
            if (editing) {
                showRefreshButton();
            } else {
                window.location.reload();
            }
        } catch {
            // A temporary network failure should not interrupt the current page.
        } finally {
            polling = false;
        }
    };

    // A one-minute interval keeps shared-hosting traffic light while still
    // surfacing database changes without requiring WebSockets.
    window.setInterval(pollForChanges, 60000);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') pollForChanges();
    });
}

if (document.querySelector('[data-allotment-selects], [data-booking-filter-select]')) {
    Promise.all([import('jquery'), import('select2'), import('select2/dist/css/select2.css')]).then(([jquery, select2]) => {
        const $ = jquery.default;
        window.$ = window.jQuery = $;
        select2.default(window, $);

        document.querySelectorAll('[data-allotment-selects]').forEach((form) => {
            const project = $(form).find('[data-project-select]');
            const booking = $(form).find('[data-booking-select]');
            const plot = $(form).find('[data-plot-select]');
            project.select2({ width: '100%', placeholder: 'Search project', allowClear: true });
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

        const bookingFilter = document.querySelector('[data-booking-filter-select]');
        if (bookingFilter) {
            const bookingFilterSelect = $(bookingFilter).select2({
                width: '100%',
                placeholder: 'Search booking or customer',
                allowClear: false,
            });
            bookingFilterSelect.on('select2:open', () => {
                window.setTimeout(() => {
                    document.querySelector('.select2-container--open .select2-search__field')?.focus();
                });
            });
        }
    });
}

function showPasskeyMessage(element, message, isError = false) {
    if (!element) return;
    element.textContent = message;
    element.classList.toggle('text-rose-600', isError);
    element.classList.toggle('text-emerald-600', !isError);
    element.hidden = false;
}

const passkeyLoginButtons = document.querySelectorAll('[data-passkey-login]');
const passkeyRegisterForms = document.querySelectorAll('[data-passkey-register]');

if (passkeyLoginButtons.length || passkeyRegisterForms.length) {
    import('@laravel/passkeys').then(({ Passkeys }) => {
passkeyLoginButtons.forEach((button) => {
    const message = document.querySelector(button.dataset.messageTarget);

    if (!Passkeys.isSupported()) {
        button.hidden = true;
        showPasskeyMessage(message, 'Passkeys are not supported by this browser.', true);
        return;
    }

    button.addEventListener('click', async () => {
        const original = button.innerHTML;
        message.hidden = true;
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
            const passkeyMissing = error.name === 'UserCancelledError'
                || /not recognized|no passkey|cancelled|JSON\.parse|unexpected character/i.test(error.message || '');
            const errorMessage = passkeyMissing
                ? 'No matching passkey was found. Sign in with your password, then create a passkey from your Profile page.'
                : (error.message || 'Passkey sign-in was not completed.');
            showPasskeyMessage(message, errorMessage, true);
            button.disabled = false;
            button.innerHTML = original;
        }
    });
});

passkeyRegisterForms.forEach((form) => {
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
    });
}

const MAX_PROOF_BYTES = 300 * 1024;

async function canvasBlob(canvas, quality) {
    return new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
}

async function compressPaymentProof(file) {
    if (file.size <= MAX_PROOF_BYTES) return file;
    if (!file.type.startsWith('image/')) {
        throw new Error('PDF payment proofs must already be 300 KB or smaller.');
    }

    let image;
    try {
        image = await createImageBitmap(file);
    } catch {
        throw new Error('Your browser cannot compress this image format. Upload a file below 300 KB or convert it to JPG, PNG or WebP.');
    }
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
    const restoreButton = () => {
        if (!button) return;
        button.disabled = false;
        button.textContent = originalText;
    };

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

        // If native validation prevents the second submission, do not leave
        // the payment button permanently disabled.
        window.setTimeout(() => {
            if (document.visibilityState === 'visible') restoreButton();
        }, 4000);
    } catch (error) {
        alert(error.message || 'The payment proof could not be prepared. Please try again.');
        restoreButton();
    }
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-compress-proof]').forEach((form) => {
        delete form.dataset.proofReady;
        const button = form.querySelector('button[type="submit"], button:not([type])');
        if (button) button.disabled = false;
    });
});
