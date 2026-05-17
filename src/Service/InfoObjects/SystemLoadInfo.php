<?php

declare(strict_types=1);

/*
 * This file is part of System Information Bundle for Contao Open Source CMS.
 *
 * (c) eikona-media.de
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\SystemInformation\Service\InfoObjects;

use Linfo\Linfo;
use Linfo\OS\OS;

/**
 * Class SystemLoadInfo.
 */
class SystemLoadInfo
{
    private float $last1Minute;

    private float $last5Minutes;

    private float $last15Minutes;

    private int $factor;

    public function init(): self
    {
        try {
            // If open_basedir is set and doesn't allow /sys, throw so the catch block uses the fallback
            $openBasedir = \ini_get('open_basedir');
            if ('' !== $openBasedir && !str_contains($openBasedir, '/sys')) {
                throw new \ErrorException('open_basedir prevents access to /sys');
            }
            $linfo = new Linfo();
            /** @var OS $parser */
            $parser = $linfo->getParser();
            $cpu = $parser->getCPU();
            $load = $parser->getLoad();

            $this->setLast1Minute((float) ($load['now'] ?? 0));
            $this->setLast5Minutes((float) ($load['5min'] ?? 0));
            $this->setLast15Minutes((float) ($load['15min'] ?? 0));
            $this->setFactor(\count($cpu));
        } catch (\Throwable) {
            // Linfo may trigger PHP warnings (converted to ErrorException)
            // on restricted environments (open_basedir). Use a safe fallback.
            $loadAvg = sys_getloadavg();

            $this->setLast1Minute((float) ($loadAvg[0] ?? 0));
            $this->setLast5Minutes((float) ($loadAvg[1] ?? 0));
            $this->setLast15Minutes((float) ($loadAvg[2] ?? 0));
            $this->setFactor(4);
        }

        return $this;
    }

    public function getLast1Minute(): float
    {
        return $this->last1Minute;
    }

    public function setLast1Minute(float $last1Minute): void
    {
        $this->last1Minute = $last1Minute;
    }

    public function getLast5Minutes(): float
    {
        return $this->last5Minutes;
    }

    public function setLast5Minutes(float $last5Minutes): void
    {
        $this->last5Minutes = $last5Minutes;
    }

    public function getLast15Minutes(): float
    {
        return $this->last15Minutes;
    }

    public function setLast15Minutes(float $last15Minutes): void
    {
        $this->last15Minutes = $last15Minutes;
    }

    public function getFactor(): int
    {
        return $this->factor;
    }

    public function setFactor(int $factor): void
    {
        $this->factor = $factor;
    }
}
