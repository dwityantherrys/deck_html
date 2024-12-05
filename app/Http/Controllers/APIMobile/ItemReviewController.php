<?php

namespace App\Http\Controllers\APIMobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Sales\SalesDetail;
use App\Models\Master\Item\ItemReview;

class ItemReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($itemId)
    {
      try {
        $reviews = ItemReview::orderBy('created_at', 'DESC')
        ->where('item_id', $itemId)
        ->with(['sales_detail', 'sales_detail.sales', 'sales_detail.sales.customer'])
        ->paginate(20);

        foreach ($reviews as $review) {
          $review->rate =  $review->rate ? $review->rate : null;
        }

        return response()->json($reviews, 200);
      } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 500);
      }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
          $salesDetail = SalesDetail::find($request->sales_detail_id);

          // harusnya cek sudah terbayar belum

          ItemReview::create([
            'comment' => $request->comment,
            'rate' => $request->rate,
            'item_id' => $salesDetail->item_material->item_id,
            'sales_detail_id' => $salesDetail->id
          ]);

          return response()->json(['message' => 'review berhasil tersimpan.'], 200);

        } catch (\Throwable $th) {
          return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
