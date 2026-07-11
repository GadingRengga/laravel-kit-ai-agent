<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrialController extends Controller
{
    public function testModal()
    {
        return view('pages.trial-modal.modal-content');
    }

    public function trialSave($request)
    {

        return [
            'qty' => $request->_qty,
            'price' => $request->_price,
        ];
    }
}
