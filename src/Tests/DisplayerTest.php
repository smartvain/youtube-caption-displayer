<?php

namespace Smartvain\YoutubeCaptionDisplayer\Tests;

use Smartvain\YoutubeCaptionDisplayer\Displayer;
use Smartvain\YoutubeCaptionDisplayer\Exception\CaptionTrackNotFound;
use Tests\TestCase;

class DisplayerTest extends TestCase
{
    /**
     * @var string
     */
    private static string $caption_exist_url = 'https://www.youtube.com/watch?v=ouf7rXDlkDk';

    /**
     * @var string
     */
    private static string $caption_not_exist_url = 'https://www.youtube.com/watch?v=WFsAon_TWPQ';
    
    /**
     * Test to count lang list.
     *
     * @return void
     */
    public function testLangListCount()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);

        $this->assertTrue(count($lang_list) > 0);
    }

    /**
     * Test if lang list doesn't exist.
     *
     * @return void
     */
    public function testLangListNotExist()
    {
        $lang_list = Displayer::getLangList(self::$caption_not_exist_url);

        $this->assertTrue(count($lang_list) === 0);
    }

    /**
     * Test if lang list has particular keys.
     *
     * @return void
     */
    public function testLangListHasKeys()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);

        $lang_list->each(function ($lang) {
            $this->assertArrayHasKey('text', $lang);
            $this->assertArrayHasKey('code', $lang);
        });
    }

    /**
     * Test to get captions.
     *
     * @return void
     */
    public function testCaptionsCount()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);
        $lang_code = $lang_list->first()['code'];
        
        $captions = Displayer::getCaptionsWithSeconds(self::$caption_exist_url, $lang_code);

        $this->assertTrue(count($captions) > 0);
    }
    
    /**
     * Test if captions doesn't exist.
     *
     * @return void
     */
    public function testCaptionsNotExist()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);
        $lang_code = $lang_list->first()['code'];
        
        $captions = Displayer::getCaptionsWithSeconds(self::$caption_not_exist_url, $lang_code);
    
        $this->assertTrue(count($captions) === 0);
    }

    /**
     * Test if captions has particular keys.
     *
     * @return void
     */
    public function testCaptionsHasKeys()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);
        $lang_code = $lang_list->first()['code'];

        $captions = Displayer::getCaptionsWithSeconds(self::$caption_exist_url, $lang_code);

        $captions->each(function ($caption) {
            $this->assertArrayHasKey('text', $caption);
            $this->assertArrayHasKey('start', $caption);
            $this->assertArrayHasKey('dur', $caption);
        });
    }

    /**
     * Test that raise the exception when a wrong lang code is entered.
     *
     * @return void
     */
    public function testCaptionsWrongLangCode()
    {
        $this->expectException(CaptionTrackNotFound::class);

        $lang_code = 'nothing-code';
        Displayer::getCaptionsWithSeconds(self::$caption_exist_url, $lang_code);
    }

    /**
     * Test to get captions in one sentence.
     *
     * @return void
     */
    public function testCaptionText()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);
        $lang_code = $lang_list->first()['code'];
        
        $caption = Displayer::getCaptionText(self::$caption_exist_url, $lang_code);

        $this->assertNotNull($caption);
    }
    
    /**
     * Test if caption text doesn't exist.
     *
     * @return void
     */
    public function testCaptionTextNotExist()
    {
        $lang_list = Displayer::getLangList(self::$caption_exist_url);
        $lang_code = $lang_list->first()['code'];
        
        $caption = Displayer::getCaptionText(self::$caption_not_exist_url, $lang_code);
    
        $this->assertNull($caption);
    }

    /**
     * Test that raise the exception when a wrong lang code is entered.
     *
     * @return void
     */
    public function testCaptionTextWrongLangCode()
    {
        $this->expectException(CaptionTrackNotFound::class);

        $lang_code = 'nothing-code';
        Displayer::getCaptionText(self::$caption_exist_url, $lang_code);
    }
}
