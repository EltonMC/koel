<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\PlaylistStoreRequest;
use App\Http\Requests\API\PlaylistSyncRequest;
use App\Models\Playlist;
use App\Repositories\PlaylistRepository;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    private $playlistRepository;

    public function __construct(PlaylistRepository $playlistRepository)
    {
        $this->playlistRepository = $playlistRepository;
    }

    /**
     * Gets all playlists by the current user.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json($this->playlistRepository->getAllByCurrentUser());
    }

    /**
     * Create a new playlist.
     *
     * @return JsonResponse
     */
    public function store(PlaylistStoreRequest $request)
    {
        $playlist = $request->user()->playlists()->create($request->only('name'));
        $playlist->songs()->sync((array) $request->songs);

        $playlist->songs = $playlist->songs->pluck('id');

        return response()->json($playlist);
    }

    /**
     * Rename a playlist.
     *
     * @throws AuthorizationException
     *
     * @return JsonResponse
     */
    public function update(Request $request, Playlist $playlist)
    {
        $this->authorize('owner', $playlist);

        $playlist->update($request->only('name'));

        return response()->json($playlist);
    }

    /**
     * Sync a playlist with songs.
     * Any songs that are not populated here will be removed from the playlist.
     *
     * @throws AuthorizationException
     *
     * @return JsonResponse
     */
    public function sync(PlaylistSyncRequest $request, Playlist $playlist)
    {
        $this->authorize('owner', $playlist);

        $playlist->songs()->sync((array) $request->songs);

        return response()->json();
    }

    /**
     * Get a playlist's all songs.
     *
     * @throws AuthorizationException
     *
     * @return JsonResponse
     */
    public function getSongs(Playlist $playlist)
    {
        $this->authorize('owner', $playlist);

        return response()->json($playlist->songs->pluck('id'));
    }

    /**
     * Delete a playlist.
     *
     * @throws Exception
     * @throws AuthorizationException
     *
     * @return JsonResponse
     */
    public function destroy(Playlist $playlist)
    {
        $this->authorize('owner', $playlist);

        $playlist->delete();

        return response()->json();
    }
}
