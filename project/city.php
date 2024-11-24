<?php require __DIR__ . '/inc/functions.inc.php';

$city = null;
if (!empty($_GET['city'])) {
    $city = $_GET['city'];
}

$fileName = null;
$cityInformation = [];

if (!empty($city)) {
    $cities = json_decode(
        file_get_contents(
            __DIR__ . "/../data/index.json"
        ), true
    );

    foreach ($cities as $curentCity) {
        if ($curentCity['city'] == $city) {
            $fileName = $curentCity['filename'];
            $cityInformation = $curentCity;
            break;
        }
    }
}

$stats = [];
if (!empty($fileName)) {
    $results = json_decode(
        file_get_contents('compress.bzip2://' . __DIR__ . "/../data/" . $fileName),
        true)['results'];

    $units = [
        'pm25' => [],
        'pm10' => [],
    ];

    foreach ($results as $result) {
        if (!empty($units['pm25']) && !empty($units['pm10'])) break;
        if ($result['parameter'] === 'pm25') {
            $units['pm25'] = $result['unit'];
        }
        if ($result['parameter'] === 'pm10') {
            $units['pm10'] = $result['unit'];
        }
    }

    foreach ($results as $result) {

        if ($result['parameter'] !== 'pm25' && $result['parameter'] !== 'pm10') continue;

        $month = substr($result['date']['local'], 0, 7);

        if (!isset($stats[$month])) {
            $stats[$month] = [
                'pm25' => [],
                'pm10' => []
            ];
        }
        if ($result['value'] < 0) continue;
        $stats[$month][$result['parameter']][] = $result['value'];
    }

}


?>
<?php require __DIR__ . '/views/header.inc.php'; ?>


<?php if (empty($city)) : ?>
    <p>The city could not be loaded</p>
<?php else: ?>
    <h1><?= e($cityInformation['city']) ?> <?= e($cityInformation['flag']) ?></h1>
    <?php if (!empty($stats)) : ?>
        <canvas id="aqi-chart" style="width: 300px; height: 200px;"></canvas>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const data = {
                    labels: ['Label 01','Label 02','Label 03','Label 04','Label 05','Label 06','Label 07'],
                    datasets: [{
                        label: 'My First Dataset',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                };

                const ctx = document.getElementById('aqi-chart')
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        onClick: (e) => {
                            const canvasPosition = getRelativePosition(e, chart);

                            // Substitute the appropriate scale IDs
                            const dataX = chart.scales.x.getValueForPixel(canvasPosition.x);
                            const dataY = chart.scales.y.getValueForPixel(canvasPosition.y);
                        }
                    }
                });
            })

        </script>

        <table>
            <thead>
            <tr>
                <th>Month</th>
                <th>PM 2.5 concentration</th>
                <th>PM 10 concentration</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($stats as $month => $measurements) : ?>
                <tr>
                    <th><?= e($month) ?></th>
                    <td>
                        <?= e(round(array_sum($measurements['pm25']) / count($measurements['pm25']), 2)) ?>
                        <?= e($units['pm25']) ?>
                    </td>
                    <td>
                        <?= e(round(array_sum($measurements['pm10']) / count($measurements['pm10']), 2)) ?>
                        <?= e($units['pm10']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/views/footer.inc.php'; ?>