## Install

You can install it by typing the following command in a terminal.

```bash
$ composer require smartvain/youtube-caption-displayer # or composer require --dev
```

### Install with [Packagist](https://packagist.org/packages/smartvain/youtube-caption-displayer)

## Usage

At first, get the language list used for youtube video subtitles with **`getLangList()`**.

Next, since the obtained language list contains **lang-code**, use it to get subtitles by **`getCaptionsWithSeconds()`** or **`getCaptionText()`**.

If it is a youtube video without subtitles, return Null.

It's so easy.