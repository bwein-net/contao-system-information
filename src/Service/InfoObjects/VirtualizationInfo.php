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
 * Class VirtualizationInfo.
 */
class VirtualizationInfo
{
    private string $type;

    private string $method;

    public function init(): self
    {
        try {
            // If open_basedir is set and doesn't allow /sys, throw so the catch block uses the fallback
            $openBasedir = ini_get('open_basedir');
            if ($openBasedir !== '' && strpos($openBasedir, '/sys') === false) {
                throw new \ErrorException('open_basedir prevents access to /sys');
            }
            $linfo = new Linfo();
            /** @var OS $parser */
            $parser = $linfo->getParser();
            $virtualization = $parser->getVirtualization();

            $this->setType($virtualization['type'] ?? '');
            $this->setMethod($virtualization['method'] ?? '');
        } catch (\Throwable) {
            $this->setType('-');
            $this->setMethod('-');
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }
}
