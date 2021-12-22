<?php
include __DIR__ . '/core/init.php';
include __DIR__ . '/src/header.php';
?>

<canvas id="myChart"></canvas>

<script>
    const ctx = $('#myChart');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: <?php echo strtoupper(jsify(\Manager\CoinManager::getRow(1)['symbol'])) ?>,
                    data: <?php echo jsify(\Manager\CoinManager::getCoinHistory(1, \Dial\CoinStatDial::P)) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 0.8)',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointBorderWidth: 0,
                    pointHoverRadius: 1,
                    pointHoverBorderWidth: 0
                },
                {
                    label: <?php echo strtoupper(jsify(\Manager\CoinManager::getRow(2)['symbol'])) ?>,
                    data: <?php echo jsify(\Manager\CoinManager::getCoinHistory(2, \Dial\CoinStatDial::P)) ?>,
                    backgroundColor: 'rgba(12, 255, 89, 0.8)',
                    borderColor: 'rgba(12, 255, 89, 0.8)',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointBorderWidth: 0,
                    pointHoverRadius: 1,
                    pointHoverBorderWidth: 0
                }
            ]
        },
        options: {
            interaction: {
                mode: 'index',
                intersect: false
            }
        }
    });
</script>

<?php
include __DIR__ . '/src/footer.php';
?>
