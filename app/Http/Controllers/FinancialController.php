<?php

namespace App\Http\Controllers;

use App\Models\Financial;
use App\Models\FinancialStatement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    // Display all financial categories for the authenticated user
    public function index()
    {
        $userId = Auth::id();
        $financials = Financial::where('user_id', $userId)->get();
        return view('financial.index', compact('financials'));
    }

    // Store a new financial category for the authenticated user
    public function storeFinancial(Request $request)
    {
        $request->validate([
            'financial_name' => 'required|string|max:255',
        ]);

        Financial::create([
            'user_id' => Auth::id(),
            'financial_name' => $request->financial_name,
        ]);

        return redirect()->route('financial.index')->with('success', 'Financial category created successfully.');
    }

    // Display financial statements for a specific financial category
    public function showStatements($financialId)
    {
        $financial = Financial::where('id', $financialId)
            ->where('user_id', Auth::id())
            ->with('statements')
            ->firstOrFail();

        return view('financial.statements', compact('financial'));
    }

    // Store a new statement under a specific financial category
    public function storeStatement(Request $request, $financialId)
    {
        $request->validate([
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'information' => 'required|string|max:255',
        ]);

        // Retrieve the last balance or set it to 0 if no previous statements exist
        $lastStatement = FinancialStatement::where('financial_id', $financialId)
            ->orderBy('created_at', 'desc')
            ->first();

        // Set initial balance to 0 if no previous statement exists
        $lastBalance = $lastStatement ? $lastStatement->balance : 0;

        // Calculate the new balance based on debit and credit
        $balance = $lastBalance + $request->debit - $request->credit;

        // Create a new financial statement entry
        FinancialStatement::create([
            'financial_id' => $financialId,
            'debit' => $request->debit,
            'credit' => $request->credit,
            'balance' => $balance,
            'information' => $request->information,
        ]);

        return redirect()->route('financial.showStatements', $financialId)->with('success', 'Financial statement added successfully.');
    }
}
