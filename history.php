<?php
include __DIR__ . '/core/init.php';
include __DIR__ . '/src/header.php';

\Manager\CoinManager::importHistory();
?>

<?php
include __DIR__ . '/src/footer.php';
?>
