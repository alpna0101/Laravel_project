<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRating extends Model
{
      public function toArray()
    {
        $array = parent::toArray();

        $array['diff_human_time'] = ($this->created_at) ? $this->created_at->diffForHumans() : 0;

        return $array;
    }

     public function product_fileUpload($files)

    {

         $name = time()."_". $files->getClientOriginalName();
        $image = $files->move(public_path().'/uploads/product' , $name);
        return $name;
    }
}
