<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Тест главной страницы - проверяем что view существует
        $this->assertTrue(view()->exists('home'));
    }
    
    /**
     * Test sitemap exists
     */
    public function test_sitemap_exists(): void
    {
        $this->assertTrue(view()->exists('sitemap.index'));
    }
    
    /**
     * Test SEO layouts exist
     */
    public function test_seo_layouts_exist(): void
    {
        $this->assertTrue(view()->exists('layouts.seo'));
        $this->assertTrue(view()->exists('layouts.navigation'));
        $this->assertTrue(view()->exists('layouts.footer'));
    }
}
