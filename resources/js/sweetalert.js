const normalizeDetail = (detail) => {
    if (Array.isArray(detail)) {
        return detail[0] ?? {};
    }

    if (typeof detail === 'string') {
        try {
            return JSON.parse(detail);
        } catch (error) {
            console.warn('Não foi possível converter o detail recebido em JSON.', error, detail);
            return {};
        }
    }

    return detail ?? {};
};

const showAlert = (detail = {}) => {
    if (typeof window.Swal === 'undefined') {
        console.warn('SweetAlert2 não está disponível.');
        return;
    }

    const options = normalizeDetail(detail);

    const {
        type = 'info',
        icon = null,
        title = '',
        text = '',
        toast = false,
        position = 'top-end',
        timer = toast ? 4000 : undefined,
        confirmButtonText = 'OK',
        showConfirmButton = !toast,
    } = options;

    window.Swal.fire({
        icon: icon ?? type,
        title,
        text,
        toast,
        position,
        timer,
        timerProgressBar: toast,
        showConfirmButton,
        confirmButtonText,
    });
};

const handleConfirm = (detail = {}) => {
    if (typeof window.Swal === 'undefined') {
        console.warn('SweetAlert2 não está disponível.');
        return;
    }

    const options = normalizeDetail(detail);

    const {
        title = 'Tem certeza?',
        text = '',
        icon = 'warning',
        confirmButtonText = 'Sim',
        cancelButtonText = 'Cancelar',
        confirmButtonColor = undefined,
        callback = null,
        payload: rawPayload = undefined,
        componentId = null,
        toast = false,
    } = options;

    const dispatchCallback = (cb, payload) => {
        if (!cb) {
            return;
        }

        if (componentId) {
            const component = Livewire.find(componentId);
            if (component) {
                if (typeof payload === 'undefined') {
                    component.call(cb);
                } else {
                    component.call(cb, payload);
                }
                return;
            }
        }

        if (typeof payload === 'undefined') {
            Livewire.dispatch(cb);
        } else {
            Livewire.dispatch(cb, payload);
        }
    };

    window.Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        confirmButtonColor,
        reverseButtons: true,
        toast,
    }).then((result) => {
        if (result.isConfirmed) {
            dispatchCallback(callback, rawPayload);
        }
    });
};

window.addEventListener('sweet-alert', (event) => showAlert(event.detail));
window.addEventListener('sweet-confirm', (event) => handleConfirm(event.detail));

document.addEventListener('livewire:init', () => {
    Livewire.on('sweet-alert', (detail) => showAlert(detail));
    Livewire.on('sweet-confirm', (detail) => handleConfirm(detail));
});

window.showSweetAlert = showAlert;
window.showSweetConfirm = handleConfirm;
