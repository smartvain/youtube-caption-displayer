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
    public function getLangList(string $url): Collection
    {
        $video_id = $this->extractVideoId($url);
        
        $html = $this->fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($html);
        
        $lang_list = collect();
        foreach ($caption_tracks as $item) {
            $lang_list->push([
                'text' => $item->name->simpleText,
                'code' => $item->languageCode
            ]);
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
    public function getCaptions(string $url, string $lang_code): Collection
    {
        $video_id = $this->extractVideoId($url);
        
        $html = $this->fetchUrlContent("https://www.youtube.com/watch?v={$video_id}");
        $caption_tracks = $this->extractCaptionTracks($html);
        
        $caption_track = $this->filterByLangCode($caption_tracks, $lang_code);
        
        $xml = $this->fetchUrlContent($caption_track->baseUrl);
        $captions = $this->extractCaptions($xml);

        return collect($captions);
    }
}