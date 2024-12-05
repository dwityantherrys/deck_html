<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

use App\Models\Master\Item\Item;
use App\Models\Master\Item\ItemReview;

class ItemReviewController extends Controller
{
    private $route = 'customer/review';
    private $routeView = 'customer.review';
    private $params = [];

    public function __construct (Builder $datatablesBuilder)
    {
      $this->model = new Item();
      $this->datatablesBuilder = $datatablesBuilder;

      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
      
        if ($request->ajax()) {
            return Datatables::of($this->model::with(['item_category', 'item_reviews']))
                        ->addColumn('review_rate', function (Item $item) {
                            $starImage = $item->review_rate > 0 ? 'filled' : 'null';
                            return '<div style="display: flex; align-items: center">
                                <b style="margin-right: 5px">' . $item->review_rate . '</b>
                                <img src="' . asset('img/stars-' . $starImage . '.png') .'" width="15px" height="15px"> 
                            </div>'; 
                        })
                        ->addColumn('last_review', function (Item $item) {
                            $reviewLatest = $item->item_reviews()->first();

                            if(empty($reviewLatest)) return;

                            $salesDetail = $reviewLatest->sales_detail;
                            if(empty($salesDetail)) return '<b>No name</b> - ' . $reviewLatest->comment;
                            
                            $sales = $salesDetail->sales;

                            return '<b>' . $sales->customer->name . '</b> - ' . $reviewLatest->comment; 
                        })
                        ->addColumn('action', function (Item $item) { 
                            return '<div class="btn-group">
                                <a 
                                    href="'. url($this->route . '/' . $item->id) .'"
                                    class="btn btn-default" 
                                    title="more review"
                                    data-toggle="tooltip">
                                    <i class="fa fa-th-list" aria-hidden="true"></i>
                                </a>
                            </div>'; 
                        })
                        ->rawColumns(['review_rate', 'last_review', 'action'])          
                        ->make(true);
        }

        $this->params['model'] = $this->model;
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'name', 'name' => 'name', 'title' => 'Item' ])
                                        ->addColumn([ 'data' => 'review_rate', 'name' => 'review_rate', 'title' => 'Avg. rating' ])
                                        ->addColumn([ 'data' => 'review_total', 'name' => 'review_total', 'title' => 'Total review' ])
                                        ->addColumn([ 'data' => 'last_review', 'name' => 'last_review', 'title' => 'Latest review' ])
                                        ->addColumn([ 'data' => 'action', 'name' => 'action', 'title' => 'Action' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                            }',
                                        ]);

        return view($this->routeView . '.index', $this->params);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        if ($request->ajax()) {
            return Datatables::of(ItemReview::where('item_id', $id)->with(['item', 'sales_detail']))
                        ->editColumn('comment', function (ItemReview $itemReview) {
                            $salesDetail = $itemReview->sales_detail;
                            
                            if(empty($salesDetail)) return '<b>No name</b> - ' . $itemReview->comment;

                            $sales = $salesDetail->sales;
                            return '<b>' . $sales->customer->name . '</b> - ' . $itemReview->comment; 
                        })
                        ->editColumn('rate', function (ItemReview $itemReview) {
                            $starImage = $itemReview->rate > 0 ? 'filled' : 'null';
                            return '<div style="display: flex; align-items: center">
                                <b style="margin-right: 5px">' . $itemReview->rate . '</b>
                                <img src="' . asset('img/stars-' . $starImage . '.png') .'" width="15px" height="15px"> 
                            </div>'; 
                        })
                        ->editColumn('created_at', function (ItemReview $itemReview) { 
                            return $itemReview->created_at->format('m/d/Y'); 
                        })
                        ->addColumn('sales_order_number', function (ItemReview $itemReview) { 
                            $salesDetail = $itemReview->sales_detail;
                            
                            if(empty($salesDetail)) return '';

                            $sales = $salesDetail->sales;
                            return $sales->order_number;
                        })
                        ->rawColumns(['comment', 'rate'])          
                        ->make(true);
        }
        
        $this->params['model'] = $this->model->find($id);
        $this->params['datatable'] = $this->datatablesBuilder
                                        ->addColumn([ 'data' => 'comment', 'name' => 'comment', 'title' => 'Comment' ])
                                        ->addColumn([ 'data' => 'rate', 'name' => 'rate', 'title' => 'Rating' ])
                                        ->addColumn([ 'data' => 'created_at', 'name' => 'created_at', 'title' => 'Date review' ])
                                        ->addColumn([ 'data' => 'sales_order_number', 'name' => 'sales_order_number', 'title' => 'Order Number' ])
                                        ->parameters([
                                            'initComplete' => 'function() { 
                                                $.getScript("'. asset("js/utomodeck.js") .'");
                                            }',
                                        ]);

        if(count($this->params['model']->images) <= 0) {
            $this->params['model']->images[] = (object) [
              "id" => 1,
              "image" => "/img/no-image.png",
              "item_id" => 1,
              "is_thumbnail" => 1,
              "is_active" => 1,
              "created_at" => date('Y-m-d H:i:s'),
              "updated_at" => date('Y-m-d H:i:s'),
              "image_url" => asset("/img/no-image.png")
            ];
        }

        return view($this->routeView . '.detail', $this->params);
    }
}
