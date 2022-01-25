<?php

use Parameters as GlobalParameters;

$start = microtime(true);

class Parameters
{
    const file_name = 'dataset.txt';
    const columns = ['item', 'price'];
    const population_size = 10;
    const budget = 11000;
    const stopping_value = 1000;
    const crossover_rate = 0.8;
    const max_iter = 6;
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

            return $fits[$index];
        } else {
            foreach ($fits as $key => $val) {
                if ($val['numberOfSelectedItem'] === $maxItem) {
                    echo $val['selectedIndividuKey'] . ' - ' . $val['fitnessValue'] . '<br>';
                    // $ret[] = [
                    //     'individuKey' => $val['selectedIndividuKey'],
                    //     'fitnessValue' => $val['fitnessValue'],
                    //     'chromosome' => $fits[$key]['chromosome']
                    // ];
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue'],
                        'chromosome' => $fits[$key]['chromosome']
                    ];
                }
            }
            // print_r($ret);
            if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1) {
                $index = rand(0, count($ret) - 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }

            return ($ret[$index]);
        }
    }

    function isFound($fits)
    {
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r('jumlah item Terbanyak ada berapa individu = ');
        print_r($countedMaxItems);
        echo '<br>';
        $maxItem =  max(array_keys($countedMaxItems));
        echo "Item Terbanyak " . $maxItem;
        echo '<br>';
        printf('jumlah individu yg memiliki value ItemMax : ' . $countedMaxItems[$maxItem] . ' individu' . '<br>');
        // echo '<br>';
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem);

        //print_r($bestFitnessValue['fitnessValue']);
        echo '<br> Best fitness value : ' . $bestFitnessValue['fitnessValue'];
        echo '<br>';
        $residual = Parameters::budget - $bestFitnessValue['fitnessValue'];
        echo 'Residual : ' . $residual;

        // if ($residual <= Parameters::stopping_value && $residual > 0) {
        //     return TRUE;
        // }
        return $bestFitnessValue;
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
                    'fitnessValue' => $fitnessValue,
                    'chromosome' => $initialPopulation[$listOfIndividuKey]
                ];
                print_r($fits);
                echo "<p>";
            } else {
                echo ' (not fit)';
            }

            echo '<br>';
        }
        // if ($this->isFound($fits)) {
        //     echo '  FOUND';
        // } else {
        //     echo '  >> Next Generation';
        // }
        return $fits;
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
        echo '<br>';
        print_r($parents);
        foreach (array_keys($parents) as $key) {
            foreach (array_keys($parents) as $subkey) {
                if ($key !== $subkey) {
                    $ret[] = [$key, $subkey];
                }
            }
            array_shift($parents);
        }
        echo "<br>";
        print_r($ret);
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
            echo '<p></p>';
            echo 'Parents :<br>';
            foreach ($parent1 as $gen) {
                echo $gen;
            }
            echo '><';
            foreach ($parent2 as $gen) {
                echo $gen;
            }
            echo '<br>';

            echo 'Offspringg<br>';
            $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
            $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
            foreach ($offspring1 as $gen) {
                echo $gen;
            }
            echo '><';
            foreach ($offspring2 as $gen) {
                echo $gen;
            }
            echo '<br>';
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
        $mr = 0.2;
        return $mr;
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

                echo 'Before Mutation : ';
                print_r($selectedIndividu);
                echo '<br>';

                $valueOfGen = $selectedIndividu[$indexOfGen];
                $mutatedGen = $this->generateMutation($valueOfGen);

                $selectedIndividu[$indexOfGen] = $mutatedGen;

                echo 'After Mutation : ';
                print_r($selectedIndividu);
                echo '<br>';
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
        echo '<br>';
        echo 'base population : ' . count($this->population) . '&nbsp;';
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
        $fitness = new Fitness($tempPopulation);
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
        return $this->getVariabelValue($tempPopulation, $fitTemporaryPopulation);
    }

    function selectingIndividu()
    {
        // print_r($this->createTemporaryPopulation());
        // print_r($this->sortFitTemporaryPopulation());
        $selected = $this->sortFitTemporaryPopulation();
        echo "<p></p>";
        print_r($selected);
        return $selected;
    }
}


// ----------------------------------------------------------------------------------
class Algen
{
    public $maxIter;

    function __construct($popSize, $maxIter)
    {
        $this->popSize = $popSize;
        $this->maxIter = $maxIter;
    }

    function isFound($bestIndividus)
    {
        $residual = Parameters::budget - $bestIndividus['fitnessValue'];
        if ($residual <= Parameters::stopping_value && $residual > 0) {
            return TRUE;
        }
    }

    function countItems($chromosome)
    {
        return array_count_values($chromosome)[1];
    }

