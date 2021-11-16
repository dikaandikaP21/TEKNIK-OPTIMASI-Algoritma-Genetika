<?php

use Catalogue as GlobalCatalogue;

class Parameters
{
    const file_name = 'products.txt';
    const columns = ['item', 'price'];
    const population_size = 30;
    const budget = 280000;
    const stopping_value = 10000;
    const crossover_rate = 0.8;
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
        // print_r($countedMaxItems);
        // echo '<br>';
        $maxItem =  max(array_keys($countedMaxItems));
        // echo $maxItem;
        // echo '<br>';
        // printf('jumlah individu yg memiliki value ItemMax : ' . $countedMaxItems[$maxItem] . ' individu');
        // echo '<br>';
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];

        //print_r($bestFitnessValue['fitnessValue']);
        // echo '<br> Best fitness value : ' . $bestFitnessValue;
        // echo '<br>';
        $residual = Parameters::budget - $bestFitnessValue;
        echo 'Residual : ' . $residual;

        if ($residual <= Parameters::stopping_value && $residual > 0) {
            return TRUE;
        }
        return $maxItem;
    }

    function fitnessEvaluation($initialPopulation)
    {
        $catalog = new Catalogue;
        foreach ($initialPopulation as $listOfIndividuKey => $listOfIndividu) {
            // echo 'Individu-' . $listOfIndividuKey . '<br>';
            // foreach ($listOfIndividu as $individuKey => $binaryGen) {
            //     print_r($binaryGen . '&nbsp;&nbsp');
            //     print_r($catalog->product()[$individuKey]);
            //     echo '<br>';
            // }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item : ' . $numberOfSelectedItem;
            echo ' Fitness Value : ' . $fitnessValue;
            if ($this->isFit($fitnessValue)) {
                // echo ' (fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue
                ];
                // print_r($fits);
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

class Crossover
{
    public $population;

    function __construct($population)
    {
        $this->population = $population;
    }

    function randomZeroToOne()
    {
        return (float) rand() / (float) getrandmax();
    }

    function generateCrossover()
    {

        for ($i = 0; $i < Parameters::population_size; $i++) {
            $randomZeroToOne = $this->randomZeroToOne();
            if ($randomZeroToOne < Parameters::crossover_rate) {
                $parents[$i] = $randomZeroToOne;
            }
        }
        // echo '<br>';
        // print_r($parents);
        foreach (array_keys($parents) as $key) {
            foreach (array_keys($parents) as $subkey) {
                if ($key !== $subkey) {
                    $ret[] = [$key, $subkey];
                }
            }
            array_shift($parents);
        }
        return $ret;
    }

    function offspring($parent1, $parent2, $cutPointIndex, $offspring)
    {
        $lengthOfGen = new Individu;
        if ($offspring === 1) {
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen() - 1; $i++) {
                if ($i <= $cutPointIndex) {
                    $ret[] = $parent1[$i];
                }
                if ($i > $cutPointIndex) {
                    $ret[] = $parent2[$i];
                }
            }
        }

        if ($offspring === 2) {
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen() - 1; $i++) {
                if ($i <= $cutPointIndex) {
                    $ret[] = $parent2[$i];
                }
                if ($i > $cutPointIndex) {
                    $ret[] = $parent1[$i];
                }
            }
        }
        return $ret;
    }

    function cutPointRandom()
    {
        $lengthOfGen = new Individu;
        return rand(0, $lengthOfGen->countNumberOfGen() - 1);
    }

    function crossover()
    {
        $cutPointIndex = $this->cutPointRandom();
        //  echo $cutPointIndex;
        foreach ($this->generateCrossover() as $listOfCrossover) {
            $parent1 = $this->population[$listOfCrossover[0]];
            $parent2 = $this->population[$listOfCrossover[1]];
            // echo '<p></p>';
            // echo 'Parents :<br>';
            // foreach ($parent1 as $gen) {
            //     echo $gen;
            // }
            // echo '><';
            // foreach ($parent2 as $gen) {
            //     echo $gen;
            // }
            // echo '<br>';

            // echo 'Offspringg<br>';
            $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
            $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
            // foreach ($offspring1 as $gen) {
            //     echo $gen;
            // }
            // echo '><';
            // foreach ($offspring2 as $gen) {
            //     echo $gen;
            // }
            // echo '<br>';

            $offsprings[] = $offspring1;
            $offsprings[] = $offspring2;
        }
        return $offsprings;
    }
}

