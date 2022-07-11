<?php

namespace Smartvain\YoutubeCaptionDisplayer\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Smartvain\YoutubeCaptionDisplayer\Displayer;
use Tests\TestCase;

class DisplayerTest extends TestCase
{
    /**
     * Test to get lang list
     *
     * @return void
     */
    public function testGetLangList()
    {
        // $lang_list = Displayer::getLangList('https://www.youtube.com/watch?v=zOjov-2OZ0E&t=7s');
        $lang_list = Displayer::getLangList('https://www.youtube.com/watch?v=mZ23BLKnA1Y');

        $this->assertTrue(count($lang_list) > 0);

        $lang_list->each(function ($lang) {
            $this->assertArrayHasKey('text', $lang);
            $this->assertArrayHasKey('code', $lang);
        });
    }
}
