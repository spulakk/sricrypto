        </section>
    </div>
</body>

<script>
    $(".alert").delay(5000).fadeOut(1000, function() {
        $(this).alert('close');
    });
</script>

</html>

<?php ob_flush(); ?>