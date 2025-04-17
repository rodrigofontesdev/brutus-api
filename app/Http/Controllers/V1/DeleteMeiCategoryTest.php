<?php

use App\Models\MeiCategory;
use App\Models\User;
use Illuminate\Support\Str;

describe('Delete Mei Category', function() {
    beforeEach(function () {
        $this->subscriber = User::factory()->create();
    });

    it('should return an unauthorized response for unauthenticated requests', function () {
        $categoryId = ['id' => Str::uuid()->toString()];

        $response = $this->deleteJson(route('v1.mei-categories.delete', $categoryId));

        $response->assertUnauthorized();
    });

    it('should return a bad request if the category ID is an invalid UUID', function () {
        $categoryId = ['id' => 'invalid'];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.mei-categories.delete', $categoryId));

        $response->assertBadRequest();
        $response->assertSee('The specified MEI category ID in URL is invalid.');
    });

    it('should return a not found response for non-existent category', function () {
        $categoryId = ['id' => Str::uuid()->toString()];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.mei-categories.delete', $categoryId));

        $response->assertNotFound();
    });

    it('should return a forbidden response if the user attempts to delete another subscriber\'s category',
        function () {
            $anotherSubscriber = User::factory()->has(MeiCategory::factory())->create();
            $categoryId = ['id' => $anotherSubscriber->firstMeiCategory->id];

            $response = $this->actingAs($this->subscriber)
                ->deleteJson(route('v1.mei-categories.delete', $categoryId));

            $response->assertForbidden();
        }
    );

    it('should delete subscriber\'s category from the database', function () {
        $category = MeiCategory::factory()->for($this->subscriber, 'owner')->create();
        $categoryId = ['id' => $category->id];

        $response = $this->actingAs($this->subscriber)
            ->deleteJson(route('v1.mei-categories.delete', $categoryId));

        $response->assertNoContent();
        $this->assertDatabaseMissing($category);
    });
});
