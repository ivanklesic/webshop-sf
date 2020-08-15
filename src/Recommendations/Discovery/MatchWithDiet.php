<?php

namespace App\Recommendations\Discovery;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Cypher\StatementInterface;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Context\Context;
use GraphAware\Reco4PHP\Engine\SingleDiscoveryEngine;

class MatchWithDiet extends SingleDiscoveryEngine
{
    public function discoveryQuery(Node $input, Context $context) : StatementInterface
    {
        $query = 'MATCH (input:User) WHERE id(input) = {id}
        MATCH (input)-[:IS_USING]->(diet)
        WITH diet
        MATCH (reco:Product) WHERE reco.proteinPercent = diet.proteinPercent AND reco.carbohydratePercent = diet.carbohydratePercent AND reco.lipidPercent = diet.lipidPercent        
        RETURN distinct reco LIMIT 100';

        return Statement::create($query, ['id' => $input->identity()]);
    }


    public function name() : string
    {
        return "match_with_diet";
    }

}