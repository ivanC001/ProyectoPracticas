(function () {

    const token = localStorage.getItem('token');

    // 🔥 CONFIG GLOBAL JQUERY
    if (token) {
        $.ajaxSetup({
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json"
            }
        });
    }

    // 🔥 FETCH GLOBAL MEJORADO
    window.apiFetch = async function (url, options = {}) {

        const token = localStorage.getItem('token');

        const response = await fetch(url, {
            ...options,
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json", // 🔥 CLAVE
                "Authorization": "Bearer " + token,
                ...(options.headers || {})
            }
        });

        // 🔥 SI TOKEN EXPIRÓ
        if (response.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return;
        }

        // 🔥 EVITAR ERROR HTML
        const text = await response.text();

        let data;

        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Respuesta no es JSON:", text);
            throw {
                message: "El servidor devolvió una respuesta inválida (HTML)"
            };
        }

        // 🔥 SI ERROR BACKEND
        if (!response.ok) {
            throw data;
        }

        return data;
    };

})();