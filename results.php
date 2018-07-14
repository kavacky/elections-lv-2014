<?php

function median($a) {
    sort($a);
    $c = count($a);
    if ($c % 2 ) {
        return $a[(int)floor($c / 2)];
    }
    else {
        return ($a[(int)floor($c / 2)] + $a[(int)floor($c / 2) - 1]) / 2;
    }
}

echo '<pre>';

$genders = [];

$namedays = json_decode(file_get_contents('name_days.json'));
foreach($namedays as $m) {
    foreach ($m as $d) {
        foreach ($d as $n) {
            $genders[$n->name] = $n->sex == 'male' ? 'm' : 'f';
        }
    }
}
$genders['ūve'] = 'm';
$genders['romeks'] = 'm';
$genders['hosams'] = 'm';
$genders['vugars'] = 'm';
$genders['nelli'] = 'f';
$genders['nazira'] = 'f';
$genders['taisja'] = 'f';
$genders['gagiks'] = 'm';
$genders['ašots'] = 'm';

$xml = new SimpleXMLElement(file_get_contents('results.xml'));

$candidates = [];
$males = 0;
$females = 0;
$males_plus = 0;
$females_plus = 0;
$males_minus = 0;
$females_minus = 0;
$males_pluses = [];
$females_pluses = [];
$males_minuses = [];
$females_minuses = [];

foreach ($xml->PamatTabulas->Kandidati->Kandidats as $k) {
    $first_name = mb_strtolower(explode(' ', (string)$k[0]['Vards'])[0]);
    $gender = array_key_exists($first_name, $genders) ? $genders[$first_name] : 'GENDERQUEER';

    if ($gender == 'm') {
        $males++;
    }
    else {
        $females++;
    }

    $candidates[(int)$k[0]['KandID']] = [
        'name' => (string)$k[0]['Vards'],
        'surname' => (string)$k[0]['Uzvards'],
        'gender' => $gender,
        'plus' => 0,
        'minus' => 0,
    ];
}

foreach ($xml->PartijasRegionos->PartReg as $r) {
    foreach ($r->Kandidati->Kandidats as $k) {
        $candidates[(int)$k[0]['KandID']]['plus'] += (int)$k[0]['Plusi'];
        $candidates[(int)$k[0]['KandID']]['minus'] += (int)$k[0]['Svitrojumi'];

        if ($candidates[(int)$k[0]['KandID']]['gender'] == 'm') {
            $males_plus += (int)$k[0]['Plusi'];
            $males_minus += (int)$k[0]['Svitrojumi'];
            $males_pluses[] = (int)$k[0]['Plusi'];
            $males_minuses[] = (int)$k[0]['Svitrojumi'];
        }
        else {
            $females_plus += (int)$k[0]['Plusi'];
            $females_minus += (int)$k[0]['Svitrojumi'];
            $females_pluses[] = (int)$k[0]['Plusi'];
            $females_minuses[] = (int)$k[0]['Svitrojumi'];
        }
    }
}

echo 'm: ', $males, ' +', $males_plus, ' (~', round($males_plus / $males), ' median: ', round(median($males_pluses)), '); -', $males_minus, ' (~',
    round($males_minus / $males), ' median: ', round(median($males_minuses)), '); ratio: ', round($males_plus / $males_minus, 2),
    '; ~Δ: ', round($males_plus / $males - $males_minus / $males), '; activity: ~', round(($males_plus + $males_minus) / $males), '<br>';

echo 'f: ', $females, '  +', $females_plus, ' (~', round($females_plus / $females), ' median: ', round(median($females_pluses)), '); -', $females_minus, ' (~',
round($females_minus / $females), ' median: ', round(median($females_minuses)), '); ratio: ', round($females_plus / $females_minus, 2),
'; ~Δ: ', round($females_plus / $females - $females_minus / $females), '; activity: ~', round(($females_plus + $females_minus) / $females), '<br>';

//print_r($candidates);
//print_r($xml->PamatTabulas->Kandidati);
//print_r($xml->PartijasRegionos);
