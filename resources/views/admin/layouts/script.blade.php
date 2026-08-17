<script>
    $(document).ready(function(){
        $(function () {
            $('.datatable').DataTable({
                "stateSave": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                }
            });
        });

        // --- Lógica de Formateo de Moneda (Gs.) ---
        function formatNumber(n) {
            return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        $(document).on('keyup input', '.currency-format', function() {
            let val = $(this).val();
            $(this).val(formatNumber(val));
        });

        // Función para obtener valor limpio (numérico)
        window.getCleanNumber = function(val) {
            if (typeof val === 'number') return val;
            if (!val) return 0;
            return parseFloat(val.toString().replace(/\./g, '')) || 0;
        };

        // Formatear campos cargados inicialmente
        $('.currency-format').each(function() {
            $(this).val(formatNumber($(this).val()));
        });

        // Limpiar valores formateados antes de enviar cualquier formulario
        $(document).on('submit', 'form', function() {
            $(this).find('.currency-format').each(function() {
                $(this).val(window.getCleanNumber($(this).val()));
            });
        });
    });
</script>