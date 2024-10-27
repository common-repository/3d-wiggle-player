<?php

class AnimationGenerator
{
    const DURATION_DEFAULT = 529;
    const DURATION_MIN = 36;
    const DURATION_MAX = 20000;

    const PAUSE_DEFAULT = 0.06;
    const PAUSE_ONOFF = 1;

    const PAUSEBALANCE_DEFAULT = 0.5;
    const PAUSEBALANCE_ONOFF = 0.5;

    const EFFECT_DEFAULT = 94;
    const EFFECT_MIN = 0;
    const EFFECT_MAX = 100;

    const EFFECTBALANCE_DEFAULT = 0;
    const EFFECTBALANCE_MIN = -1;
    const EFFECTBALANCE_MAX = 1;
    const AUTOPLAY_DEFAULT = 0;
    const IMG_CLASS = 'psswiggleplayer';
    const DATA_PREFIX = 'data-psswiggleplayer-';
    static $counter = 7612;
    static $figureCounter = 9515;

    function generate($post, $content)
    {
        return preg_replace_callback("/<img ([^>]+)+\\/>/", array($this, 'replaceWrapper'), $content);
    }

    function replaceWrapper($matches)
    {
        wp_enqueue_script('psswiggleplayer');
        wp_enqueue_style('psswiggleplayer');
        return $this->generateDiv($matches[0], $matches[1]);
    }

    function generateDiv($original, $attrs)
    {
        $duration = $this->getAttribute("data-psswiggleplayer-duration", $attrs);
        if (!$this->isValidAndInRange($duration, self::DURATION_MIN, self::DURATION_MAX)) {
            return $original;
        }

        $effect = $this->getAttribute("data-psswiggleplayer-effect", $attrs);
        if (!$this->isValidAndInRange($effect, self::EFFECT_MIN, self::EFFECT_MAX)) {
            return $original;
        }

        $balance = $this->getAttribute("data-psswiggleplayer-balance", $attrs);
        if (!$this->isValidAndInRange($balance, self::EFFECTBALANCE_MIN, self::EFFECTBALANCE_MAX)) {
            return $original;
        }

        $figureId = $this->getAttribute("data-psswiggleplayer-figureid", $attrs);

        $src = $this->getAttribute('src', $attrs);
        $width = $this->getAttribute("width", $attrs);
        $height = $this->getAttribute("height", $attrs);
        $classes = $this->getAttribute("class", $attrs);
        if (in_array(null, array($src, $width, $height, $classes))) {
            return $original;
        }

        $playerClasses = array('psswiggleplayer');
        foreach (explode(' ', $classes) as $class) {
            // aligment classes for div (some themes specify them as img.align*)
            if (in_array($class, array('alignleft', 'aligncenter', 'alignright', 'alignnone'))) {
                $playerClasses[] = 'psswiggleplayer_' . $class;
            }
            if (substr($class, 0, 9) == 'wp-image-') {
                $attachmentId = substr($class, 9);
            }
        }

        if ($attachmentId == null) {
            return $original;
        }

        $showDialog = $this->getAttribute("data-psswiggleplayer-dialog", $attrs) == "0" ? 0 : 1;
        $dialogDetails = "";
        if ($showDialog == 1) {
            $details = wp_get_attachment_image_src($attachmentId, 'full');
            $dialogDetails = sprintf(' data-psswiggleplayer-fullwidth="%s"  data-psswiggleplayer-fullheight="%s"  data-psswiggleplayer-fullsrc="%s" ',
                $details[1], $details[2], $details[0]);
            $playerClasses[] = "psswiggleplayercursor";
        }

        $animation_pause = AnimationParams::effectToPause($effect);
        $balance = AnimationParams::effectBalanceToPauseBalance($balance);

        $imageId = self::$counter++;

        $image = sprintf('<img src="%s" width="%s" height="%s" data-psswiggleplayer-autoplay="%s" data-psswiggleplayer-autostop="%s" data-psswiggleplayer-dialog="%s" ',
            $src, $width, $height,
            $this->getAttribute("data-psswiggleplayer-autoplay", $attrs),
            $this->getAttribute("data-psswiggleplayer-autostop", $attrs),
            $showDialog);

        $tmp = $this->getAttribute('alt', $attrs);
        if ($tmp != null) {
            $image .= sprintf(' alt="%s"', $tmp);
        }

        $tmp = $this->getAttribute('title', $attrs);
        if ($tmp != null) {
            $image .= sprintf(' title="%s"', $tmp);
        }

        $image .= $dialogDetails . '>';

        return '<style>' .
            $this->buildCommonCSS($imageId, $duration, $animation_pause, $balance) .
            $this->buildMediaCSS($attachmentId, $figureId, $imageId, $width, $height, $src) .
        '</style>' . "<div id=\"psswiggleplayer_{$imageId}\" class=\"" . join(' ', $playerClasses) . "\">{$image}</div>";
    }

