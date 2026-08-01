<script>
document.addEventListener('DOMContentLoaded', function () {
    const desde = document.querySelector('input[name="desde"]');
    const hasta = document.querySelector('input[name="hasta"]');
    if (!desde || !hasta) return;

    const sincronizar = () => {
        if (desde.value) hasta.min = desde.value;
        if (hasta.value) desde.max = hasta.value;
        if (desde.value && hasta.value && hasta.value < desde.value) {
            hasta.value = desde.value;
        }
    };

    desde.addEventListener('change', sincronizar);
    hasta.addEventListener('change', sincronizar);
    sincronizar();

    document.querySelectorAll('[data-rango]').forEach(boton => {
        boton.addEventListener('click', () => {
            const hoy = new Date();
            const formato = d => {
                const dd = String(d.getDate()).padStart(2, '0');
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                return d.getFullYear() + '-' + mm + '-' + dd;
            };
            let inicio = formato(hoy);
            const fin = inicio;
            if (boton.dataset.rango === 'semana') {
                const d = new Date(hoy);
                const dia = d.getDay();
                d.setDate(d.getDate() - (dia === 0 ? 6 : dia - 1));
                inicio = formato(d);
            } else if (boton.dataset.rango === 'mes') {
                inicio = formato(new Date(hoy.getFullYear(), hoy.getMonth(), 1));
            }
            desde.value = inicio;
            hasta.value = fin;
            sincronizar();
            boton.closest('form').submit();
        });
    });
});
</script>
