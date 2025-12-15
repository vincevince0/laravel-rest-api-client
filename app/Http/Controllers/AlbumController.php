<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlbumRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $needle = $request->get('needle');

        try {
            $url = $needle ? "albums?needle=" . urlencode($needle) : "albums";

            $response = Http::api()->get($url);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Ismeretlen hiba történt.';
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Hiba történt a lekérdezés során: $message");
            }

            $entities = ResponseHelper::getData($response);

            return view('albums.index', ['entities' => $entities, 'isAuthenticated' => $this->isAuthenticated()]);

        } catch (\Exception $e) {
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült betölteni az albumokat: " . $e->getMessage());
        }

    }

    public function show($id)
    {
        try {
            $response = Http::api()->get("/albums/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Az album nem található vagy hiba történt.';
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['album'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Az album adatai nem érhetők el.");
            }

            return view('albums.show', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült betölteni az album adatait: " . $e->getMessage());
        }
    }

    public function create()
    {
        return view('albums.create');
    }
	

    public function store(AlbumRequest $request)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->post('/albums', ['name' => $name]);

            if ($response->failed()) {
                // Ha az API válaszolt, de hibás státuszkóddal (pl. 422, 403, 500)
                $message = $response->json('message') ?? 'Nem sikerült létrehozni az albumot.';
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Hiba: $message");
            }

            return redirect()
                ->route('albums.index')
                ->with('success', "$name album sikeresen létrehozva!");

        } catch (\Exception $e) {
            // Hálózati vagy JSON dekódolási hiba
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }

    }

	public function edit($id)
    {
        try {
            $response = Http::api()->get("/albums/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Az album nem található vagy hiba történt.';
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['album'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Az album adatai nem érhetők el.");
            }

            return view('albums.edit', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült betölteni az album szerkesztő nézetét: " . $e->getMessage());
        }
    }


    public function update(AlbumRequest $request, $id)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->put("/albums/$id", ['name' => $name]);

            if ($response->successful()) {
                return redirect()
                    ->route('albums.index')
                    ->with('success', "$name album sikeresen frissítve!");
            }

            // Ha nem sikeres, de nem dobott kivételt (pl. 422)
            $errorMessage = $response->json('message') ?? 'Ismeretlen hiba történt.';
            return redirect()
                ->route('albums.index')
                ->with('error', "Hiba történt: $errorMessage");

        } catch (\Exception $e) {
            // Hálózati vagy egyéb kivétel
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült frissíteni: " . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $response = Http::api()
                ->withToken($this->token)
                ->delete("/albums/$id", ['id' => $id]);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Nem sikerült törölni az albumot.';
                return redirect()
                    ->route('albums.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $name = $body['name'] ?? 'Ismeretlen';

            return redirect()
                ->route('albums.index')
                ->with('success', "$name album sikeresen törölve!");

        } catch (\Exception $e) {
            return redirect()
                ->route('albums.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }
    }

}

