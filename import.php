<?php
include __DIR__ . '/core/init.php';
include __DIR__ . '/src/header.php';

\Manager\ImportManager::importCoinList();
?>

<?php
include __DIR__ . '/src/footer.php';
?>
