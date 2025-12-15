<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArtistRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArtistController extends Controller
{
    public function index(Request $request)
    {
        $needle = $request->get('needle');

        try {
            $url = $needle ? "artists?needle=" . urlencode($needle) : "artists";

            $response = Http::api()->get($url);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Ismeretlen hiba történt.';
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Hiba történt a lekérdezés során: $message");
            }

            $entities = ResponseHelper::getData($response);

            return view('artists.index', ['entities' => $entities, 'isAuthenticated' => $this->isAuthenticated()]);

        } catch (\Exception $e) {
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült betölteni az előadókat: " . $e->getMessage());
        }

    }

    public function show($id)
    {
        try {
            $response = Http::api()->get("/artists/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Az előadó nem található vagy hiba történt.';
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['artist'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Az előadó adatai nem érhetők el.");
            }

            return view('artists.show', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült betölteni az előadó adatait: " . $e->getMessage());
        }
    }

    public function create()
    {
        return view('artists.create');
    }
	

    public function store(artistRequest $request)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->post('/artists', ['name' => $name]);

            if ($response->failed()) {
                // Ha az API válaszolt, de hibás státuszkóddal (pl. 422, 403, 500)
                $message = $response->json('message') ?? 'Nem sikerült létrehozni az előadót.';
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Hiba: $message");
            }

            return redirect()
                ->route('artists.index')
                ->with('success', "$name előadó sikeresen létrehozva!");

        } catch (\Exception $e) {
            // Hálózati vagy JSON dekódolási hiba
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }

    }

	public function edit($id)
    {
        try {
            $response = Http::api()->get("/artists/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Az előadó nem található vagy hiba történt.';
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['artist'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Az előadó adatai nem érhetők el.");
            }

            return view('artists.edit', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült betölteni az előadó szerkesztő nézetét: " . $e->getMessage());
        }
    }


    public function update(artistRequest $request, $id)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->put("/artists/$id", ['name' => $name]);

            if ($response->successful()) {
                return redirect()
                    ->route('artists.index')
                    ->with('success', "$name előadó sikeresen frissítve!");
            }

            // Ha nem sikeres, de nem dobott kivételt (pl. 422)
            $errorMessage = $response->json('message') ?? 'Ismeretlen hiba történt.';
            return redirect()
                ->route('artists.index')
                ->with('error', "Hiba történt: $errorMessage");

        } catch (\Exception $e) {
            // Hálózati vagy egyéb kivétel
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült frissíteni: " . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $response = Http::api()
                ->withToken($this->token)
                ->delete("/artists/$id", ['id' => $id]);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Nem sikerült törölni az előadót.';
                return redirect()
                    ->route('artists.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $name = $body['name'] ?? 'Ismeretlen';

            return redirect()
                ->route('artists.index')
                ->with('success', "$name előadó sikeresen törölve!");

        } catch (\Exception $e) {
            return redirect()
                ->route('artists.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }
    }

}

