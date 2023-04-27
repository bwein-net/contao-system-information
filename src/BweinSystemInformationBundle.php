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

namespace Bwein\SystemInformation;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BweinSystemInformationBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
