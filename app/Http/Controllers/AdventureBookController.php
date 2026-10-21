<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Support\AdventureBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Livre des aventures en page simple, prête à imprimer ou à enregistrer en PDF depuis le navigateur.
 * Réservé au MJ, comme la page du livre.
 */
class AdventureBookController extends Controller
{
    public function print(Request $request, Campaign $campaign): View
    {
        Gate::authorize('update', $campaign);

        $book = new AdventureBook($campaign);
        $version = $request->query('version') === 'redigee' && filled($campaign->adventure_book) ? 'redigee' : 'assemblee';

        return view('book.print', [
            'campaign' => $campaign,
            'chapters' => $book->chapters(),
            'sessions' => $book->sessions(),
            'version' => $version,
        ]);
    }
}
