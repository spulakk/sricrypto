<?php
include __DIR__ . '/core/init.php';
include __DIR__ . '/src/header.php';
?>

<form method="get" class="my-4">
    <div class="row">
        <div class="col-12 col-sm-5">
            <label for="dateStart" class="form-label">Start:</label>
            <input type="date" id="dateStart" name="dateStart" class="form-control">
        </div>
        <div class="col-12 col-sm-5 mt-2 mt-sm-0">
            <label for="dateEnd" class="form-label">End:</label>
            <input type="date" id="dateEnd" name="dateEnd" class="form-control">
        </div>
        <div class="col-12 col-sm-2" style="margin-top: 32px">
            <input type="submit" class="btn btn-primary" value="Submit">
        </div>
    </div>
</form>

<canvas id="myChart"></canvas>

<script>
    const ctx = $('#myChart');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            datasets: [
                {
                    label: <?php echo strtoupper(jsify(\Manager\CoinManager::getCoin(1)['symbol'])) ?>,
                    data: <?php echo jsify(\Manager\ChartManager::baselineData(1, \Dial\CoinStatDial::D, $_GET['dateStart'] ?? null, $_GET['dateEnd'] ?? null)) ?>,
                    backgroundColor: 'rgba(255, 99, 132, 0.8)',
                    borderColor: 'rgba(255, 99, 132, 0.8)',
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
