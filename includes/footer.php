<footer class="main-footer">
    <div class="container">
        <div class="footer-section links">
            <ul>
                <li><a href="/index.php">Início</a></li>

                <?php if (isset($_SESSION['user_id'])) : ?>
                    <?php if ($_SESSION['user_tipo'] == 1) : // TIPO ALUNO 
                    ?>
                        <li><a href="/aluno/index.php">Meu Painel</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 2) : // TIPO EMPRESA 
                    ?>
                        <li><a href="/empresa/index.php">Meu Painel</a></li>
                    <?php elseif ($_SESSION['user_tipo'] == 3) : // TIPO ADMIN 
                    ?>
                        <li><a href="/admin/index.php">Dashboard</a></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <li><a href="/vagas.php">Vagas</a></li>
                <li><a href="/sobre.php">Sobre</a></li>
                <li><a href="/contato.php">Contato</a></li>
            </ul>
        </div>
        <hr>
        <div class="footer-bottom">
            &copy; <?php echo date("Y"); ?> Banco de Talentos BSI - IFBA. Todos os direitos reservados.
        </div>
    </div>
</footer>

</div>

<script src="/assets/js/data-theme.js"></script>
</body>
</html>