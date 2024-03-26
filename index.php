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
        'type' => 'dessert',
        'count' => 2,
    ];

    $country = $json['country'];
    $type = $json['type'];
    $count = $json['count'];

    $apiKey = $_ENV['API_KEY'];

    $response = file_get_contents("https://api.spoonacular.com/recipes/random?include-tags={$country},{$type}&apiKey={$apiKey}&number={$count}");
    // $response = file_get_contents('jsons/random.json');
    $response = json_decode($response, true)['recipes'];

    $arRecipes = [];

    foreach($response as $item)
    {
        $recipes = file_get_contents("https://api.spoonacular.com/recipes/{$item['id']}/information?includeNutrition=true&addTasteData=true&apiKey={$apiKey}");
        // $recipes = file_get_contents($item);
        $recipes = json_decode($recipes, true);

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
    }

    $timeForFile = time();
    $ftp_file = "filename_{$timeForFile}.pdf";

    // создание локального файл для сохранения
    $mpdf->Output($ftp_file, 'F');

    // сохранение PDF на FTP сервер
    $ftp_server = $_ENV['FTP_HOST'];  
    $ftp_user_name = $_ENV['FTP_USER']; 
    $ftp_user_pass = $_ENV['FTP_PASSWORD']; 

    $ftp = new \FtpClient\FtpClient();
    $ftp->connect($_ENV['FTP_HOST']);
    $ftp->login($_ENV['FTP_USER'], $_ENV['FTP_PASSWORD']);
    $ftp->chdir('lr4');
    $ftp->putFromPath($ftp_file);

    unlink($ftp_file);
    
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
