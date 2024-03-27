<?php
require_once ('vendor/autoload.php');

use Mpdf\Mpdf;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(".env.local");

$connection = new AMQPStreamConnection(
        $_ENV['RABBIT_HOST'], 
        $_ENV['RABBIT_PORT'], 
        $_ENV['RABBIT_USER'], 
        $_ENV['RABBIT_PASSWORD']
    );
$channel = $connection->channel();
$channel->exchange_declare('recept', 'direct', false, true, false);

$channel->queue_declare('receptResponse', false, true, false, false);
$channel->queue_bind('receptResponse', 'recept', 'rres');

$channel->queue_declare('receptRequest', false, true, false, false);
$channel->queue_bind('receptRequest', 'recept', 'rreq');

$callback = function ($msg) use($channel)
{
    $json = json_decode($msg->body, true);

    $country = $json['country'];
    $type = $json['type'];
    $count = $json['count'];
    $telegram_id = $json['telegram_id'];

    $apiKey = $_ENV['API_KEY'];

    // получаем рандомные рецепты
    $response = file_get_contents("https://api.spoonacular.com/recipes/random?include-tags={$country},{$type}&apiKey={$apiKey}&number={$count}");
    $response = json_decode($response, true)['recipes'];

    // $response = ['jsons/recipes6.json', 'jsons/recipes3.json'];
    $recipe_ids = implode(',', array_column($response, 'id'));

    $arRecipes = [];

    // получаем полную информацию о рецептах
    $recipes = file_get_contents("https://api.spoonacular.com/recipes/informationBulk?ids={$recipe_ids}&includeNutrition=true&apiKey={$apiKey}");
    $recipes = json_decode($recipes, true);

    echo "Было(и) получено(ы) {$count} рецепт(ов) по {$country} и {$type} \n";

    foreach($recipes as $item)
    {
        $arRecipes[] = getRecipes($item);
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
    $ftp_file = "recipes_{$timeForFile}.pdf";

    // создание локального файл для сохранения
    $mpdf->Output($ftp_file, 'F');

    // сохранение PDF на FTP сервер
    $ftp = new \FtpClient\FtpClient();
    $ftp->connect($_ENV['FTP_HOST']);
    $ftp->login($_ENV['FTP_USER'], $_ENV['FTP_PASSWORD']);
    $ftp->chdir('lr4');
    $ftp->putFromPath($ftp_file);

    unlink($ftp_file);

    $msg_rres = new AMQPMessage(json_encode([
        'file' => '/lr4/' . $ftp_file,
        'telegram_id' => $telegram_id
    ]));

    $channel->basic_publish($msg_rres, 'recept', 'rres');

    echo "Отправили файл {$ftp_file} \n";
};
  
$channel->basic_consume('receptRequest', '', false, true, false, false, $callback);

try {
    $channel->consume();
} catch (\Throwable $exception) {
    echo $exception->getMessage();
}

$channel->close();
$connection->close();
