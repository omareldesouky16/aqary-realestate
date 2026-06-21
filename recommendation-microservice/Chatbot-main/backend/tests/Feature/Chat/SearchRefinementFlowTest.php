<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SearchCriteriaService;
use PHPUnit\Framework\TestCase;

class SearchRefinementFlowTest extends TestCase
{
    public function test_budget_and_optional_fields_are_refinement_signals(): void
    {
        $service = new SearchCriteriaService();

        $this->assertTrue($service->isRefinement(['slots' => ['area' => 180]]));
        $this->assertTrue($service->isRefinement(['slots' => ['bedrooms' => 3]]));
        $this->assertTrue($service->isRefinement(['slots' => ['bathrooms' => 2]]));
        $this->assertTrue($service->isRefinement(['slots' => ['features' => ['Security']]]));
    }
}
