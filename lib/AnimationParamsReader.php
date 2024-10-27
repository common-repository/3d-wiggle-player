<?php

class AnimationParamsReader
{
    const WIGGLEPLAYER_CUSTOM_3DWIGGLE_ID = 0xE4;
    const WIGGLEPLAYER_CUSTOM_3DWIGGLE_STRING = "PSS3DWIGGLE\0\0";

    const WIGGLEPLAYER_ANIMATION_PAUSE_DEFAULT = 0.06;
    const WIGGLEPLAYER_ANIMATION_PAUSE_ONOFF = 1;
    const WIGGLEPLAYER_ANIMATION_PAUSEBALANCE_DEFAULT = 0.5;
    const WIGGLEPLAYER_ANIMATION_PAUSEBALANCE_ONOFF = 0.5;

    public static function read($path)
    {
        if (!is_file($path) || !file_exists($path)) {
            return null;
        }

        $bytes = file_get_contents($path);
        $data = self::readCurrentData($bytes);

        if (!isset($data->version)) {
            return null;
        }

        $result = new StdClass();

        if (isset($data->animation, $data->animation->duration, $data->animation->pause, $data->animation->balance)) {
            $result->duration = $data->animation->duration;
            $result->effect = AnimationParams::pauseToEffect($data->animation->pause);
            $result->balance = AnimationParams::pauseBalanceToEffectBalance($data->animation->balance);
            return $result;
        }

        if (isset($data->imgDuration) && isset($data->imgEffect)) {
            $result->duration = $data->imgDuration;
            if ($data->imgEffect == true) {
                $result->effect = AnimationParams::pauseToEffect(self::WIGGLEPLAYER_ANIMATION_PAUSE_DEFAULT);
                $result->balance = AnimationParams::pauseBalanceToEffectBalance(self::WIGGLEPLAYER_ANIMATION_PAUSEBALANCE_DEFAULT);
            } else {
                $result->effect = AnimationParams::pauseToEffect(self::WIGGLEPLAYER_ANIMATION_PAUSE_ONOFF);
                $result->balance = AnimationParams::pauseBalanceToEffectBalance(self::WIGGLEPLAYER_ANIMATION_PAUSEBALANCE_ONOFF);
            }
            return $result;
        }

        return null;
    }

    private static function readCurrentData($bytes)
    {
        $helper = new JPEGMarkersHelper();
        $data = $helper->extractFirstMarker($bytes, self::WIGGLEPLAYER_CUSTOM_3DWIGGLE_ID, self::WIGGLEPLAYER_CUSTOM_3DWIGGLE_STRING);
        if ($data && isset($data['contents'])) {
            $data = json_decode($data['contents']);
            // multiple settings saved
            if (is_array($data)) {
                foreach ($data as $d) {
                    if ($d->name == 'current') {
                        return $d;
                    }
                }
            }
            // signle setting saved
            return $data;
        }
        return null;
    }
}