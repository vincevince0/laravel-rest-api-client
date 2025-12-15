<?php

namespace App\Http\Controllers;

use App\Http\Requests\SongRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $needle = $request->get('needle');

        try {
            $url = $needle ? "songs?needle=" . urlencode($needle) : "songs";

            $response = Http::api()->get($url);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Ismeretlen hiba történt.';
                return redirect()
                    ->route('songs.index')
                    ->with('error', "Hiba történt a lekérdezés során: $message");
            }

            $entities = ResponseHelper::getData($response);

            return view('songs.index', ['entities' => $entities, 'isAuthenticated' => $this->isAuthenticated()]);

        } catch (\Exception $e) {
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült betölteni a zenéket: " . $e->getMessage());
        }

    }

    public function show($id)
    {
        try {
            $response = Http::api()->get("/songs/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'A zene nem található vagy hiba történt.';
                return redirect()
                    ->route('songs.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['song'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('songs.index')
                    ->with('error', "A zene adatai nem érhetők el.");
            }

            return view('songs.show', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült betölteni a zene adatait: " . $e->getMessage());
        }
    }

    public function create()
    {
        return view('songs.create');
    }
	

    public function store(songRequest $request)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->post('/songs', ['name' => $name]);

            if ($response->failed()) {
                // Ha az API válaszolt, de hibás státuszkóddal (pl. 422, 403, 500)
                $message = $response->json('message') ?? 'Nem sikerült létrehozni a zenét.';
                return redirect()
                    ->route('songs.index')
                    ->with('error', "Hiba: $message");
            }

            return redirect()
                ->route('songs.index')
                ->with('success', "$name zene sikeresen létrehozva!");

        } catch (\Exception $e) {
            // Hálózati vagy JSON dekódolási hiba
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }

    }

	public function edit($id)
    {
        try {
            $response = Http::api()->get("/songs/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'A zene nem található vagy hiba történt.';
                return redirect()
                    ->route('songs.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['song'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('songs.index')
                    ->with('error', "A zene adatai nem érhetők el.");
            }

            return view('songs.edit', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült betölteni a zene szerkesztő nézetét: " . $e->getMessage());
        }
    }


    public function update(songRequest $request, $id)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->put("/songs/$id", ['name' => $name]);

            if ($response->successful()) {
                return redirect()
                    ->route('songs.index')
                    ->with('success', "$name zene sikeresen frissítve!");
            }

            // Ha nem sikeres, de nem dobott kivételt (pl. 422)
            $errorMessage = $response->json('message') ?? 'Ismeretlen hiba történt.';
            return redirect()
                ->route('songs.index')
                ->with('error', "Hiba történt: $errorMessage");

        } catch (\Exception $e) {
            // Hálózati vagy egyéb kivétel
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült frissíteni: " . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $response = Http::api()
                ->withToken($this->token)
                ->delete("/songs/$id", ['id' => $id]);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Nem sikerült törölni a zenét.';
                return redirect()
                    ->route('songs.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $name = $body['name'] ?? 'Ismeretlen';

            return redirect()
                ->route('songs.index')
                ->with('success', "$name zene sikeresen törölve!");

        } catch (\Exception $e) {
            return redirect()
                ->route('songs.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }
    }
}
