<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * 管理员登录，返回 Sanctum token
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => '邮箱或密码不正确'], 401);
        }

        $token = $user->createToken('admin')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    /**
     * 后台影片列表（包含已下架影片）
     */
    public function movies(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 20), 100) ?: 20;
        $search = $request->input('search');
        $status = $request->input('status'); // published | unpublished

        $query = Movie::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('translated_title', 'like', "%{$search}%")
                  ->orWhere('director', 'like', "%{$search}%");
            });
        }

        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'unpublished') {
            $query->where('is_published', false);
        }

        $movies = $query->orderByDesc('id')->paginate($perPage);

        return response()->json($movies);
    }

    /**
     * 上架 / 下架
     */
    public function setPublished(Request $request, $id)
    {
        $movie = Movie::find($id);
        if (!$movie) {
            return response()->json(['error' => '影片不存在'], 404);
        }

        // 接受 true/false/1/0/"1"/"0"/"true"/"false"
        $validated = $request->validate([
            'is_published' => 'required|boolean',
        ]);

        $movie->is_published = (bool) $validated['is_published'];
        $movie->save();

        return response()->json([
            'status' => 'success',
            'id' => $movie->id,
            'is_published' => $movie->is_published,
        ]);
    }
}
