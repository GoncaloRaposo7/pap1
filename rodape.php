</div>
        </div>
    </div>
    
    <script>
        // Toggle menu mobile
        function toggleMenu() {
            const sidebar = document.querySelector('.barra-lateral');
            sidebar.classList.toggle('aberta');
        }

        // Confirmar ações
        function confirmarAcao(mensagem) {
            return confirm(mensagem);
        }

        // Fechar alertas automaticamente
        document.addEventListener('DOMContentLoaded', function() {
            const alertas = document.querySelectorAll('.alerta');
            alertas.forEach(function(alerta) {
                setTimeout(function() {
                    alerta.style.transition = 'opacity 0.3s';
                    alerta.style.opacity = '0';
                    setTimeout(function() {
                        alerta.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>