<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBitcoin extends Model
{
  public function QR_fileUpload($files)

    {

         $name = time()."_". $files->getClientOriginalName();
        $image = $files->move(public_path().'/uploads/qr_code' , $name);
        return $name;
    }
}
