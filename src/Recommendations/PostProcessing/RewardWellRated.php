<?php

namespace App\Recommendations\PostProcessing;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Result\Record;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Post\RecommendationSetPostProcessor;
use GraphAware\Reco4PHP\Result\Recommendation;
use GraphAware\Reco4PHP\Result\Recommendations;
use GraphAware\Reco4PHP\Result\SingleScore;

class RewardWellRated extends RecommendationSetPostProcessor
{
    public function buildQuery(Node $input, Recommendations $recommendations)
    {
        $query = 'UNWIND {ids} as id
        MATCH (n) WHERE id(n) = id
        MATCH (n)<-[r:RATED]-(u)
        WITH n, count(r) as num_ratings, reduce(total=0, number in r.rating | total + number) as full_rating
        RETURN id(n) as id, toFloat(full_rating) / num_ratings as score';

        $ids = [];
        foreach ($recommendations->getItems() as $item) {
            $ids[] = $item->item()->identity();
        }

        return Statement::create($query, ['ids' => $ids]);
    }

    public function postProcess(Node $input, Recommendation $recommendation, Record $record)
    {
        $recommendation->addScore($this->name(), new SingleScore($record->get('score')));
    }

    public function name()
    {
        return "reward_well_rated";
    }

}