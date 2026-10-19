<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()->with('user')
            ->when($request->query('user'), fn ($query, $user) => $query->where('user_id', $user))
            ->when($request->query('action'), fn ($query, $action) => $query->where('action', $action))
            ->when($request->query('q'), fn ($query, $term) => $query->where('description', 'like', "%{$term}%"))
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')->paginate(50)->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'users' => User::withTrashed()->orderBy('name')->pluck('name', 'id'),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action', 'action'),
        ]);
    }
}
