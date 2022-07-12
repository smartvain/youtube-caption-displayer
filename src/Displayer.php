<?php

namespace Smartvain\YoutubeCaptionDisplayer;

use Illuminate\Support\Collection;

class Displayer extends DisplayerAbstract
{
    /**
     * Get a list of languages used in a particular youtube video.
     *
     * @param string $url
     *
     * @return Collection
     */
    public static function getLangList(string $url): Collection
    {
        $video_id = self::extractVideoId($url);
        
        $html = self::fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = self::extractCaptionTracks($html);
        
        $lang_list = collect();
        if ($caption_tracks) {
            foreach ($caption_tracks as $item) {
                $lang_list->push([
                    'text' => $item->name->simpleText,
                    'code' => $item->languageCode
                ]);
            }
        }
        
        return $lang_list;
    }

    /**
     * Get captions as collection from selected lang code.
     *
     * @param string $url
     * @param string $lang_code
     *
     * @return Collection
     */
    public static function getCaptionsWithSeconds(string $url, string $lang_code): Collection
    {
        $video_id = self::extractVideoId($url);
        
        $html = self::fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = self::extractCaptionTracks($html);
        
        $caption_track = self::filterByLangCode($caption_tracks, $lang_code);
        
        $xml = self::fetchUrlContent($caption_track->baseUrl);
        $captions = self::extractCaptionsWithSeconds($xml);

        return collect($captions);
    }
}