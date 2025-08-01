<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleInquiry extends Model
{
    protected $fillable = [
        'orderFile',
        'orderNo',
        'inquiryReceiveDate',
        'customerName',
        'merchandiseName',
        'coordinatorName',
        'item',
        'ItemDiscription',
        'size',
        'qtRef',
        'color',
        'style',
        'sampleQty',
        'customerSpecialComment',
        'customerRequestDate',
        'alreadyDeveloped',
        'sentToSampleDevelopmentDate',
        'developPlannedDate',
        'productionStatus',
        'referenceNo',
        'customerDeliveryDate',
        'dNoteNumber',
        'customerDecision',
        'notes'
    ];

    protected $casts = [
        'inquiryReceiveDate' => 'date',
        'customerRequestDate' => 'date',
        'developPlannedDate' => 'date',
        'customerDeliveryDate' => 'datetime'
    ];

    public function samplePreparationRnD()
    {
        return $this->hasOne(SamplePreparationRnD::class);
    }

}
