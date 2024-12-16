<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait WithRestoreAction
{
    /**
     * Process restore action request.
     */
    public function restore(Request $request): JsonResponse
    {
        $this->restoring = true;

        return $this->edit($request);
    }
}
