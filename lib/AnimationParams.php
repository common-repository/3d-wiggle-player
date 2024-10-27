<?php

class AnimationParams
{

    public static function pauseToEffect($pause)
    {
        return 100 - ($pause * 100);
    }

    public static function pauseBalanceToEffectBalance($balance)
    {
        return 2 * $balance - 1;
    }

    public static function effectToPause($effect)
    {
        return (100 - $effect) / 100;
    }

    public static function effectBalanceToPauseBalance($balance)
    {
        return ($balance + 1) / 2;
    }

    public static function paramsToCSS($duration, $pause, $balance)
    {
        $tpl = "\t%.2f%% { opacity: %.2f; }";

        // handle specia cases
        if ($pause == 1 && $balance == 0) {
            //top layer invisible all the time
            $frames[] = sprintf($tpl, 0, 0);
            $frames[] = sprintf($tpl, 100, 0);
            return join("\n", $frames);
        }

        if ($pause == 1 && $balance == 1) {
            //bottom layer invisible all the time
            $frames[] = sprintf($tpl, 0, 1);
            $frames[] = sprintf($tpl, 100, 1);
            return join("\n", $frames);
        }

        if ($pause == 0) {
            // if there are no pause intervals, they can't be balanced
            $frames[] = sprintf($tpl, 0, 1);
            $frames[] = sprintf($tpl, 100, 0);
            return join("\n", $frames);
        }

        $pauseAtStart = 100 * $pause * $balance;
        $pauseAtEnd = 100 * (1 - $pause * (1 - $balance));

        // biztosan nem: $pauseAtEnd == 0!
        // always start with top layer visible
        $frames[] = sprintf($tpl, 0, 1);
        if ($pauseAtStart > 0) {
            $frames[] = sprintf($tpl, $pauseAtStart, 1);
        }

        if ($pauseAtStart == $pauseAtEnd) { // p == 1 & b == 0.5
            $frames[] = sprintf($tpl, $pauseAtEnd + 1, 0);
        } else if ($pauseAtEnd < 100) {
            $frames[] = sprintf($tpl, $pauseAtEnd, 0);
        }
        // at the end, the top layer is invisible
        $frames[] = sprintf($tpl, 100, 0);
        return join("\n", $frames);
    }
}