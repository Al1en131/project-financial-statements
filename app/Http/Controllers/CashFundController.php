<?php

namespace App\Http\Controllers;

use App\Models\CashFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashFundController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Retrieve only the cash funds of the authenticated user
        $cashFunds = CashFund::where('user_id', Auth::id())->get();

        return view('cashfunds.index', compact('cashFunds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cash_fund_name' => 'required|string|max:255',
        ]);

        // Create cash fund only if the user is authenticated
        if (Auth::check()) {
            CashFund::create([
                'user_id' => Auth::id(),
                'cash_fund_name' => $request->cash_fund_name,
            ]);

            return redirect()->route('cashfunds.index')->with('success', 'Cash Fund created successfully.');
        }

        return redirect()->route('login')->withErrors('You need to log in to create a cash fund.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cash_fund_name' => 'required|string|max:255',
        ]);

        $cashFund = CashFund::findOrFail($id);
        $cashFund->cash_fund_name = $request->cash_fund_name;
        $cashFund->save();

        return redirect()->route('cashfunds.index')->with('success', 'Cash Fund updated successfully');
    }

    public function destroy($id)
    {
        $cashFund = CashFund::findOrFail($id);
        $cashFund->delete();

        return redirect()->route('cashfunds.index')->with('success', 'Cash Fund deleted successfully');
    }
}
