<?php

namespace App\Recommendations\PostProcessing;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Result\Record;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Post\RecommendationSetPostProcessor;
use GraphAware\Reco4PHP\Result\Recommendation;
use GraphAware\Reco4PHP\Result\Recommendations;
use GraphAware\Reco4PHP\Result\SingleScore;

class RewardProductsWithLessGasEmission extends RecommendationSetPostProcessor
{
    public function buildQuery(Node $input, Recommendations $recommendations)
    {
        $query = 'UNWIND {ids} as id
        MATCH (n) WHERE id(n) = id        
        RETURN id(n) as id, n.emission as emission';

        $ids = [];
        foreach ($recommendations->getItems() as $item) {
            $ids[] = $item->item()->identity();
        }

        return Statement::create($query, ['ids' => $ids]);
    }

    public function postProcess(Node $input, Recommendation $recommendation, Record $record)
    {
        $emission = $record->get('emission');

        if($emission <= 10 && $emission > 0){
            $score = 10;
        }
        else if ($emission <= 25 && $emission > 10 ){
            $score = 5;
        }
        else{
            $score = 0;
        }
        $recommendation->addScore($this->name(), new SingleScore($score, 'emission'));
    }

    public function name()
    {
        return "reward_less_emission";
    }

}