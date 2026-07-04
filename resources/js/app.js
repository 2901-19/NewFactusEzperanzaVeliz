import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Alpine from 'alpinejs';
import $ from 'jquery';
import 'datatables.net-bs5';
import Swal from 'sweetalert2';

window.Alpine = Alpine;
window.$ = $;
window.jQuery = $;
window.Swal = Swal;

Alpine.start();

// SweetAlert2 config por defecto
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});
window.Toast = Toast;

// Reemplazar alerts flash nativos por SweetAlert2
document.addEventListener('DOMContentLoaded', function () {
    const successMsg = document.getElementById('flash-success')?.value;
    const errorMsg = document.getElementById('flash-error')?.value;

    if (successMsg) {
        Toast.fire({ icon: 'success', title: successMsg });
    }
    if (errorMsg) {
        Toast.fire({ icon: 'error', title: errorMsg });
    }
});

// DataTables default config
$.extend($.fn.dataTable.defaults, {
    language: {
        url: '//cdn.datatables.net/plug-ins/2.2.2/i18n/es-ES.json',
    },
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    responsive: true,
});
