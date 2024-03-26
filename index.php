<?php
require_once ('vendor/autoload.php');

use Mpdf\Mpdf;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(".env.local");

// $connection = new AMQPStreamConnection('otisnth.ru', 5672, 'test', 'test');
// $channel = $connection->channel();

// $callback = function ($msg) 
// {
    // $json = json_decode($msg->body, true);

    $json = [
        'country' => 'Greek',
        'type' => 'salad',
        'count' => 2,
    ];

    $country = $json['country'];
    $type = $json['type'];
    $count = $json['count'];

    $apiKey = $_ENV['API_KEY'];

    // $response = file_get_contents("https://api.spoonacular.com/recipes/random?include-tags={$country},{$type}&apiKey={$apiKey}&number={$count}");
    // $response = file_get_contents('random.json');
    // $response = json_decode($response, true)['recipes'];

    // получение рандомного рецепта
    // https://api.spoonacular.com/recipes/random?number={$count}&include-tags=vegetarian,dessert&exclude-tags=quinoa

/*     $arRecipes = [];

    foreach($response as $recipes)
    {
        // $recipes = file_get_contents("https://api.spoonacular.com/recipes/{$item['id']}/information?includeNutrition=true&addTasteData=true&addWinePairing=true&apiKey={$apiKey}");
        // $recipes = file_get_contents($item);
        // $recipes = json_decode($recipes, true);

        $arRecipes[] = getRecipes($recipes);
    }

    // генерация пдф
    $mpdf = new Mpdf();
    $total_recipes = count($arRecipes);
    $current_recipe_index = 1;

    foreach($arRecipes as $recipes)
    {
        $html = getHtml($recipes);
        $mpdf->WriteHTML($html);
        if ($current_recipe_index < $total_recipes) {
            $mpdf->WriteHTML("<pagebreak/>");
        }
        $current_recipe_index++;
    } */

    $mpdf = new Mpdf();

    // сохранение PDF на FTP сервер
    $ftp_server = $_ENV['FTP_HOST'];  
    $ftp_user_name = $_ENV['FTP_USER']; 
    $ftp_user_pass = $_ENV['FTP_PASSWORD']; 

/*     $timeForFile = time();
    // $ftp_file = "filename_{$timeForFile}.pdf";
    $ftp_file = "filename.pdf";

    // создание локального файл для сохранения
    $file = "filename.pdf";
    echo $file;
    $mpdf->Output($file, 'F');

    // установка соединения с FTP сервером
    $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    ftp_pasv($conn_id, true);

    // загрузка файла на FTP сервер
    if (ftp_put($conn_id, basename($file), $file, FTP_ASCII)) {
        echo 'Файл успешно загружен';
    } else {
        echo 'Ошибка при загрузке файла на FTP сервер: ' . ftp_last_error($conn_id);
    }

    // закрытие соединения
    ftp_close($conn_id);

    // удаление временный файл
    unlink($file); */

    // Установка соединения
    $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    ftp_pasv($conn_id, true);
    ftp_set_option($conn_id, FTP_USEPASVADDRESS, false);

    if (!$login_result) exit("Ошибка подключения");

        // Полный путь к файлу, который вы хотите загрузить
        $file_to_upload = "jsons/random.json";

        // Попытка асинхронной загрузки файла на FTP сервер
        $put_result = ftp_nb_put($conn_id, "random.json", $file_to_upload, FTP_BINARY);
    
        while ($put_result == FTP_MOREDATA) {
            // Продолжаем загрузку
            $put_result = ftp_nb_continue($conn_id);
        }
    
        if ($put_result == FTP_FINISHED) {
            echo "Файл успешно загружен";
        } elseif ($put_result == FTP_FAILED) {
            echo "Ошибка при загрузке файла на FTP сервер";
        }

    // ftp_chdir($conn_id, "lr4");

    // $text = 'Содержимое файла file.txt';
    // $file = fopen('php://temp', 'r+');
    // fwrite($file, $text);
    // rewind($file);
    
    // if (ftp_fput($conn_id, 'file.txt', $file, FTP_ASCII)) {
    //     echo 'Файл создан';
    // } else {
    //     echo 'Не удалось создать файл';
    // }
    
/*     ftp_mkdir($conn_id, "tyk");
    // Полный путь к файлу, который вы хотите загрузить
    $file_to_upload = __DIR__ . "/doc.xml";

    // Попытка асинхронной загрузки файла на FTP сервер
    $put_result = ftp_nb_put($conn_id, "new_doc.xml", $file_to_upload, FTP_BINARY);

    while ($put_result == FTP_MOREDATA) {
        // Продолжаем загрузку
        $put_result = ftp_nb_continue($conn_id);
    }

    if ($put_result == FTP_FINISHED) {
        echo "Файл успешно загружен";
    } elseif ($put_result == FTP_FAILED) {
        echo "Ошибка при загрузке файла на FTP сервер";
    }
    // Получить содержимое директории
    $contents = ftp_nlist($conn_id, '.');
    
    print_r($contents);
    
    // Закрытие соединения
    ftp_close($conn_id); */
    
    /* 
    telegram_id
    country
    type
    count
    */
    // echo ' [x] Received ', $msg->body, "\n";
    // echo "ff";
// };
  
// $channel->basic_consume('dick', '', false, true, false, false, $callback);

// try {
//     $channel->consume();
// } catch (\Throwable $exception) {
//     echo $exception->getMessage();
// }