class Randomizer
{
    static function getRandomIndexOfGen()
    {
        return rand(0, (new Individu())->countNumberOfGen() - 1);
    }
    static function getRandomIndexOfIndividu()
    {
        return rand(0, Parameters::population_size - 1);
    }
}

class Mutation
{
    protected $population;
    function __construct($population)
    {
        $this->population = $population;
    }

    function calculateMutationRate()
    {
        return 1 / (new Individu())->countNumberOfGen();
    }

    function calculateNumOfMutation()
    {
        return round($this->calculateMutationRate() * Parameters::population_size);
    }

    function isMutation()
    {
        if ($this->calculateNumOfMutation() > 0) {
            return TRUE;
        }
    }
    function generateMutation($valueOfGen)
    {
        if ($valueOfGen === 0) {
            return 1;
        } else {
            return 0;
        }
    }
    function mutation()
    {
        if ($this->isMutation()) {
            for ($i = 0; $i < $this->calculateNumOfMutation(); $i++) {
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                $indexOfGen = Randomizer::getRandomIndexOfGen();
                $selectedIndividu = $this->population[$indexOfIndividu];

                // echo 'Before Mutation : ';
                // print_r($selectedIndividu);
                // echo '<br>';

                $valueOfGen = $selectedIndividu[$indexOfGen];
                $mutatedGen = $this->generateMutation($valueOfGen);

                $selectedIndividu[$indexOfGen] = $mutatedGen;

                // echo 'After Mutation : ';
                // print_r($selectedIndividu);

                $ret[] = $selectedIndividu;
            }
            return $ret;
        }
    }
}

class Selection
{
    function __construct($population, $combinedOffsprings)
    {
        $this->population = $population;
        $this->combinedOffsprings = $combinedOffsprings;
    }

    function createTemporaryPopulation()
    {
        //  echo '<br>';
        // echo 'base population : ' . count($this->population) . '&nbsp;';
        foreach ($this->combinedOffsprings as $offspring) {
            $this->population[] = $offspring;
        }
        // echo 'offspring : ' . count($this->combinedOffsprings) . ' Temporary : ' . count($this->population);
        return $this->population;
    }

    function getVariabelValue($basePopulation, $fitTemporaryPopulation)
    {
        foreach ($fitTemporaryPopulation as $val) {
            $ret[] = $basePopulation[$val[1]];
        }
        return $ret;
    }

    function sortFitTemporaryPopulation()
    {
        $tempPopulation = $this->createTemporaryPopulation();
        $fitness = new Fitness;
        foreach ($tempPopulation as $key => $individu) {
            $fitnessValue = $fitness->calculateFitnessValue($individu);
            if ($fitness->isFit($fitnessValue)) {
                // echo $fitnessValue . ' ' . $key . '<br>';
                $fitTemporaryPopulation[] = [
                    $fitnessValue, $key
                ];
            }
        }
        rsort($fitTemporaryPopulation);
        // foreach ($fitTemporaryPopulation as $val) {
        //     print_r($val) . '<br>';
        // }
        $fitTemporaryPopulation = array_slice($fitTemporaryPopulation, 0, Parameters::population_size);
        echo '<p></p>' . print_r($fitTemporaryPopulation);
        // return $this->getVariabelValue($tempPopulation, $fitTemporaryPopulation);
    }

    function selectingIndividu()
    {
        // print_r($this->createTemporaryPopulation());
        // print_r($this->sortFitTemporaryPopulation());
        $selected = $this->sortFitTemporaryPopulation();
        echo "<p></p>";
        print_r($selected);
    }
}

// $catalog = new Catalogue;
// $catalog->product();

$initialPopulation  = new Population;
$population = $initialPopulation->createPopulationRandom();

// print_r($population);

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);

$crosover = new Crossover($population);
$crossoveroffsprings = $crosover->crossover();

// echo 'Crossover offsprings : <br> ';
// print_r($crossoveroffsprings);

echo '<p></p>';
// (new Mutation($population))->mutation();
$mutation = new Mutation($population);

if ($mutation->mutation()) {
    $mutationOffsprings = $mutation->mutation();
    // echo 'Mutation offspring <br>';
    // print_r($mutationOffsprings);
    // echo '<p></p>';
    foreach ($mutationOffsprings as $mutationOffspring) {
        $crossoveroffsprings[] = $mutationOffspring;
    }
}

//echo '<br>Tidak Ada Mutasi';
// echo 'Mutation offsprings : <br> ';
// print_r($crossoveroffsprings);


$fitness->fitnessEvaluation($crossoveroffsprings);

$selection = new Selection($population, $crossoveroffsprings);
$selection->selectingIndividu();
