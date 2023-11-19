<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Activities;
use App\Models\Content;

class ActivitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activities = Activities::with(['User', 'Content'])->get();

        if (count($activities) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $activities
            ],200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_user' => 'required',
            'id_content' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($request->input('id_user'));
        $content = Content::find($request->input('id_content'));

        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        if (!$content) {
            return response(['message' => 'Content not found'], 400);
        }

        if ($content->type == 'Paid' && $user->status = 0) {
            return response(['message' => 'User belum subscribe'], 400);
        }

        $activities = Activities::create([
            'id_user' => $user->id,
            'id_content' => $content->id,
            'accessed_at' => now(),
        ]);

        return response([
            'message' => "{$user->name} accessed {$content->title} at {$activities->accessed_at}.",
            'data' => $activities,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $activities = Activities::find($id);

        if(!is_null($activities)){
            return response([
                'message' => 'Activities found',
                'data' => $activities
            ],200);
        }

        return response([
            'message' => 'Activities not found',
            'data' => null
        ],404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $updateData = $request->all();
        $activities = Activities::find($id);

        if (is_null($activities)) {
            return response(['message' => 'Activities not found', 'data' => null], 404);
        }

        $validate = Validator::make($updateData, [
            'id_user' => 'required',
            'id_content' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($updateData['id_user']);
        $content = Content::find($updateData['id_content']);

        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        if (!$content) {
            return response(['message' => 'Content not found'], 400);
        }

        $activities->update([
            'id_user' => $user->id,
            'id_content' => $content->id,
            'accessed_at' => $updateData['accessed_at'],
        ]);

        return response([
            'message' => 'Update Activities Success',
            'data' => $activities,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $activities = Activities::find($id);

        if (is_null($activities)) {
            return response(['message' => 'Activities not found', 'data' => null], 404);
        }

        if ($activities->delete()) {
            return response([
                'message' => 'Delete Activities Success',
                'data' => $activities,
            ], 200);
        }

        return response(['message' => 'Delete Activities Failed', 'data' => null], 400);
    }
}