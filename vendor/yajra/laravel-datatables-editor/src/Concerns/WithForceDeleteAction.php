<?php

declare(strict_types=1);

namespace Yajra\DataTables\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait WithForceDeleteAction
{
    /**
     * Process force delete action request.
     *
     * @throws \Exception
     */
    public function forceDelete(Request $request): JsonResponse
    {
        $this->forceDeleting = true;

        return $this->remove($request);
    }
}
