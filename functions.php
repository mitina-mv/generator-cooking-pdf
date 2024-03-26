<?php
use \Dejurin\GoogleTranslateForFree;

function getRecipes($recipes)
{
    $result = [];

    $source = 'en';
    $target = 'ru';
    $attempts = 5;
    $tr = new GoogleTranslateForFree();

    // собираем список ингридиентов
    $ingredients = [];
    foreach($recipes['extendedIngredients'] as $ing)
    {
        $name = $tr->translate($source, $target, $ing['nameClean'], $attempts);
        $measure = MEASURES[$ing['unit']] ?: $tr->translate($source, $target, $ing['unit'], $attempts);
        $ingredients[] = "{$name} - {$ing['amount']} {$measure}";
    }

    // показатели блюда
    $nutrients = [];
    foreach($recipes['nutrition']['nutrients'] as $nut)
    {
        $name = NUTRIENTS[$nut['name']];
        $measure = MEASURES[$nut['unit']] ?: $nut['unit'];

        $nutrients[] = "<b>{$name}:</b> {$nut['amount']}{$measure} ({$nut['percentOfDailyNeeds']}%)*";
    }

    $summary = strip_tags($recipes['summary']);

    return [
        'title' => $tr->translate($source, $target, $recipes['title'], $attempts),
        'image' => $recipes['image'],
        'recipes' => $tr->translate($source, $target, $summary, $attempts),
        'nutrition' => $nutrients,
        'ingredients' => $ingredients,
        'minutes' => $recipes['readyInMinutes'],
        'taste' => $recipes['taste']?:[],
        'instructions' => $recipes['instructions'] ? $tr->translate($source, $target, $recipes['instructions'], $attempts) : 'Не указано',
    ];
}

function getHtml($recipes)
{
    $html = '';

    $html .= "<img src='{$recipes['image']}' /><br />";
    $html .= "<h1>{$recipes['title']}</h1>";
    $html .= "<p style='color: #fff; background-color: red; padding: 10px 16px;'>Время приготовления: {$recipes['minutes']} мин.</p>";

    if($recipes['taste'])
    {
        $html .= "<h3>Вкусовые характеристики:</h3>";
        $html .= "<table style='font-size: 12px; margin-bottom: 10px;'>";
        $html .= "<tr>";
    
        // Разбиваем массив на части по 4 элемента
        $taste_chunks = array_chunk($recipes['taste'], 4, true);
    
        // Для каждого куска элементов создаем строку таблицы
        foreach ($taste_chunks as $chunk) {
            $html .= "<tr>";
            foreach ($chunk as $key => $value) {
                $name = TASTE[$key];
                $html .= "<td>{$name}: $value%</td>";
            }
            $html .= "</tr>";
        }
    
        $html .= "</table>";
    }

    $html .= "<div><b>Описание:</b> {$recipes['recipes']}</div>";

    $html .= "<h3>Ингредиенты:</h3>";
    $html .= implode('<br />', $recipes['ingredients']);

    $html .= "<h3>Инструкция по приготовлению:</h3>";
    $html .= "<div>{$recipes['instructions']}</div>";

    if($recipes['nutrition'])
    {
        $html .= "<h3>Питательная ценность:</h3>";
        $html .= "<table style='font-size: 10px;'>";
        $html .= "<tr>";
    
        $chunks = array_chunk($recipes['nutrition'], 3);
    
        foreach ($chunks as $chunk) {
            $html .= "<tr>";
            foreach ($chunk as $item) {
                $html .= "<td>$item</td>";
            }
            $html .= "</tr>";
        }
    
        $html .= "</table>";
    }

    return $html;
}
