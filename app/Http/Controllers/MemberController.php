<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Models\Project;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request, Project $project) 
    {
        $members = $project->members;

        return new UserCollection($members);
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Sync without detaching attaches the id
        // if it is not already attached
        $project->members()->syncWithoutDetaching([$request->user_id]);

        $members = $project->members;

        return new UserCollection($members);
    }

    public function destroy(Request $request, Project $project, int $member)
    {
        abort_if($project->creator_id === $member, 400, 'The project creator cannot be removed');

        abort_if(!$project->members()->pluck('id')->contains($member), 400, 'There is no member with given ID');

        $project->members()->detach([$member]);

        // Return new list of members instead of 204 
        $members = $project->members;

        return new UserCollection($members);
    }
}
