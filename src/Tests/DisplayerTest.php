<?php

namespace Smartvain\YoutubeCaptionDisplayer\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Smartvain\YoutubeCaptionDisplayer\Displayer;
use Tests\TestCase;

class DisplayerTest extends TestCase
{
    /**
     * @var string
     */
    private static string $caption_exist_url = 'https://www.youtube.com/watch?v=zOjov-2OZ0E&t=7s';

    /**
     * @var string
     */
    private static string $caption_not_exist_url = 'https://www.youtube.com/watch?v=WFsAon_TWPQ';
    
    /**
     * Test to count lang list.
     *
     * @return void
     */
    public function testCountLangList()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);

        $this->assertTrue(count($lang_list) > 0);
    }

    /**
     * Test if lang list has particular keys.
     *
     * @return void
     */
    public function testLangListHasKey()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);

        $lang_list->each(function ($lang) {
            $this->assertArrayHasKey('text', $lang);
            $this->assertArrayHasKey('code', $lang);
        });
    }

    /**
     * Test when lang list not exist.
     *
     * @return void
     */
    public function testLangListNotExist()
    {
        $lang_list = Displayer::getLangList(self::$caption_not_exist_url);

        $this->assertTrue(count($lang_list) === 0);
    }
}
