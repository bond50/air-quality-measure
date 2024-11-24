<?php require __DIR__ . '/inc/functions.inc.php';
$city = null;
if (!empty($_GET['city'])) {
    $city = $_GET['city'];
}
$fileName = null;
if (!empty($city)) {
    $cities = json_decode(
        file_get_contents(
            __DIR__ . "/../data/index.json"
        ), true
    );

    foreach ($cities as $curentCity) {
        if ($curentCity['city'] == $city) {
            $fileName = $curentCity['filename'];
            break;
        }
    }
}
$stats = [];
if (!empty($fileName)) {
    $results = json_decode(
        file_get_contents('compress.bzip2://' . __DIR__ . "/../data/" . $fileName),
        true)['results'];

    foreach ($results as $result) {
        if ($result['parameter'] !== 'pm25') continue;

        $month = substr($result['date']['local'], 0, 7);
        if (!isset($stats[$month])) {
            $stats[$month] = [];
        }
        $stats[$month][] = $result['value'];
    }

}


?>
<?php require __DIR__ . '/views/header.inc.php'; ?>


<?php if (empty($city)) : ?>
    <p>The city could not be loaded</p>
<?php else: ?>
    <?php if (!empty($stats)) : ?>
        <table>
            <?php foreach ($stats as $month => $measurements) : ?>
                <tr>
                    <th><?= e($month) ?></th>
                    <td><?= e(array_sum($measurements) / count($measurements)) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/views/footer.inc.php'; ?>