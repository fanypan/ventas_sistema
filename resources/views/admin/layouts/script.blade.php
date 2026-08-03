<script>
    localStorage.getItem('theme') == "light" || localStorage.getItem('theme') == null ? $("#icontheme").attr("class","fas fa-sun"):$("#icontheme").attr("class","fas fa-moon");
    document.querySelector('body').classList.add(localStorage.getItem('theme'));
    $(document).ready(function(){
        $("#btntheme").on("click", function(){
            if (localStorage.getItem('theme') == 'light' || localStorage.getItem('theme') == null) {
            localStorage.setItem('theme', 'dark-mode')
            document.querySelector('body').classList.add(localStorage.getItem('theme'));
            document.querySelector('body').classList.remove('light');
            $("#icontheme").attr("class","fas fa-moon");
            }else{
            localStorage.setItem('theme', 'light')
            document.querySelector('body').classList.add(localStorage.getItem('theme'));
            document.querySelector('body').classList.remove('dark-mode');
            $("#icontheme").attr("class","fas fa-sun");
            }
        });

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
    });
</script>