<?php
require_once ('vendor/autoload.php');

use Mpdf\Mpdf;

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

    $response = file_get_contents("https://api.spoonacular.com/recipes/random?number=1&include-tags={$country},{$type}&apiKey=8d06c13886994874a7f127746d9c0b64&number={$count}");
    $response = json_decode($response, true)['recipes'];

    // получение рандомного рецепта
    // https://api.spoonacular.com/recipes/random?number=1&include-tags=vegetarian,dessert&exclude-tags=quinoa

    // $response = ['recipes5.json', 'recipes3.json'];

    $arRecipes = [];

    foreach($response as $recipes)
    {
        // $recipes = file_get_contents("https://api.spoonacular.com/recipes/{$item['id']}/information?includeNutrition=true&addTasteData=true&addWinePairing=true&apiKey=8d06c13886994874a7f127746d9c0b64");
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
    }
    $mpdf->Output('filename.pdf','F');
    
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
