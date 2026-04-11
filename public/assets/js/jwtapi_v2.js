(function () {
    const token = localStorage.getItem('token');
    const mutatingMethods = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);
    const pendingRequestKeys = new Set();

    function toSafeString(value) {
        if (value === null || value === undefined) {
            return '';
        }

        if (typeof value === 'string') {
            return value;
        }

        if (value instanceof URLSearchParams) {
            return value.toString();
        }

        if (typeof FormData !== 'undefined' && value instanceof FormData) {
            const entries = [];
            value.forEach((entryValue, entryKey) => {
                entries.push(entryKey + ':' + String(entryValue));
            });
            return entries.sort().join('&');
        }

        try {
            return JSON.stringify(value);
        } catch (error) {
            return String(value);
        }
    }

    function buildRequestKey(method, url, payload) {
        return String(method || '').toUpperCase() + '::' + String(url || '') + '::' + toSafeString(payload);
    }

    function getMethodFromFetch(input, init) {
        const requestInit = init || {};
        return String(
            requestInit.method ||
            (input && typeof input === 'object' && input.method) ||
            'GET'
        ).toUpperCase();
    }

    function getUrlFromFetch(input) {
        if (typeof input === 'string') {
            return input;
        }

        if (input && typeof input === 'object' && input.url) {
            return input.url;
        }

        return '';
    }

    function lockButtonTemporarily(button, durationMs) {
        if (!button || button.dataset.doubleLock === '1') {
            return false;
        }

        button.dataset.doubleLock = '1';
        button.disabled = true;

        setTimeout(function () {
            button.disabled = false;
            button.dataset.doubleLock = '0';
        }, durationMs || 1400);

        return true;
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('button');

        if (!button) {
            return;
        }

        // Do not lock modal trigger buttons; Bootstrap needs the click flow intact.
        const opensModal = button.matches('[data-toggle="modal"], [data-bs-toggle="modal"]');
        if (opensModal) {
            return;
        }

        const type = String(button.getAttribute('type') || 'button').toLowerCase();
        const shouldProtect =
            button.dataset.preventDouble !== undefined ||
            type === 'submit' ||
            button.classList.contains('btn-success') ||
            button.classList.contains('btn-danger') ||
            (button.classList.contains('btn-primary') && !!button.closest('form'));

        if (!shouldProtect) {
            return;
        }

        if (button.dataset.doubleLock === '1') {
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        // Apply the lock after the current click flow finishes.
        // This avoids interfering with Bootstrap modal/data-api handlers.
        setTimeout(function () {
            lockButtonTemporarily(button, 1400);
        }, 0);
    }, true);

    if (token) {
        $.ajaxSetup({
            headers: {
                Authorization: 'Bearer ' + token,
                Accept: 'application/json',
            },
        });
    }

    if (window.jQuery && typeof $.ajaxPrefilter === 'function') {
        $.ajaxPrefilter(function (options, originalOptions) {
            const method = String(options.type || options.method || 'GET').toUpperCase();

            if (!mutatingMethods.has(method)) {
                return;
            }

            const key = buildRequestKey(method, options.url, originalOptions.data || options.data);
            const previousBeforeSend = options.beforeSend;
            const previousComplete = options.complete;

            options.beforeSend = function (xhr, settings) {
                if (pendingRequestKeys.has(key)) {
                    return false;
                }

                pendingRequestKeys.add(key);
                xhr.__requestLockKey = key;

                if (typeof previousBeforeSend === 'function') {
                    return previousBeforeSend.call(this, xhr, settings);
                }
            };

            options.complete = function (xhr, status) {
                const lockKey = (xhr && xhr.__requestLockKey) ? xhr.__requestLockKey : key;
                pendingRequestKeys.delete(lockKey);

                if (typeof previousComplete === 'function') {
                    return previousComplete.call(this, xhr, status);
                }
            };
        });
    }

    const nativeFetch = window.fetch.bind(window);

    window.fetch = async function (input, init) {
        const requestInit = init || {};
        const method = getMethodFromFetch(input, requestInit);
        const isMutating = mutatingMethods.has(method);
        const key = isMutating
            ? buildRequestKey(method, getUrlFromFetch(input), requestInit.body)
            : null;

        if (key && pendingRequestKeys.has(key)) {
            const duplicateError = new Error('Solicitud en proceso. Espera un momento.');
            duplicateError.code = 'DUPLICATE_REQUEST';
            throw duplicateError;
        }

        if (key) {
            pendingRequestKeys.add(key);
        }

        try {
            return await nativeFetch(input, requestInit);
        } finally {
            if (key) {
                pendingRequestKeys.delete(key);
            }
        }
    };

    window.apiFetch = async function (url, options) {
        const requestOptions = options || {};
        const authToken = localStorage.getItem('token');

        const response = await fetch(url, {
            ...requestOptions,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                Authorization: 'Bearer ' + authToken,
                ...(requestOptions.headers || {}),
            },
        });

        if (response.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return;
        }

        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no es JSON:', text);
            throw {
                message: 'El servidor devolvio una respuesta invalida (HTML)',
            };
        }

        if (!response.ok) {
            throw data;
        }

        return data;
    };

    window.isDuplicateRequestError = function (error) {
        return !!(error && (error.code === 'DUPLICATE_REQUEST' || error.message === 'Solicitud en proceso. Espera un momento.'));
    };
})();
