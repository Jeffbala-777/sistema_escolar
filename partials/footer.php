<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const menuToggle = document.getElementById('menuToggle');

    const sidebar = document.getElementById('sidebarMenu');

    const overlay = document.getElementById('sidebarOverlay');

    if (!menuToggle || !sidebar || !overlay) {
        return;
    }

    function abrirMenu() {

        sidebar.classList.add('active');

        overlay.classList.add('active');

        document.body.style.overflow = 'hidden';
    }

    function fecharMenu() {

        sidebar.classList.remove('active');

        overlay.classList.remove('active');

        document.body.style.overflow = '';
    }

    function alternarMenu() {

        if (sidebar.classList.contains('active')) {

            fecharMenu();

        } else {

            abrirMenu();
        }
    }

    menuToggle.addEventListener('click', function (e) {

        e.stopPropagation();

        alternarMenu();
    });

    overlay.addEventListener('click', fecharMenu);

    document.addEventListener('click', function (e) {

        const clicouNoMenu = sidebar.contains(e.target);

        const clicouNoBotao = menuToggle.contains(e.target);

        if (!clicouNoMenu && !clicouNoBotao) {

            fecharMenu();
        }
    });

    window.addEventListener('resize', function () {

        if (window.innerWidth > 991) {

            fecharMenu();
        }
    });

});

</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var cpfInputs = document.querySelectorAll('input[name="cpf"]');
        cpfInputs.forEach(function(input) {
            VMasker(input).maskPattern('999.999.999-99');
        });

        var telefoneInputs = document.querySelectorAll('input[name="telefone"]');
        telefoneInputs.forEach(function(input) {
            VMasker(input).maskPattern('(99) 99999-9999');
        });
    });
</script>
</body>
</html>
