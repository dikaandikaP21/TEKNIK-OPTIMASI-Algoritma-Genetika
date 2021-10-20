<?php

use Catalogue as GlobalCatalogue;

class Parameters
{
    const file_name = 'products.txt';
    const columns = ['item', 'price'];
    const population_size = 10;
    const budget = 280000;
    const stopping_value = 10000;
}


class Catalogue
{
    function createProductColumn($listOfRawProduct)
    {
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey) {
            $listOfRawProduct[Parameters::columns[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product()
    {
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::file_name);
        foreach ($raw_data as $listOfRawProduct) {
            $collectionOfListProduct[] = $this->createProductColumn(explode(',', $listOfRawProduct));
        }
        // foreach ($collectionOfListProduct as $listOfRawProduct) {
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }
        return $collectionOfListProduct;
    }
}

class Individu
{
    function countNumberOfGen()
    {
        $catalog = new Catalogue;
        return count($catalog->product());
    }

    function createRandomIndividu()
    {

        for ($i = 0; $i < $this->countNumberOfGen(); $i++) {
            $ret[] = rand(0, 1);
        }
        return $ret;
    }
}

class Population
{

    function createPopulationRandom()
    {
        $individu = new Individu;
        for ($i = 0; $i < Parameters::population_size; $i++) {
            $ret[] = $individu->createRandomIndividu();
        }
        // $n = 1;
        // foreach ($ret as $key => $val) {
        //     //echo 'individu' . $n++ . " ";
        //     print_r($val);
        //     echo '<br>';
        // }
        return $ret;
    }
}

class Fitness
{
    function selectingItem($individu)
    {
        $catalog = new Catalogue;
        foreach ($individu as $individuKey => $binaryGen) {
            if ($binaryGen === 1) {
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalog->product()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }

    function calculateFitnessValue($individu)
    {
        return  array_sum(array_column($this->selectingItem($individu), 'selectedPrice'));
    }

    function countSelectedItem($individu)
    {
        return count($this->selectingItem($individu));
    }

    function isFit($fitnessValue)
    {
        if ($fitnessValue <= Parameters::budget) {
            return TRUE;
        }
    }

    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)
    {
        if ($numberOfIndividuHasMaxItem === 1) {
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            // print_r($fits[$index]);
            return $fits[$index];
        } else {
            foreach ($fits as $key => $val) {
                if ($val['numberOfSelectedItem'] === $maxItem) {
                    echo $val['selectedIndividuKey'] . ' - ' . $val['fitnessValue'] . '<br>';
                    $ret[] = [
                        'individuKey' => $val['selectedIndividuKey'],
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }
            if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1) {
                $index = rand(0, count($ret) - 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }

            //echo '<br> Hasil ';
            //  print_r($ret[$index]);
            return ($ret[$index]);
        }
    }

    function isFound($fits)
    {
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r($countedMaxItems);
        echo '<br>';
        $maxItem =  max(array_keys($countedMaxItems));
        echo $maxItem;
        echo '<br>';
        printf('jumlah individu yg memiliki value ItemMax : ' . $countedMaxItems[$maxItem] . ' individu');
        echo '<br>';
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];

        //print_r($bestFitnessValue['fitnessValue']);
        echo '<br> Best fitness value : ' . $bestFitnessValue;
        echo '<br>';
        $residual = Parameters::budget - $bestFitnessValue;
        echo 'Residual : ' . $residual;

        if ($residual <= Parameters::stopping_value && $residual > 0) {
            return TRUE;
        }
        // return $maxItem;
    }

    function fitnessEvaluation($initialPopulation)
    {
        $catalog = new Catalogue;
        foreach ($initialPopulation as $listOfIndividuKey => $listOfIndividu) {
            echo 'Individu-' . $listOfIndividuKey . '<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen) {
                print_r($binaryGen . '&nbsp;&nbsp');
                print_r($catalog->product()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item : ' . $numberOfSelectedItem;
            echo ' Fitness Value : ' . $fitnessValue;
            if ($this->isFit($fitnessValue)) {
                echo ' (fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits);
            } else {
                echo ' (not fit)';
            }

            echo '<br>';
        }
        if ($this->isFound($fits)) {
            echo '  FOUND';
        } else {
            echo '  >> Next Generation';
        }
    }
}

// $catalog = new Catalogue;
// $catalog->product($parameters);

$initialPopulation  = new Population;
$population = $initialPopulation->createPopulationRandom();

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);
// $individu = new Individu;
// print_r($individu->createRandomIndividu());
