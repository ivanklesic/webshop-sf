<?php

namespace App\Recommendations\PostProcessing;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Result\Record;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Post\RecommendationSetPostProcessor;
use GraphAware\Reco4PHP\Result\Recommendation;
use GraphAware\Reco4PHP\Result\Recommendations;
use GraphAware\Reco4PHP\Result\SingleScore;

class RewardViewed extends RecommendationSetPostProcessor
{
    public function buildQuery(Node $input, Recommendations $recommendations)
    {
        $query = 'UNWIND {ids} as id
        MATCH (n) WHERE id(n) = id
        MATCH (input:User) WHERE id(input) = {id}        
        RETURN id(n) as id, EXISTS ((input)-[:VIEWED]->(n)) as has_viewed';

        $ids = [];
        foreach ($recommendations->getItems() as $item) {
            $ids[] = $item->item()->identity();
        }

        return Statement::create($query, ['ids' => $ids, 'id' => $input->identity()]);
    }

    public function postProcess(Node $input, Recommendation $recommendation, Record $record)
    {
        $score = 0;
        if ($record->get('has_viewed')){
            $score = 5;
        }

        $recommendation->addScore($this->name(), new SingleScore($score));
    }

    public function name()
    {
        return "reward_viewed";
    }

}