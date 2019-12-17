<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OwnerPage extends Model
{
      public function owner_fileUpload($files)

    {

         $name = time()."_". $files->getClientOriginalName();
        $image = $files->move(public_path().'/uploads/owner' , $name);
        return $name;
    }
}
