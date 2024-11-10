<?php
require __DIR__ . "/vendor/autoload.php";
use GuzzleHttp\Client;
$client = new Client([
    'verify' => false,
    'timeout' => 20,
]);
$pages_array = [];
$url = 'https://www.komod43.ru/catalog/zhenskaya-shorty.html';
$response = $client->request('GET', $url);
$html = $response->getBody()->getContents();
// Создаем новый объект DOMDocument
$dom = new DOMDocument;
// Загружаем HTML
libxml_use_internal_errors(true); // Игнорируем ошибки парсинга
$dom->loadHTML($html);
libxml_clear_errors();
// Создаем новый объект DOMXPath
$xpath = new DOMXPath($dom);
// Ищем все элементы с классом "col-xs-12 text-center"
$elements = $xpath->query('//div[@class="text-right"]');
// Массив для хранения href
$hrefs = [];
if ($elements->length > 0) {
    // Проходим по всем найденным элементам и извлекаем href
    foreach ($elements as $element) {
        // Ищем все ссылки внутри текущего элемента
        $links = $xpath->query('.//a', $element);
        foreach ($links as $link) {
            $hrefs[] = $link->getAttribute('href');
        }
    }
    $hrefs = array_unique($hrefs);
    // Выводим все href
    foreach ($hrefs as $href) {
        echo $href . "\n";
    }
    echo count($hrefs);
} else {
    echo "Элемент не найден.";
}
?>

