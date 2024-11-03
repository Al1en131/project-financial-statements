<?php

namespace App\Http\Controllers;

use App\Models\MemberCash;
use App\Models\CashFundInformation;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class MemberCashController extends Controller
{
    public function index($cashFundInformationId)
    {
        // Fetch the specific cash fund information
        $cashFundInformation = CashFundInformation::findOrFail($cashFundInformationId);

        // Fetch all members associated with this cash fund information
        $members = MemberCash::where('cash_fund_information_id', $cashFundInformationId)->get();

        // Get the cash detail amount (assuming it is the amount per week)
        $cashDetail = $cashFundInformation->cash_detail; // Adjust according to your actual column name

        // Initialize the total collected amount
        $totalCollected = 0;

        // Calculate the total collected funds based on the status of payments
        foreach ($members as $member) {
            // Count the number of weeks paid by the member
            $weeksPaid = 0;
            if ($member->week_1_status) $weeksPaid++;
            if ($member->week_2_status) $weeksPaid++;
            if ($member->week_3_status) $weeksPaid++;
            if ($member->week_4_status) $weeksPaid++;

            // Add to total collected: cash_detail * weeksPaid
            $totalCollected += $cashDetail * $weeksPaid;
        }

        return view('member_cash.index', compact('cashFundInformation', 'members', 'totalCollected'));
    }



    public function store(Request $request, $cashFundInformationId)
    {
        // Log the incoming request data
        Log::info('Request data:', $request->all());

        // Validate the request
        $request->validate([
            'member_name' => 'required|string|max:255',
        ]);

        // Create a new MemberCash entry
        MemberCash::create([
            'cash_fund_information_id' => $cashFundInformationId,
            'member_name' => $request->member_name,
        ]);

        return redirect()->route('cashfund_informations.member_cash.index', $cashFundInformationId)
            ->with('success', 'Member added successfully!');
    }

    public function update(Request $request, $cashFundInformationId, $member_cash)
    {
        $memberCash = MemberCash::findOrFail($member_cash);

        // Log data permintaan yang masuk
        Log::info('Updating MemberCash:', $request->all());

        // Validasi permintaan
        $request->validate([
            'week_1_status' => 'boolean',
            'week_2_status' => 'boolean',
            'week_3_status' => 'boolean',
            'week_4_status' => 'boolean',
        ]);

        // Perbarui status pembayaran
        $memberCash->update([
            'week_1_status' => $request->has('week_1_status'),
            'week_2_status' => $request->has('week_2_status'),
            'week_3_status' => $request->has('week_3_status'),
            'week_4_status' => $request->has('week_4_status'),
        ]);

        return redirect()->route('cashfund_informations.member_cash.index', $cashFundInformationId)
            ->with('success', 'Payment status updated successfully!');
    }

    public function updateName(Request $request, $cashFundInformationId, $memberId)
    {
        // Validate the incoming request
        $request->validate([
            'member_name' => 'required|string|max:255', // Adjust validation as necessary
        ]);

        // Find the member cash record by ID
        $member = MemberCash::where('cash_fund_information_id', $cashFundInformationId)
            ->where('id', $memberId)
            ->first();

        // Check if the member exists
        if (!$member) {
            return redirect()->back()->withErrors('Member not found.')->withInput();
        }

        // Update the member's name
        $member->name = $request->input('member_name');
        $member->save();

        // Redirect back with a success message
        return redirect()->route('cashfund_informations.show', $cashFundInformationId)
            ->with('success', 'Member name updated successfully.');
    }


    public function destroy($cashFundInformationId, $memberId)
    {
        $member = MemberCash::findOrFail($memberId);
        $member->delete();

        return redirect()->route('cashfund_informations.member_cash.index', $cashFundInformationId)
            ->with('success', 'Member deleted successfully.');
    }
}