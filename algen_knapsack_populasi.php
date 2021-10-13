<?php

class Catalogue
{
    function createProductColumn($columns, $listOfRawProduct)
    {
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey) {
            $listOfRawProduct[$columns[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product($parameters)
    {
        $collectionOfListProduct = [];

        $raw_data = file($parameters['file_name']);
        foreach ($raw_data as $listOfRawProduct) {
            $collectionOfListProduct[] = $this->createProductColumn($parameters['columns'], explode(',', $listOfRawProduct));
        }
        // foreach ($collectionOfListProduct as $listOfRawProduct) {
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }
        return [
            'product' => $collectionOfListProduct,
            'gen_length' => count($collectionOfListProduct)
        ];
    }
}

class PopulationGenerator
{
    function createIndividu($parameters)
    {
        $catalogue = new Catalogue;
        $lenghtOfGen = $catalogue->product($parameters)['gen_length'];
        for ($i = 0; $i < $lenghtOfGen; $i++) {
            $ret[] = rand(0, 1);
        }
        return $ret;
    }
    function createPopulation($parameters)
    {

        for ($i = 0; $i < $parameters['population_size']; $i++) {
            $ret[] = $this->createIndividu($parameters);
        }
        $n = 1;
        foreach ($ret as $key => $val) {
            echo 'individu' . $n++ . " ";
            print_r($val);
            echo '<br>';
        }
    }
}

$parameters = [
    'file_name' => 'products.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];

$catalog = new Catalogue;
$catalog->product($parameters);

$initialPopulation  = new PopulationGenerator;
$initialPopulation->createPopulation($parameters);
