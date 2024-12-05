<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\Sales\Sales;
use Illuminate\Support\Str;

class TransAction {
    public static function table($route, $model, $constraint, $modelLogPrint)
    {
        $deleteInformation = '<span class="text-red" style="font-weight: 600">Deleted</span>';
        if($model->getTable() == 'sales') $deleteInformation .= '<br> ' . $model->canceled_reason;
        
        if(!empty($model->deleted_at)) return $deleteInformation;

        $tableName =  Str::replaceLast('s', '', $model->getTable());
        $constraintColumn = null;

        if(!empty($constraint)) {
            if(Str::contains($constraint, '.')) {
                $expConstraint = explode('.', $constraint);
                $getConstraint = DB::table($expConstraint[0])
                                    ->where($tableName . '_id', $model->id)
                                    ->whereNull('deleted_at')
                                    ->first();
                $getConstraint = (array) $getConstraint;

                if(!empty($getConstraint)) $constraintColumn = $getConstraint[$expConstraint[1]];
            }else{
                $constraintColumn = $model[$constraint];
            }
        }

        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        $printInformation = !empty($modelLogPrint) ? 'di print oleh ' . $modelLogPrint->employee->name . ' pada tanggal ' . $modelLogPrint->date->format('m/d/Y') : '';
        $buttonPrintColor = !empty($printInformation) && !$isSuperAdmin ? 'text-yellow' : '';

        $buttonDeleteClass = 'confirmation-delete';

        if($model->getTable() == 'sales') {
            $quotationOnRequest = empty($model->order_number) && ($model->order_status == $model::ORDER_PENDING);
            $orderOnProcess = ($model->order_status == $model::ORDER_PROCESS);

            if($quotationOnRequest || $orderOnProcess) $buttonDeleteClass = 'prompt-delete';

            $buttonDeleteClass = ($quotationOnRequest || $orderOnProcess) ? 'prompt-delete' : 'confirmation-delete';
        }

        $isDisableDelete = !empty($printInformation) && (!$isSuperAdmin || !empty($constraintColumn)) ? 'disabled' : '';

        if($model->getTable() == 'sales' && $model->transaction_channel == Sales::TRANSACTION_CHANNEL_MOBILE) $isDisableDelete = 'disabled';

        $target = $isDisableDelete ? '' : url($route . '/' . $model->id);

        return '<div class="btn-group">
                    <a 
                        class="confirmation-print btn btn-default ' . $buttonPrintColor . '" 
                        title="' . $printInformation . '"
                        data-target="' . url($route . '/' . $model->id . '/print') . '"
                        data-toggle="tooltip"
                        data-information="' . $printInformation . '">
                        <i class="fa fa-print"></i>
                    </a>
                    <button 
                        class="' . $buttonDeleteClass . ' btn btn-default text-red"
                        data-target="' . $target . '"
                        data-token="' . csrf_token() . '"' .
                        $isDisableDelete . '>
                        <i class="fa fa-trash"></i>
                    </button>
                </div>'; 
    }

    public static function form($route, $model, $constraint, $modelLogPrint)
    {
        if(!empty($model->deleted_at)) return '<span class="text-red" style="font-weight: 600">Deleted</span>';

        $tableName =  Str::replaceLast('s', '', $model->getTable());
        $constraintColumn = null;

        if(!empty($constraint)) {
            if(Str::contains($constraint, '.')) {
                $expConstraint = explode('.', $constraint);
                $getConstraint = DB::table($expConstraint[0])
                                    ->where($tableName . '_id', $model->id)
                                    ->first();
                $getConstraint = (array) $getConstraint;

                if(!empty($getConstraint)) $constraintColumn = $getConstraint[$expConstraint[1]];
            }else{
                $constraintColumn = $model[$constraint];
            }
        }

        $roleUser = request()->user()->role->name;
        $isSuperAdmin = $roleUser === 'super_admin';

        $printInformation = !empty($modelLogPrint) ? 'di print oleh ' . $modelLogPrint->employee->name . ' pada tanggal ' . $modelLogPrint->date->format('m/d/Y')  : '';
        $isDisable = !empty($printInformation) && (!$isSuperAdmin || !empty($constraintColumn)) ? 'disabled' : '';
        $buttonPrintColor = !empty($printInformation) && !$isSuperAdmin ? 'text-yellow' : '';
        $target = $isDisable ? '' : url($route . '/' . $model->id . '/print');
        $actionType = $isDisable ? 'button' : 'submit';

        return '<input type="' . $actionType . '" class="btn btn-primary" value="save" ' . $isDisable . '>
                <input type="' . $actionType . '" class="btn btn-default text-red" value="save & print" 
                    title="' . $printInformation . '" 
                    data-toggle="tooltip"
                    ' . $isDisable . '>
                <input type="button" class="btn btn-default text-red" value="print"
                    title="' . $printInformation . '"
                    data-target="' . $target . '"
                    data-toggle="tooltip"
                    data-information="' . $printInformation . '"
                    ' . $isDisable . '>'; 
    }
    
}