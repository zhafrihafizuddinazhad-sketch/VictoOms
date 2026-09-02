<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Http\Request;

class OwnerReviewController extends Controller
{
    /**
     * Approve the designer's submitted design.
     *
     * Only Owner is allowed to approve.
     */
    public function approve(Request $request, Order $order)
    {
        // Only Owner can approve a design
        abort_unless(
            auth()->user()->hasRole('owner'),
            403
        );

        // Make sure the order is actually waiting for approval
        if ($order->status !== 'Pending Approval') {
            return back()->with(
                'error',
                'This order is not waiting for approval.'
            );
        }

        $request->validate([
            'owner_review_comment' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => 'Printing',
            'owner_review_comment' => $request->owner_review_comment,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'Design Approved',
            'description' => auth()->user()->name .
                ' approved the final design for printing.',
        ]);

        return back()->with(
            'success',
            'Design approved successfully.'
        );
    }


    /**
     * Request a revision from the designer.
     *
     * Only Owner is allowed to request a revision.
     */
    public function revision(Request $request, Order $order)
    {
        // Only Owner can request a revision
        abort_unless(
            auth()->user()->hasRole('owner'),
            403
        );

        // Make sure the order is actually waiting for approval
        if ($order->status !== 'Pending Approval') {
            return back()->with(
                'error',
                'This order is not waiting for approval.'
            );
        }

        $request->validate([
            'owner_review_comment' => 'required|string|max:1000',
        ]);

        $order->update([
            'status' => 'In Progress',
            'owner_review_comment' => $request->owner_review_comment,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        ActivityLog::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'Revision Requested',
            'description' => auth()->user()->name .
                ' requested a design revision. Comment: ' .
                $request->owner_review_comment,
        ]);

        return back()->with(
            'success',
            'Revision request sent to designer.'
        );
    }
}