<?php

namespace App\Http\Controllers;

use App\Contracts\ActivityLogReadRepository;
use App\Http\Requests\ActivityLogIndexRequest;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(private readonly ActivityLogReadRepository $activityLogs) {}

    public function index(ActivityLogIndexRequest $request): View
    {
        return view('activity-log.index', [
            'activities' => $this->activityLogs->paginate($request->filters()),
            'logNames' => $this->activityLogs->logNames(),
            'summary' => $this->activityLogs->summary(),
        ]);
    }
}
