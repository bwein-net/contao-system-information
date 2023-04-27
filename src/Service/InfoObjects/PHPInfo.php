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

/**
 * Class PHPInfo.
 */
class PHPInfo
{
    private string $version;
    private array $extensions;

    public function init(): self
    {
        // collect php information
        $this->setVersion(PHP_VERSION);
        $this->setExtensions(get_loaded_extensions());

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(array $extensions): void
    {
        $this->extensions = $extensions;
    }
}
