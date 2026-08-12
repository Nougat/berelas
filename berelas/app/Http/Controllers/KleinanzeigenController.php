<?php

namespace App\Http\Controllers;

use App\Services\Großhandel;
use Illuminate\Http\Request;

class KleinanzeigenController extends Controller
{
    public function next(Großhandel $großhandel)
    {
        $item = $großhandel->getNextItemForKleinanzeigen();
        
        if (!$item) {
            return response()->json(['message' => 'No items found for Kleinanzeigen.'], 404);
        }

        return response()->json($item);
    }
}