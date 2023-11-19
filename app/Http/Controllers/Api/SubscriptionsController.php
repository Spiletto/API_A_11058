<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Subscriptions;

class SubscriptionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscriptions::with(['User'])->get();

        if (count($subscriptions) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $subscriptions
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
            'category' => 'required|in:Basic,Standard,Premium',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($request->input('id_user'));

        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        $user->status = 1;
        $user->save();

        $subscriptionsData = [
            'transaction_date' => now(),
            'price' => $this->subscriptionPrice($request->input('category')),
        ];

        $subscriptions = Subscriptions::create(array_merge($request->all(), $subscriptionsData));

        return response([
            'message' => "{$user->name}, category: {$subscriptions->category}, price: {$subscriptions->price}.",
            'data' => $subscriptions,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subscriptions = Subscriptions::find($id);

        if(!is_null($subscriptions)){
            return response([
                'message' => 'Berlangganan',
                'data' => $subscriptions
            ],200);
        }

        return response([
            'message' => 'Tidak menemukan langganan',
            'data' => null
        ],404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $updateData = $request->all();
        $subscriptions = Subscriptions::find($id);

        if (is_null($subscriptions)) {
            return response(['message' => 'Tidak menemukan langganan', 'data' => null], 404);
        }

        $validate = Validator::make($updateData, [
            'id_user' => 'required',
            'category' => 'required|in:Basic,Standard,Premium',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($updateData['id_user']);

        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        $updateData['price'] = $this->subscriptionPrice($updateData['category']);

        $subscriptions->update([
            'user_id' => $updateData['id_user'],
            'category' => $updateData['category'],
            'price' => $updateData['price'],
        ]);

        return response([
            'message' => 'Update Berhasil',
            'data' => $subscriptions,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subscriptions = Subscriptions::find($id);

        if(is_null($subscriptions)){
            return response([
                'message' => 'Tidak menemukan langganan',
                'data' => null
            ],404);
        }

        if ($subscriptions->delete()){
            return response([
                'message' => 'Berhasil Menghapus',
                'data' => $subscriptions
            ],200);
        }

        return response([
            'message' => 'Gagal Menghapus',
            'data' => null
        ],400);
    }

    private function subscriptionPrice($category)
    {
        switch ($category) {
            case 'Basic':
                return 50000;
            case 'Standard':
                return 100000;
            case 'Premium':
                return 150000;
            default:
                return 0;
        }
    }
}