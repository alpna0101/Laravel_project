<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingInfo extends Model
{
     public function shipping_fileUpload($files)

    {

         $name = time()."_". $files->getClientOriginalName();
        $image = $files->move(public_path().'/uploads/shipping' , $name);
        return $name;
    }
}
