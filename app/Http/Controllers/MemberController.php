<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $needle = $request->get('needle');

        try {
            $url = $needle ? "members?needle=" . urlencode($needle) : "members";

            $response = Http::api()->get($url);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Ismeretlen hiba történt.';
                return redirect()
                    ->route('members.index')
                    ->with('error', "Hiba történt a lekérdezés során: $message");
            }

            $entities = ResponseHelper::getData($response);

            return view('members.index', ['entities' => $entities, 'isAuthenticated' => $this->isAuthenticated()]);

        } catch (\Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült betölteni az tagokat: " . $e->getMessage());
        }

    }

    public function show($id)
    {
        try {
            $response = Http::api()->get("/members/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'A tag nem található vagy hiba történt.';
                return redirect()
                    ->route('members.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['member'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('members.index')
                    ->with('error', "A tag adatai nem érhetők el.");
            }

            return view('members.show', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült betölteni a tag adatait: " . $e->getMessage());
        }
    }

    public function create()
    {
        return view('members.create');
    }
	

    public function store(memberRequest $request)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->post('/members', ['name' => $name]);

            if ($response->failed()) {
                // Ha az API válaszolt, de hibás státuszkóddal (pl. 422, 403, 500)
                $message = $response->json('message') ?? 'Nem sikerült létrehozni a tagot.';
                return redirect()
                    ->route('members.index')
                    ->with('error', "Hiba: $message");
            }

            return redirect()
                ->route('members.index')
                ->with('success', "$name tag sikeresen létrehozva!");

        } catch (\Exception $e) {
            // Hálózati vagy JSON dekódolási hiba
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }

    }

	public function edit($id)
    {
        try {
            $response = Http::api()->get("/members/$id");

            if ($response->failed()) {
                $message = $response->json('message') ?? 'A tag nem található vagy hiba történt.';
                return redirect()
                    ->route('members.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $entity = $body['member'] ?? null;

            if (!$entity) {
                return redirect()
                    ->route('members.index')
                    ->with('error', "A tag adatai nem érhetők el.");
            }

            return view('members.edit', ['entity' => $entity]);

        } catch (\Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült betölteni a tag szerkesztő nézetét: " . $e->getMessage());
        }
    }


    public function update(memberRequest $request, $id)
    {
        $name = $request->get('name');

        try {
            $response = Http::api()
                ->withToken($this->token)
                ->put("/members/$id", ['name' => $name]);

            if ($response->successful()) {
                return redirect()
                    ->route('members.index')
                    ->with('success', "$name tag sikeresen frissítve!");
            }

            // Ha nem sikeres, de nem dobott kivételt (pl. 422)
            $errorMessage = $response->json('message') ?? 'Ismeretlen hiba történt.';
            return redirect()
                ->route('members.index')
                ->with('error', "Hiba történt: $errorMessage");

        } catch (\Exception $e) {
            // Hálózati vagy egyéb kivétel
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült frissíteni: " . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            $response = Http::api()
                ->withToken($this->token)
                ->delete("/members/$id", ['id' => $id]);

            if ($response->failed()) {
                $message = $response->json('message') ?? 'Nem sikerült törölni a tagot.';
                return redirect()
                    ->route('members.index')
                    ->with('error', "Hiba: $message");
            }

            $body = $response->json();
            $name = $body['name'] ?? 'Ismeretlen';

            return redirect()
                ->route('members.index')
                ->with('success', "$name tag sikeresen törölve!");

        } catch (\Exception $e) {
            return redirect()
                ->route('members.index')
                ->with('error', "Nem sikerült kommunikálni az API-val: " . $e->getMessage());
        }
    }
}
