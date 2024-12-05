<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SalesQuotationExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStrictNullComparison
{
  /**
  * @return \Illuminate\Support\Collection
  */
  public function __construct($model, $heading) {
    $this->model = $model;
    $this->heading = $heading;
  }

  public function collection()
  {
    //
    return $this->model->get();
  }

  public function map($val): array
  {
    $customer = isset($val->customer) ? $val->customer->name : null;
    switch ($val->quotation_status) {
      case 0:
      $quotation_status = "Quotation";
      break;

      case 1:
      $quotation_status = "Accepted";
      break;

      case 3:
      $quotation_status = "Rejected/Canceled";
      break;
    }

    switch ($val->order_status) {
      case 0:
      $order_status = "Pending";
      break;

      case 1:
      $order_status = "Process";
      break;

      case 2:
      $order_status = "Finish";
      break;

      case 3:
      $order_status = "Canceled";
      break;
    }

    $warehouse = isset($val->warehouse_out) ? $val->warehouse_out->name : null;
    switch ($val->shipping_method_id) {
      case 1:
      $shipping_method = "Pickup";
      break;

      case 2:
      $shipping_method = "Pickup Point";
      break;

      case 3:
      $shipping_method = "Delivery";
      break;
    }
    $shipping_address = isset($val->warehouse_pickup_point) ? $val->warehouse_pickup_point->name : null;
    $payment_method = isset($val->payment_method) ? $val->payment_method->name : null;
    $payment_bank_channel = isset($val->payment_bank_channel) ? $val->payment_bank_channel->rekening_name : null;

    switch ($val->transaction_channel) {
      case 0:
      $transaction_channel = "Web";
      break;

      case 1:
      $transaction_channel = "Mobile";
      break;
    }

    $pic = isset($val->pic) ? $val->pic->name : null;

    return [
      $val->id,
      $val->quotation_number,
      $val->quotation_date,
      $val->quotation_expiration_date,
      ($customer ?? null),
      $quotation_status,
      $val->order_number,
      $val->order_date,
      $order_status,
      ($warehouse ?? null),
      ($shipping_method ?? null),
      ($shipping_address ?? null),
      $val->shipping_cost,
      $val->discount . "%",
      $val->downpayment,
      $val->tax,
      $val->total_price,
      $val->grand_total_price,
      ($payment_method ?? null),
      ($payment_bank_channel ?? null),
      $transaction_channel,
      $val->canceled_reason,
      ($pic ?? null),
      $val->created_at,
      $val->updated_at,
      $val->deleted_at,
    ];
  }

  public function headings(): array
  {
    return $this->heading;
  }
}