    function analytics($iter, $analitics)
    {
        $numOfLastResults = 10;
        if ($iter >= ($numOfLastResults - 1)) {
            $residual = count($analitics) - $numOfLastResults;

            if ($residual === 0 && count(array_unique($analitics)) === 1) {
                return true;
            }

            if ($residual > 0) {
                for ($i = 0; $i < $residual; $i++) {
                    array_shift($analitics);
                }
                if (count(array_unique($analitics)) === 1) {
                    return true;
                }
            }
        }
    }

    function algen()
    {
        $population = (new Population($this))->createPopulationRandom();
        $fitness = new Fitness($population);
        $fitIndividus = $fitness->fitnessEvaluation($population);
        $bestIndividus = $fitness->isFound($fitIndividus);
        $bestIndividuIsFound = $this->isFound($bestIndividus);

        $iter = 0;
        while ($iter < $this->maxIter || $bestIndividuIsFound === FALSE) {

            $crossoverOffsprings = (new Crossover($population))->crossover();
            $mutation = new Mutation($population);

            if ($mutation->mutation()) {
                $mutationOffsprings = $mutation->mutation();
                echo 'Mutation offspring <br>';
                print_r($mutationOffsprings);
                echo '<p></p>';
                foreach ($mutationOffsprings as $mutationOffspring) {
                    $crossoverOffsprings[] = $mutationOffspring;
                }
                printf('Populasi gabungan crossover dan mutation Offsprings : ');
                print_r($crossoverOffsprings);
                echo "<br>";
            }
            $selection = new Selection($population, $crossoverOffsprings);
            $population = [];
            $population = $selection->selectingIndividu();
            $fitIndividus = [];
            $fitIndividus = $fitness->fitnessEvaluation($crossoverOffsprings);
            $bestIndividus = $fitness->isFound($fitIndividus);

            $bestIndividuIsFound = $this->isFound($bestIndividus);

            if ($bestIndividuIsFound) {
                $bestIndividus['numOfItems'] = $this->countItems($bestIndividus['chromosome']);
                return $bestIndividus;
            }
            $bests[] = $bestIndividus;
            $analitics[] = $bestIndividus['fitnessValue'];
            if ($this->analytics($iter, $analitics)) {
                break;
            }
            $iter++;
        }

        foreach ($bests as $key => $best) {
            $bests[$key]['numOfItems'] =  $this->countItems($best['chromosome']);
        }

        $maxItems = max(array_column($bests, 'numOfItems'));
        $index = array_search($maxItems, array_column($bests, 'numOfItems'));
        return $bests[$index];
    }
}

// ----------------------------------------------------------------------------------

// $catalog = new Catalogue;
// $catalog->product();

// $initialPopulation  = new Population;
// $population = $initialPopulation->createPopulationRandom();

// print_r($population);

// $fitness = new Fitness($population);
// $fitness->fitnessEvaluation($population);

// $crosover = new Crossover($population);
// $crossoveroffsprings = $crosover->crossover();

// echo 'Crossover offsprings : <br> ';
// print_r($crossoveroffsprings);

// echo '<p></p>';
// //(new Mutation($population))->mutation();
// $mutation = new Mutation($population);

// if ($mutation->mutation()) {
//     $mutationOffsprings = $mutation->mutation();
//     echo 'Mutation offspring <br>';
//     print_r($mutationOffsprings);
//     echo '<p></p>';
//     foreach ($mutationOffsprings as $mutationOffspring) {
//         $crossoveroffsprings[] = $mutationOffspring;
//     }
//     printf('Populasi gabungan crossover dan mutation Offsprings : ');
//     print_r($crossoveroffsprings);
//     echo "<br>";
// };

// $fitness->fitnessEvaluation($crossoveroffsprings);


// $selection = new Selection($population, $crossoveroffsprings);
// $selection->selectingIndividu();



function saveToFile($maxIter, $fitnessValue, $numOfItems)
{
    $pathToFile = 'output.txt';
    $data = array($maxIter, $fitnessValue, $numOfItems);
    $fp = fopen($pathToFile, 'a');
    fputcsv($fp, $data);
    fclose($fp);
}

for ($popSize = Parameters::population_size; $popSize <= 35; $popSize += 5) {
    for ($i = 0; $i < 10; $i++) {
        echo 'PopSize: ' . $popSize . '<p>';
        $algenKnapsack = (new Algen($popSize, Parameters::max_iter))->algen();
        echo ' Fitness: ' . $algenKnapsack['fitnessValue'] . ' Items: ' . $algenKnapsack['numOfItems'];
        echo "\n";
        saveToFile($popSize, $algenKnapsack['fitnessValue'], $algenKnapsack['numOfItems']);
    }
}





$end = microtime(true);
echo 'Time: ' . ($end - $start);
