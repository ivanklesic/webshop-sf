<?php

namespace App\Recommendations;

use GraphAware\Reco4PHP\Engine\BaseRecommendationEngine;
use App\Recommendations\Discovery\BoughtByOthers;
use App\Recommendations\Filter\AlreadyBoughtBlacklist;
use App\Recommendations\Filter\ConflictingWithDietBlacklist;
use App\Recommendations\Filter\ConflictingWithConditionBlacklist;
use App\Recommendations\PostProcessing\RewardWellRated;
use App\Recommendations\PostProcessing\RewardProductsWithLessGasEmission;
use App\Recommendations\PostProcessing\RewardViewed;
use App\Recommendations\Discovery\MatchWithDiet;

class RecommendationEngine extends BaseRecommendationEngine
{
    public function name() : string
    {
        return "user_product_reco";
    }

    public function discoveryEngines() : array
    {
        return array(
            new BoughtByOthers(),
            new MatchWithDiet()
        );
    }

    public function blacklistBuilders() : array
    {
        return array(
            new ConflictingWithConditionBlacklist(),
            new ConflictingWithDietBlacklist(),
            new AlreadyBoughtBlacklist()
        );
    }

    public function postProcessors() : array
    {
        return array(
            new RewardWellRated(),
            new RewardProductsWithLessGasEmission(),
            new RewardViewed()
        );
    }

    public function filters() : array
    {
        return array();
    }
}