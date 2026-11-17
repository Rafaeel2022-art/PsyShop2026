<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GameController extends Controller
{
    public function index()
    {
        return response()->json( 
            Game::where('is_active', true)
                ->orderBy('title')
                ->paginate(12)
        );
    }

    
    public function adminIndex()
    {
    return response()->json(
        Game::orderBy('title')->paginate(12)
    );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:games,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'platform' => ['required', 'string', 'max:80'],
            'genre' => ['nullable', 'string', 'max:80'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $game = Game::create($data);

        return response()->json($game, 201);
    }

    public function show(Game $game)
    {
        abort_unless($game->is_active, 404);

        return response()->json($game);
    }

    public function update(Request $request, Game $game)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('games', 'slug')->ignore($game->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'platform' => ['sometimes', 'required', 'string', 'max:80'],
            'genre' => ['sometimes', 'nullable', 'string', 'max:80'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $game->update($data);

        return response()->json($game->fresh());
    }

    public function destroy(Game $game)
    {
        $game->update(['is_active' => false]);

        return response()->json([
            'message' => 'Videojuego desactivado correctamente.',
        ]);
    }
}
