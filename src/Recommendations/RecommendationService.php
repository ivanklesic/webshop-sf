<?php

namespace App\Recommendations;

use GraphAware\Reco4PHP\Context\SimpleContext;
use GraphAware\Reco4PHP\RecommenderService;
use GraphAware\Reco4PHP\Result\Recommendations;

class RecommendationService
{
    /**
     * @var RecommenderService
     */
    protected $service;

    /**
     * ExampleRecommenderService constructor.
     * @param string $databaseUri
     */
    public function __construct($databaseUri)
    {
        $this->service = RecommenderService::create($databaseUri);
        $this->service->registerRecommendationEngine(new RecommendationEngine());
    }

    /**
     * @param int $id
     * @return Recommendations
     */
    public function recommendProductForUserWithId($id)
    {

        $input = $this->service->findInputBy('User', 'userID', $id);
        $recommendationEngine = $this->service->getRecommender("user_product_reco");

        return $recommendationEngine->recommend($input, new SimpleContext());
    }
}