    function getAttribute($name, $attrs)
    {
        $re = "/{$name}=\\\"([^\\\"]+)\"/";
        preg_match($re, $attrs, $matches);
        if (count($matches) > 0) {
            return $matches[1];
        }
        return null;
    }

    function isValidAndInRange($value, $min, $max)
    {
        return ($value !== null && is_numeric($value) && $min <= $value && $max >= $value);
    }

    function buildCommonCSS($imageId, $duration, $animation_pause, $balance)
    {
        $declarations = AnimationParams::paramsToCSS($duration, $animation_pause, $balance);
        $result = $this->buildCSSRuleSet('keyframes psswiggleplayer_' . $imageId . '_animation', $declarations, array('@-webkit-', '@'));

        $declarations = $this->buildCSSDeclaration('z-index', 100, array(''));
        $result .= $this->buildCSSRuleSet('#psswiggleplayer_' . $imageId, $declarations);

        $prefixes = array('-webkit-', '');
        $declarations = $this->buildCSSDeclaration('animation-name', 'psswiggleplayer_' . $imageId . '_animation', $prefixes);
        $declarations .= $this->buildCSSDeclaration('animation-duration', $duration . 'ms', $prefixes);
        $declarations .= $this->buildCSSDeclaration('animation-timing-function', 'linear', $prefixes);
        $declarations .= $this->buildCSSDeclaration('animation-iteration-count', 'infinite', $prefixes);
        $declarations .= $this->buildCSSDeclaration('animation-direction', 'alternate', $prefixes);

        $result .= $this->buildCSSRuleSet('#psswiggleplayer_' . $imageId . ' img.hover, .psswiggleplayer_' . $imageId, $declarations);
        return $result;
    }

    function buildMediaCSS($attachmentId, $figureId, $imageId, $width, $height, $src)
    {
        $result = $this->buildCSSMediaRule($width * 0.6, 0, $this->buildSizeSpecificCSS($attachmentId, $figureId, $imageId, $width, $height, $src));

        while ($width > 400) {
            $width = $width * 0.6;
            $height = $height * 0.6;
            $rules = $this->buildSizeSpecificCSS($attachmentId, $figureId, $imageId, $width, $height, $src);
            $result .= $this->buildCSSMediaRule($width * 0.6, $width, $rules);
        }

        $result .= $this->buildCSSMediaRule(0, $width, $rules);
        return $result;
    }

    function buildCSSMediaRule($minWidth, $maxWidth, $rules) {
        $result = ' @media screen ';
        if ($minWidth != 0) {
            $result .= ' and (min-width: ' . $minWidth . 'px) ';
        }
        if ($maxWidth != 0) {
            $result .= ' and (max-width: ' . $maxWidth . 'px) ';
        }

        return $result . ' { ' . $rules . ' } ';
    }

    function buildSizeSpecificCSS($attachmentId, $figureId, $imageId, $width, $height, $src)
    {
        $halfWidth = $width / 2;

        $media = "";

        if (is_numeric($figureId)) {
            $declarations = $this->buildCSSDeclaration('width', "{$halfWidth}px !important");
            $media = $this->buildCSSRuleSet('figure#attachment_' . $attachmentId . '.psswiggleplayer_' . $figureId, $declarations);
        }

        $declarations = $this->buildCSSDeclaration('overflow', "hidden");
        $declarations .= $this->buildCSSDeclaration('width', "{$halfWidth}px");
        $declarations .= $this->buildCSSDeclaration('height', "{$height}px");
        $declarations .= $this->buildCSSDeclaration("background-image", "url(\"{$src}\")");
        $declarations .= $this->buildCSSDeclaration("background-position", "0");
        $declarations .= $this->buildCSSDeclaration("background-size", "{$width}px {$height}px");
        $media .= $this->buildCSSRuleSet("#psswiggleplayer_{$imageId}", $declarations);

        $declarations = $this->buildCSSDeclaration('border', "none");
        $declarations .= $this->buildCSSDeclaration('margin-left', "-{$halfWidth}px");
        $declarations .= $this->buildCSSDeclaration('max-width', "none");
        $declarations .= $this->buildCSSDeclaration('width', "{$width}px");
        $declarations .= $this->buildCSSDeclaration('height', "{$height}px");
        $media .= $this->buildCSSRuleSet("#psswiggleplayer_{$imageId} img", $declarations);

        return $media;
    }

    function buildCSSRuleSet($selector, $declarations, $prefixes = array(''))
    {
        $result = "";
        foreach ($prefixes as $prefix) {
            $result .= $prefix . $selector . ' { ' . $declarations . ' } ';
        }
        return $result;
    }

    function buildCSSDeclaration($property, $value, $prefixes = array(''))
    {
        $result = "";
        foreach ($prefixes as $prefix) {
            $result .= $prefix . $property . ': ' . $value . '; ';
        }
        return $result;
    }
}
