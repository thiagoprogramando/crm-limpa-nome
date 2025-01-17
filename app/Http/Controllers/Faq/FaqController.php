<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaqController extends Controller {
    
    public function faq(Request $request) {

        return view('app.Faq.faq');
    }
}
