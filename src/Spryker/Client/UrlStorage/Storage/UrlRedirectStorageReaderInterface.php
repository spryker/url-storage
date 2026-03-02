<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\UrlStorage\Storage;

use Generated\Shared\Transfer\UrlRedirectStorageTransfer;

interface UrlRedirectStorageReaderInterface
{
    public function findUrlRedirectStorageById(int $idRedirectUrl): ?UrlRedirectStorageTransfer;
}
