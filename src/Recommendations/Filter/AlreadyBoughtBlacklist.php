<?php

namespace App\Recommendations\Filter;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Filter\BaseBlacklistBuilder;

class AlreadyBoughtBlacklist extends BaseBlacklistBuilder
{
    public function blacklistQuery(Node $input)
    {
        $query = 'MATCH (input) WHERE id(input) = {inputId}
        MATCH (input)-[:BOUGHT]->(product)
        RETURN distinct product as item';

        return Statement::create($query, ['inputId' => $input->identity()]);
    }

    public function name()
    {
        return 'already_bought';
    }
}