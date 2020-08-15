<?php

namespace App\Recommendations\Filter;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Type\Node;
use GraphAware\Reco4PHP\Filter\BaseBlacklistBuilder;

class ConflictingWithDietBlacklist extends BaseBlacklistBuilder
{
    public function blacklistQuery(Node $input)
    {
        $query = 'MATCH (input) WHERE id(input) = {inputId}
        MATCH (input)-[:IS_USING]->(diet)<-[:IS_EXCLUDED_FROM]-(product)
        RETURN distinct product as item';

        return Statement::create($query, ['inputId' => $input->identity()]);
    }

    public function name()
    {
        return 'conflicting_with_diet';
    }
}
