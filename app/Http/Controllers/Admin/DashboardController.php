<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Visit;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'projects' => Project::count(),
                'blogs' => Blog::count(),
                'contacts' => Contact::count(),
                'unreadContacts' => Contact::where('read', false)->count(),
                'totalVisits' => Visit::count(),
                'todayVisits' => Visit::whereDate('visited_at', today())->count(),
            ],
        ]);
    }
}
