<?php

namespace App\Recommendations\Discovery;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Cypher\StatementInterface;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Context\Context;
use GraphAware\Reco4PHP\Engine\SingleDiscoveryEngine;

class BoughtByOthers extends SingleDiscoveryEngine
{
    public function discoveryQuery(Node $input, Context $context) : StatementInterface
    {
        $query = 'MATCH (input:User) WHERE id(input) = {id}
        MATCH (input)-[:BOUGHT]->(product)<-[:BOUGHT]-(o)
        WITH distinct o
        MATCH (o)-[:BOUGHT]->(reco)
        RETURN distinct reco LIMIT 100';

        return Statement::create($query, ['id' => $input->identity()]);
    }


    public function name() : string
    {
        return "bought_by_others";
    }

}