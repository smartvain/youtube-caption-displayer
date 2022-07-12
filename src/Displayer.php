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
        $caption_tracks = self::extractCaptionTracksFromUrl($url);

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
     * Get captions as collection from entered lang code.
     *
     * @param string $url
     * @param string $lang_code
     *
     * @return Collection
     */
    public static function getCaptionsWithSeconds(string $url, string $lang_code): Collection
    {
        $caption_tracks = self::extractCaptionTracksFromUrl($url);

        $captions = null;
        if ($caption_tracks) {
            $caption_track = self::filterByLangCode($caption_tracks, $lang_code);
            $xml = self::fetchUrlContent($caption_track->baseUrl);
            $captions = self::extractCaptionsWithSeconds($xml);
        }

        return collect($captions);
    }

    /**
     * Get caption in one sentence from entered lang code.
     *
     * @param string $url
     * @param string $lang_code
     *
     * @return string|null
     */
    public static function getCaptionText(string $url, string $lang_code): ?string
    {
        $caption_tracks = self::extractCaptionTracksFromUrl($url);

        $caption = null;
        if ($caption_tracks) {
            $caption_track = self::filterByLangCode($caption_tracks, $lang_code);
            $xml = self::fetchUrlContent($caption_track->baseUrl);
            $caption = self::extractCaptionText($xml);
        }

        return $caption;
    }
}
