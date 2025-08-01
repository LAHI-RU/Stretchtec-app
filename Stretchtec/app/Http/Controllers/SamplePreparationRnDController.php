<?php

namespace App\Http\Controllers;

use App\Models\LeftoverYarn;
use App\Models\SamplePreparationRnD;
use App\Models\SamplePreparationProduction;
use App\Models\SampleStock;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SamplePreparationRnDController extends Controller
{
    public function viewRnD()
    {
        $samplePreparations = SamplePreparationRnD::with('sampleInquiry')->latest()->get();


        return view('sample-development.pages.sample-preparation-details', compact('samplePreparations'));
    }

    public function markColourMatchSent(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
        ]);

        $rnd = SamplePreparationRnD::findOrFail($request->id);
        $rnd->colourMatchSentDate = Carbon::now();
        $rnd->save();

        return back()->with('success', 'Colour Match marked as sent.');
    }

    public function markColourMatchReceive(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
        ]);

        $rnd = SamplePreparationRnD::findOrFail($request->id);
        $rnd->colourMatchReceiveDate = Carbon::now();
        $rnd->save();

        return back()->with('success', 'Colour Match marked as received.');
    }

    public function markYarnOrdered(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
        ]);

        $rnd = SamplePreparationRnD::findOrFail($request->id);
        $rnd->yarnOrderedDate = Carbon::now();
        $rnd->save();

        return back()->with('success', 'Yarn Ordered Date marked.');
    }

    public function markYarnReceived(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
        ]);

        $rnd = SamplePreparationRnD::findOrFail($request->id);
        $rnd->yarnReceiveDate = Carbon::now();
        $rnd->save();

        return back()->with('success', 'Yarn Receive Date marked.');
    }

    public function markSendToProduction(Request $request)
    {
        $rnd = SamplePreparationRnD::findOrFail($request->id);

        // Optional: Check if already sent
        if ($rnd->sendOrderToProductionStatus) {
            return redirect()->back()->with('info', 'Already sent to production.');
        }

        // Update sendOrderToProductionStatus
        $rnd->sendOrderToProductionStatus = now();
        $rnd->save();

        // Create production record
        SamplePreparationProduction::create([
            'sample_preparation_rnd_id' => $rnd->id,
            'order_no' => $rnd->orderNo,
            'production_deadline' => $rnd->productionDeadline,
            'order_received_at' => now(),
            'note' => $rnd->note,
        ]);

        return back()->with('success', 'Sent to production successfully.');
    }


    public function setDevelopPlanDate(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'developPlannedDate' => 'required|date',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);

        if ($prep->developPlannedDate) {
            return back()->with('error', 'Development Plan Date is already set and locked.');
        }

        $prep->developPlannedDate = $request->developPlannedDate;
        $prep->is_dev_plan_locked = true;
        $prep->save();

        return back()->with('success', 'Develop Plan Date saved.');
    }


    public function lockPoField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'yarnOrderedPONumber' => 'required|string',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        if (!$prep->is_po_locked) {
            $prep->yarnOrderedPONumber = $request->yarnOrderedPONumber;
            $prep->is_po_locked = true;
            $prep->save();
        }

        return back()->with('success', 'PO Number saved.');
    }

    public function lockShadeField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'shade' => 'required|string',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        if (!$prep->is_shade_locked) {
            $prep->shade = $request->shade;
            $prep->is_shade_locked = true;
            $prep->save();
        }

        return back()->with('success', 'Shade saved.');
    }

    public function lockTktField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'tkt' => 'required|string',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        if (!$prep->is_tkt_locked) {
            $prep->tkt = $request->tkt;
            $prep->is_tkt_locked = true;
            $prep->save();
        }

        return back()->with('success', 'TKT saved.');
    }

    public function lockSupplierField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'yarnSupplier' => 'required|string',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        if (!$prep->is_supplier_locked) {
            $prep->yarnSupplier = $request->yarnSupplier;
            $prep->is_supplier_locked = true;
            $prep->save();
        }

        return back()->with('success', 'Supplier saved.');
    }

    public function lockDeadlineField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'productionDeadline' => 'required|date',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        if (!$prep->is_deadline_locked) {
            $prep->productionDeadline = $request->productionDeadline;
            $prep->is_deadline_locked = true;
            $prep->save();
        }

        return back()->with('success', 'Production Deadline saved.');
    }

    public function lockReferenceField(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'referenceNo' => 'required|string',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);

        if (!$prep->is_reference_locked) {
            $prep->referenceNo = $request->referenceNo;
            $prep->is_reference_locked = true;
            $prep->save();

            // ✅ Fetch production details
            $production = $prep->production;

            $productionOutput = (int)($production->production_output ?? 0);
            $damagedOutput = (int)($production->damaged_output ?? 0);
            $availableStock = max($productionOutput - $damagedOutput, 0);

            // ✅ Create entry in sample_stocks
            SampleStock::create([
                'reference_no' => $request->referenceNo,
                'shade' => $prep->shade ?? $prep->sampleInquiry?->shade ?? 'N/A',
                'available_stock' => $availableStock,
                'special_note' => null,
            ]);

            // ✅ Sync with SampleInquiry
            $inquiry = $prep->sampleInquiry;
            if ($inquiry) {
                $inquiry->referenceNo = $prep->referenceNo;
                $inquiry->save();
            }
        }

        return back()->with('success', 'Reference No saved and Sample Stock created.');
    }

    public function updateDevelopedStatus(Request $request)
    {
        $prep = SamplePreparationRnD::findOrFail($request->id);

        // Disallow update if locked
        if ($prep->alreadyDeveloped || $prep->developPlannedDate) {
            return back()->with('error', 'Status is locked and cannot be changed.');
        }

        $prep->alreadyDeveloped = $request->alreadyDeveloped;
        $prep->save();

        return back()->with('success', 'Developed status updated successfully!');
    }

    public function updateYarnWeights(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:sample_preparation_rnd,id',
            'field' => 'required|in:yarnOrderedWeight,yarnLeftoverWeight',
            'value' => 'required|numeric',
        ]);

        $prep = SamplePreparationRnD::findOrFail($request->id);
        $field = $request->field;
        $lockField = 'is_' . Str::snake($field) . '_locked';

        $prep->$field = $request->value;
        $prep->$lockField = true;
        $prep->save();

        // ✅ Insert into leftover_yarns if yarnLeftoverWeight is updated
        if ($field === 'yarnLeftoverWeight') {
            LeftoverYarn::create([
                'shade'              => $prep->shade,
                'po_number'          => $prep->yarnOrderedPONumber,
                'yarn_received_date' => \Carbon\Carbon::parse($prep->yarnReceiveDate)->format('Y-m-d'),
                'tkt'                => $prep->tkt,
                'yarn_supplier'      => $prep->yarnSupplier,
                'available_stock'    => $request->value, // using yarnLeftoverWeight as available_stock
            ]);
        }

        return back()->with('success', 'Weight updated and leftover recorded.');
    }

    public function borrow(Request $request, $id)
    {
        $request->validate([
            'borrow_qty' => 'required|integer|min:1',
        ]);

        $leftover = LeftoverYarn::findOrFail($id);
        $borrowQty = $request->borrow_qty;

        if ($borrowQty > $leftover->available_stock) {
            return back()->with('error', 'Borrowed quantity exceeds available stock.');
        }

        if ($borrowQty == $leftover->available_stock) {
            $leftover->delete();
            return back()->with('success', 'All yarn borrowed. Record deleted.');
        }

        $leftover->available_stock -= $borrowQty;
        $leftover->save();

        return back()->with('success', 'Borrowed successfully.');
    }

}
