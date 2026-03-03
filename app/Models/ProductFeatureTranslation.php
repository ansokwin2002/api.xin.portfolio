<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFeatureTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['product_feature_id', 'locale', 'feature_text'];

    public function feature()
    {
        return $this->belongsTo(ProductFeature::class, 'product_feature_id');
    }
}
